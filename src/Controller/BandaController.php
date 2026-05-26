<?php

namespace App\Controller;

use App\Entity\Banda;
use App\Entity\MiembroBanda;
use App\Form\BandaType;
use App\Repository\BandaRepository;
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
    public function index(BandaRepository $bandaRepository): Response
    {
        return $this->render('banda/index.html.twig', [
            'bandas' => $bandaRepository->findAll(),
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

            $entityManager->persist($banda);

            $miembro = new MiembroBanda();
            $miembro->setBanda($banda);
            $miembro->setMusico($musico);
            $miembro->setRolBanda('Fundador');
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

            $entityManager->flush();

            $this->addFlash('success', '¡Banda actualizada con éxito!');
            return $this->redirectToRoute('app_banda_show', ['id' => $banda->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('banda/edit.html.twig', [
            'banda' => $banda,
            'form' => $form,
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

        $miembro = new MiembroBanda();
        $miembro->setBanda($banda);
        $miembro->setMusico($musico);
        $miembro->setRolBanda(null);
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

        return $this->render('banda/solicitudes.html.twig', [
            'banda' => $banda,
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

    #[Route('/{id}', name: 'app_banda_delete', methods: ['POST'])]
    public function delete(Request $request, Banda $banda, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $musico = $this->getUser()->getMusico();
        if (!$musico || !$this->esAdminDeBanda($banda, $musico)) {
            throw $this->createAccessDeniedException('No tienes permisos de administrador en esta banda.');
        }

        if ($this->isCsrfTokenValid('delete' . $banda->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($banda);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_banda_index', [], Response::HTTP_SEE_OTHER);
    }

    private function esAdminDeBanda(Banda $banda, \App\Entity\Musico $musico): bool
    {
        foreach ($banda->getMiembroBandas() as $mb) {
            if ($mb->getMusico() === $musico && $mb->isEsAdministrador() && $mb->getEstado() === 'aceptado') {
                return true;
            }
        }
        return false;
    }
}
