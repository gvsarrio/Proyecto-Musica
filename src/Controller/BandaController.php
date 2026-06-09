<?php

namespace App\Controller;

use App\Entity\Banda;
use App\Entity\MiembroBanda;
use App\Entity\Musico;
use App\Entity\Usuario;
use App\Form\BandaType;
use App\Repository\BandaRepository;
use App\Repository\GeneroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/banda')]
final class BandaController extends AbstractController
{
    #[Route('/list', name: 'app_banda_index', methods: ['GET'])]
    public function index(
        BandaRepository $bandaRepository,
        GeneroRepository $generoRepository,
        Request $request
    ): Response {
        $filtros = $request->query->all('filtros');

        $generoIds = !empty($filtros['generos']) ? array_map('intval', (array) $filtros['generos']) : [];
        $lat   = isset($filtros['lat'])   && $filtros['lat']   !== '' ? (float) $filtros['lat']   : null;
        $lng   = isset($filtros['lng'])   && $filtros['lng']   !== '' ? (float) $filtros['lng']   : null;
        $radio = isset($filtros['radio']) && $filtros['radio'] !== '' ? (int)   $filtros['radio'] : null;

        $hayFiltros = !empty($generoIds) || ($lat !== null && $radio !== null);

        $bandas = $hayFiltros
            ? $bandaRepository->findByFiltros($generoIds, $lat, $lng, $radio)
            : $bandaRepository->findAll();

        $distancias = [];
        if ($lat !== null && $lng !== null) {
            foreach ($bandas as $b) {
                if ($b->getLatitud() !== null && $b->getLongitud() !== null) {
                    $distancias[$b->getId()] = round(
                        $bandaRepository->calcularDistanciaKm($lat, $lng, $b->getLatitud(), $b->getLongitud()),
                        1
                    );
                }
            }
        }

        return $this->render('banda/index.html.twig', [
            'bandas'     => $bandas,
            'generos'    => $generoRepository->findBy([], ['nombre' => 'ASC']),
            'filtros'    => $filtros,
            'distancias' => $distancias,
        ]);
    }

    #[Route('/new', name: 'app_banda_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        if (!$musico) {
            $this->addFlash('warning', 'Necesitas un perfil de músico para crear una banda.');
            return $this->redirectToRoute('app_musico_new');
        }

        $banda = new Banda();
        $form = $this->createForm(BandaType::class, $banda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fotoArchivo = $form->get('imagen_url')->getData();
            if ($fotoArchivo) {
                $nuevoNombre = uniqid() . '.' . $fotoArchivo->guessExtension();
                try {
                    $fotoArchivo->move($this->getParameter('bandas_directory'), $nuevoNombre);
                    $banda->setImagenUrl($nuevoNombre);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo guardar la imagen.');
                }
            }

            foreach ($form->get('generos_musicales')->getData() as $genero) {
                $banda->addGeneroMusical($genero);
            }

            $entityManager->persist($banda);

            $miembro = new MiembroBanda();
            $miembro->setBanda($banda);
            $miembro->setMusico($musico);
            $miembro->setRolBanda(null);
            $miembro->setEstado('aceptado');
            $miembro->setEsAdministrador(true);
            $entityManager->persist($miembro);

            $entityManager->flush();

            $this->addFlash('success', '¡Banda creada con éxito!');
            return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('banda/new.html.twig', [
            'banda' => $banda,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_banda_show', methods: ['GET'])]
    public function show(Banda $banda): Response
    {
        $miembroActual = null;
        $esAdmin = false;

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $musico = $this->getUser()->getMusico();
            if ($musico) {
                foreach ($banda->getMiembroBandas() as $mb) {
                    if ($mb->getMusico() === $musico) {
                        $miembroActual = $mb;
                        $esAdmin = $mb->isEsAdministrador() && $mb->getEstado() === 'aceptado';
                        break;
                    }
                }
            }
        }

        return $this->render('banda/show.html.twig', [
            'banda' => $banda,
            'miembro_actual' => $miembroActual,
            'es_admin' => $esAdmin,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_banda_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Banda $banda, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException('No tienes permisos de administrador en esta banda.');
        }

        $form = $this->createForm(BandaType::class, $banda);
        $form->get('generos_musicales')->setData($banda->getGenerosMusicales()->toArray());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fotoArchivo = $form->get('imagen_url')->getData();
            if ($fotoArchivo) {
                $nuevoNombre = uniqid() . '.' . $fotoArchivo->guessExtension();
                try {
                    $fotoArchivo->move($this->getParameter('bandas_directory'), $nuevoNombre);
                    $banda->setImagenUrl($nuevoNombre);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo actualizar la imagen.');
                }
            }

            $banda->getGenerosMusicales()->clear();
            $entityManager->flush();

            foreach ($form->get('generos_musicales')->getData() as $genero) {
                $banda->addGeneroMusical($genero);
            }

            $entityManager->flush();

            $this->addFlash('success', '¡Banda actualizada con éxito!');
            return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('banda/edit.html.twig', [
            'banda' => $banda,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/solicitar-union', name: 'app_banda_form_solicitar_union', methods: ['GET'])]
    public function formSolicitarUnion(Banda $banda): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        if (!$musico) {
            $this->addFlash('warning', 'Necesitas un perfil de músico para unirte a una banda.');
            return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()]);
        }

        foreach ($banda->getMiembroBandas() as $mb) {
            if ($mb->getMusico() === $musico) {
                $this->addFlash('info', 'Ya eres miembro de esta banda o tienes una solicitud pendiente.');
                return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()]);
            }
        }

        $instrumentos = array_merge(
            $musico->getInstrumentosSistema()->toArray(),
            $musico->getInstrumentosPersonalizados()->toArray()
        );

        return $this->render('banda/solicitar_union.html.twig', [
            'banda' => $banda,
            'instrumentos' => $instrumentos,
        ]);
    }

    #[Route('/{id}/solicitar-union', name: 'app_banda_solicitar_union', methods: ['POST'])]
    public function solicitarUnion(Request $request, Banda $banda, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        if (!$musico) {
            $this->addFlash('warning', 'Necesitas un perfil de músico para unirte a una banda.');
            return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()]);
        }

        foreach ($banda->getMiembroBandas() as $mb) {
            if ($mb->getMusico() === $musico) {
                $this->addFlash('info', 'Ya eres miembro de esta banda o tienes una solicitud pendiente.');
                return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()]);
            }
        }

        if (!$this->isCsrfTokenValid('solicitar_union_' . $banda->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $instrumentosSeleccionados = $request->request->all('instrumentos');
        $rolBanda = !empty($instrumentosSeleccionados) ? implode(', ', $instrumentosSeleccionados) : null;

        $miembro = new MiembroBanda();
        $miembro->setBanda($banda);
        $miembro->setMusico($musico);
        $miembro->setRolBanda($rolBanda);
        $miembro->setEstado('pendiente');
        $miembro->setEsAdministrador(false);
        $entityManager->persist($miembro);
        $entityManager->flush();

        $this->addFlash('success', 'Solicitud enviada. Un administrador la revisará pronto.');
        return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()]);
    }

    #[Route('/{id}/solicitudes', name: 'app_banda_solicitudes', methods: ['GET'])]
    public function solicitudes(Banda $banda): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException('No tienes permisos de administrador en esta banda.');
        }

        $pendientes = $banda->getMiembroBandas()->filter(
            fn(MiembroBanda $mb) => $mb->getEstado() === 'pendiente'
        );
        $miembrosAceptados = $banda->getMiembroBandas()->filter(
            fn(MiembroBanda $mb) => $mb->getEstado() === 'aceptado'
        );

        return $this->render('banda/solicitudes.html.twig', [
            'banda' => $banda,
            'miembros_aceptados' => $miembrosAceptados,
            'pendientes' => $pendientes,
        ]);
    }

    #[Route('/solicitud/{id}/aceptar', name: 'app_banda_aceptar_solicitud', methods: ['POST'])]
    public function aceptarSolicitud(Request $request, MiembroBanda $miembro, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        $banda = $miembro->getBanda();

        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('aceptar_solicitud_' . $miembro->getId(), $request->request->get('_token'))) {
            $miembro->setEstado('aceptado');
            $entityManager->flush();
            $this->addFlash('success', 'Solicitud de ' . $miembro->getMusico()->getNombre() . ' aceptada.');
        }

        return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
    }

    #[Route('/solicitud/{id}/rechazar', name: 'app_banda_rechazar_solicitud', methods: ['POST'])]
    public function rechazarSolicitud(Request $request, MiembroBanda $miembro, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        $banda = $miembro->getBanda();

        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('rechazar_solicitud_' . $miembro->getId(), $request->request->get('_token'))) {
            $miembro->setEstado('rechazado');
            $entityManager->flush();
            $this->addFlash('info', 'Solicitud de ' . $miembro->getMusico()->getNombre() . ' rechazada.');
        }

        return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
    }

    #[Route('/miembro/{id}/hacer-admin', name: 'app_banda_hacer_admin', methods: ['POST'])]
    public function hacerAdmin(Request $request, MiembroBanda $miembro, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        $banda = $miembro->getBanda();

        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('hacer_admin_' . $miembro->getId(), $request->request->get('_token'))) {
            $miembro->setEsAdministrador(true);
            $entityManager->flush();
            $this->addFlash('success', $miembro->getMusico()->getNombre() . ' es ahora administrador de la banda.');
        }

        return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
    }

    #[Route('/miembro/{id}/quitar-admin', name: 'app_banda_quitar_admin', methods: ['POST'])]
    public function quitarAdmin(Request $request, MiembroBanda $miembro, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        $banda = $miembro->getBanda();

        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException();
        }

        // Evitar que quede la banda sin ningún administrador
        $totalAdmins = $banda->getMiembroBandas()->filter(
            fn(MiembroBanda $mb) => $mb->isEsAdministrador() && $mb->getEstado() === 'aceptado'
        )->count();

        if ($totalAdmins <= 1) {
            $this->addFlash('warning', 'No puedes quitar el último administrador de la banda.');
            return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
        }

        if ($this->isCsrfTokenValid('quitar_admin_' . $miembro->getId(), $request->request->get('_token'))) {
            $miembro->setEsAdministrador(false);
            $entityManager->flush();
            $this->addFlash('info', $miembro->getMusico()->getNombre() . ' ya no es administrador.');
        }

        return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
    }

    #[Route('/{id}', name: 'app_banda_delete', methods: ['POST'])]
    public function delete(Request $request, Banda $banda, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException('No tienes permisos de administrador en esta banda.');
        }

        if ($this->isCsrfTokenValid('delete' . $banda->getId(), $request->getPayload()->getString('_token'))) {
            $miembrosAceptados = $banda->getMiembroBandas()->filter(
                fn($mb) => $mb->getEstado() === 'aceptado'
            );

            if ($miembrosAceptados->count() > 1) {
                $this->addFlash('error', 'Debes eliminar a todos los miembros antes de borrar la banda.');
                return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()], Response::HTTP_SEE_OTHER);
            }

            foreach ($banda->getMiembroBandas() as $miembro) {
                $entityManager->remove($miembro);
            }
            $entityManager->remove($banda);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_banda_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{banda}/invitar/{musico}', name: 'app_banda_invitar', methods: ['POST'])]
    public function invitar(Request $request, Banda $banda, Musico $musico, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\Usuario $usuario */
        $musicoActual = $this->getUser()->getMusico();
        if (!$musicoActual || !$this->esAdminDeBanda($banda, $musicoActual)) {
            throw $this->createAccessDeniedException('No tienes permisos de administrador en esta banda.');
        }

        foreach ($banda->getMiembroBandas() as $mb) {
            if ($mb->getMusico() === $musico && $mb->getEstado() !== 'rechazado') {
                $this->addFlash('info', $musico->getNombre() . ' ya tiene relación activa con esta banda.');
                return $this->redirectToRoute('app_musico_show', ['id' => $musico->getId()]);
            }
        }

        if (!$this->isCsrfTokenValid('invitar_' . $banda->getId() . '_' . $musico->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $miembro = new MiembroBanda();
        $miembro->setBanda($banda);
        $miembro->setMusico($musico);
        $miembro->setRolBanda(null);
        $miembro->setEstado('invitado');
        $miembro->setEsAdministrador(false);
        $entityManager->persist($miembro);
        $entityManager->flush();

        $this->addFlash('success', 'Invitación enviada a ' . $musico->getNombre() . '.');
        return $this->redirectToRoute('app_musico_show', ['id' => $musico->getId()]);
    }

    #[Route('/invitacion/{id}/aceptar', name: 'app_banda_aceptar_invitacion', methods: ['POST'])]
    public function aceptarInvitacion(Request $request, MiembroBanda $miembro, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\Usuario $usuario */
        $musicoActual = $this->getUser()->getMusico();
        if (!$musicoActual || $miembro->getMusico() !== $musicoActual || $miembro->getEstado() !== 'invitado') {
            throw $this->createAccessDeniedException('No puedes gestionar esta invitación.');
        }

        if ($this->isCsrfTokenValid('aceptar_invitacion_' . $miembro->getId(), $request->request->get('_token'))) {
            $miembro->setEstado('aceptado');
            $entityManager->flush();
            $this->addFlash('success', '¡Te has unido a ' . $miembro->getBanda()->getNombre() . '!');
        }

        return $this->redirectToRoute('app_musico_show', ['id' => $musicoActual->getId()]);
    }

    #[Route('/invitacion/{id}/rechazar', name: 'app_banda_rechazar_invitacion', methods: ['POST'])]
    public function rechazarInvitacion(Request $request, MiembroBanda $miembro, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\Usuario $usuario */
        $musicoActual = $this->getUser()->getMusico();
        if (!$musicoActual || $miembro->getMusico() !== $musicoActual || $miembro->getEstado() !== 'invitado') {
            throw $this->createAccessDeniedException('No puedes gestionar esta invitación.');
        }

        if ($this->isCsrfTokenValid('rechazar_invitacion_' . $miembro->getId(), $request->request->get('_token'))) {
            $miembro->setEstado('rechazado');
            $entityManager->flush();
            $this->addFlash('info', 'Has rechazado la invitación de ' . $miembro->getBanda()->getNombre() . '.');
        }

        return $this->redirectToRoute('app_musico_show', ['id' => $musicoActual->getId()]);
    }

    #[Route('/miembro/{id}/expulsar', name: 'app_banda_expulsar_miembro', methods: ['POST'])]
    public function expulsarMiembro(Request $request, MiembroBanda $miembro, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        $banda = $miembro->getBanda();

        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException();
        }

        if ($miembro->getMusico() === $musico) {
            $this->addFlash('warning', 'No puedes expulsarte a ti mismo. Usa "Salir de la banda".');
            return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
        }

        if ($miembro->isEsAdministrador()) {
            $totalAdmins = $banda->getMiembroBandas()->filter(
                fn(MiembroBanda $mb) => $mb->isEsAdministrador() && $mb->getEstado() === 'aceptado'
            )->count();
            if ($totalAdmins <= 1) {
                $this->addFlash('warning', 'No puedes expulsar al único administrador de la banda.');
                return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
            }
        }

        if ($this->isCsrfTokenValid('expulsar_miembro_' . $miembro->getId(), $request->request->get('_token'))) {
            $nombre = $miembro->getMusico()->getNombre();
            $entityManager->remove($miembro);
            $entityManager->flush();
            $this->addFlash('info', $nombre . ' ha sido expulsado de la banda.');
        }

        return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
    }

    #[Route('/{id}/salir', name: 'app_banda_salir', methods: ['POST'])]
    public function salirDeBanda(Request $request, Banda $banda, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        if (!$musico) {
            throw $this->createAccessDeniedException();
        }

        $miembro = null;
        foreach ($banda->getMiembroBandas() as $mb) {
            if ($mb->getMusico() === $musico && $mb->getEstado() === 'aceptado') {
                $miembro = $mb;
                break;
            }
        }

        if (!$miembro) {
            $this->addFlash('warning', 'No eres miembro aceptado de esta banda.');
            return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()]);
        }

        if ($miembro->isEsAdministrador()) {
            $totalAdmins = $banda->getMiembroBandas()->filter(
                fn(MiembroBanda $mb) => $mb->isEsAdministrador() && $mb->getEstado() === 'aceptado'
            )->count();
            if ($totalAdmins <= 1) {
                $this->addFlash('warning', 'Eres el único administrador. Designa otro admin antes de salir.');
                return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()]);
            }
        }

        if ($this->isCsrfTokenValid('salir_banda_' . $banda->getId(), $request->request->get('_token'))) {
            $entityManager->remove($miembro);
            $entityManager->flush();
            $this->addFlash('info', 'Has salido de ' . $banda->getNombre() . '.');
        }

        return $this->redirectToRoute('app_banda_index');
    }

    #[Route('/miembro/{id}/editar-rol', name: 'app_banda_editar_rol', methods: ['POST'])]
    public function editarRol(Request $request, MiembroBanda $miembro, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        $banda = $miembro->getBanda();

        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('editar_rol_' . $miembro->getId(), $request->request->get('_token'))) {
            $nuevoRol = $request->request->get('rol_banda');
            $miembro->setRolBanda($nuevoRol ?: null);
            $entityManager->flush();
            $this->addFlash('success', 'Instrumentos de ' . $miembro->getMusico()->getNombre() . ' actualizados.');
        }

        return $this->redirectToRoute('app_banda_solicitudes', ['id' => $banda->getId()]);
    }

    private function esAdminDeBanda(Banda $banda, Musico $musico): bool
    {
        foreach ($banda->getMiembroBandas() as $mb) {
            if ($mb->getMusico() === $musico && $mb->isEsAdministrador() && $mb->getEstado() === 'aceptado') {
                return true;
            }
        }
        return false;
    }
}
