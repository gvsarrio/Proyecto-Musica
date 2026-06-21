<?php

namespace App\Controller;

use App\Entity\Musico;
use App\Entity\Usuario;
use App\Form\MusicoType;
use App\Repository\GeneroRepository;
use App\Repository\InstrumentoSistemaRepository;
use App\Repository\MusicoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/musico')]
final class MusicoController extends AbstractController
{
    #[Route('/list', name: 'app_musico_index', methods: ['GET'])]
    public function index(
        MusicoRepository $musicoRepository,
        GeneroRepository $generoRepository,
        InstrumentoSistemaRepository $instrumentoSistemaRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $filtros = $request->query->all('filtros');

        $generoIds      = !empty($filtros['generos'])      ? array_map('intval', (array) $filtros['generos'])      : [];
        $instrumentoIds = !empty($filtros['instrumentos']) ? array_map('intval', (array) $filtros['instrumentos']) : [];
        $lat   = isset($filtros['lat'])   && $filtros['lat']   !== '' ? (float) $filtros['lat']   : null;
        $lng   = isset($filtros['lng'])   && $filtros['lng']   !== '' ? (float) $filtros['lng']   : null;
        $radio = isset($filtros['radio']) && $filtros['radio'] !== '' ? (int)   $filtros['radio'] : null;

        $hayFiltros = !empty($generoIds) || !empty($instrumentoIds) || ($lat !== null && $radio !== null);

        $musicos = $hayFiltros
            ? $musicoRepository->findByFiltros($generoIds, $instrumentoIds, $lat, $lng, $radio)
            : $musicoRepository->findAll();

        /** @var \App\Entity\Usuario|null $usuarioActual */
        $usuarioActual = $this->getUser();
        $musicoPropioId = $usuarioActual?->getMusico()?->getId();
        if ($musicoPropioId !== null) {
            $musicos = array_values(array_filter($musicos, fn($m) => $m->getId() !== $musicoPropioId));
        }

        $distancias = [];
        if ($lat !== null && $lng !== null) {
            foreach ($musicos as $m) {
                if ($m->getLatitud() !== null && $m->getLongitud() !== null) {
                    $distancias[$m->getId()] = round(
                        $musicoRepository->calcularDistanciaKm($lat, $lng, $m->getLatitud(), $m->getLongitud()),
                        1
                    );
                }
            }
        }

        $pagination = $paginator->paginate($musicos, $request->query->getInt('page', 1), 12);

        return $this->render('musico/index.html.twig', [
            'musicos'      => $pagination,
            'generos'      => $generoRepository->findBy([], ['nombre' => 'ASC']),
            'instrumentos' => $instrumentoSistemaRepository->findBy([], ['nombre' => 'ASC']),
            'filtros'      => $filtros,
            'distancias'   => $distancias,
        ]);
    }

    #[Route('/new', name: 'app_musico_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        if (!$usuario) {
            throw $this->createAccessDeniedException();
        }

        if ($usuario->getMusico() !== null) {
            throw $this->createAccessDeniedException('Ya existe un perfil creado por este usuario');
        }

        $musico = new Musico();
        $form = $this->createForm(MusicoType::class, $musico, ['usuario' => $usuario]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $musico->setUsuario($usuario);

            $fotoArchivo = $form->get('imagen_url')->getData();
            if ($fotoArchivo) {
                $nuevoNombre = uniqid() . '.' . $fotoArchivo->guessExtension();
                try {
                    $fotoArchivo->move($this->getParameter('perfiles_directory'), $nuevoNombre);
                    $musico->setImagenUrl($nuevoNombre);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo guardar la imagen.');
                }
            }

            foreach ($form->get('instrumentos_sistema')->getData() as $instrumento) {
                $musico->getInstrumentosSistema()->add($instrumento);
            }

            foreach ($form->get('instrumentos_personalizados')->getData() as $instrumento) {
                $musico->getInstrumentosPersonalizados()->add($instrumento);
            }

            foreach ($form->get('generos_musicales')->getData() as $genero) {
                $musico->addGeneroMusical($genero);
            }

            $entityManager->persist($musico);
            $entityManager->flush();

            $this->addFlash('success', '¡Perfil creado con éxito!');
            return $this->redirectToRoute('app_musico_index');
        }

        return $this->render('musico/new.html.twig', [
            'musico' => $musico,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_musico_show', methods: ['GET'])]
    public function show(Musico $musico): Response
    {
        $bandasParaInvitar = [];
        $invitaciones = [];

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            /** @var Usuario $usuario */
            $usuario = $this->getUser();
            $musicoActual = $usuario->getMusico();

            if ($musicoActual && $musicoActual !== $musico) {
                foreach ($musicoActual->getMiembroBandasAceptadas() as $mb) {
                    if (!$mb->isEsAdministrador()) {
                        continue;
                    }
                    $banda = $mb->getBanda();
                    $yaRelacionado = false;
                    foreach ($banda->getMiembroBandas() as $mbBanda) {
                        if ($mbBanda->getMusico() === $musico && $mbBanda->getEstado() !== 'rechazado') {
                            $yaRelacionado = true;
                            break;
                        }
                    }
                    if (!$yaRelacionado) {
                        $bandasParaInvitar[] = $banda;
                    }
                }
            }

            if ($musicoActual === $musico) {
                foreach ($musico->getMiembroBandas() as $mb) {
                    if ($mb->getEstado() === 'invitado') {
                        $invitaciones[] = $mb;
                    }
                }
            }
        }

        return $this->render('musico/show.html.twig', [
            'musico' => $musico,
            'bandas_para_invitar' => $bandasParaInvitar,
            'invitaciones' => $invitaciones,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_musico_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Musico $musico, EntityManagerInterface $entityManager): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        if ($musico->getUsuario() !== $usuario) {
            throw $this->createAccessDeniedException('No se puede editar este perfil desde este usuario.');
        }

        $form = $this->createForm(MusicoType::class, $musico, ['usuario' => $usuario]);

        $form->get('instrumentos_sistema')->setData($musico->getInstrumentosSistema()->toArray());
        $form->get('instrumentos_personalizados')->setData($musico->getInstrumentosPersonalizados()->toArray());
        $form->get('generos_musicales')->setData($musico->getGenerosMusicales()->toArray());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fotoArchivo = $form->get('imagen_url')->getData();
            if ($fotoArchivo) {
                $nuevoNombre = uniqid() . '.' . $fotoArchivo->guessExtension();
                try {
                    $fotoArchivo->move($this->getParameter('perfiles_directory'), $nuevoNombre);
                    $musico->setImagenUrl($nuevoNombre);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo actualizar la imagen.');
                }
            }

            $musico->getInstrumentosSistema()->clear();
            $musico->getInstrumentosPersonalizados()->clear();
            $musico->getGenerosMusicales()->clear();
            $entityManager->flush();

            foreach ($form->get('instrumentos_sistema')->getData() as $instrumento) {
                $musico->getInstrumentosSistema()->add($instrumento);
            }

            foreach ($form->get('instrumentos_personalizados')->getData() as $instrumento) {
                $musico->getInstrumentosPersonalizados()->add($instrumento);
            }

            foreach ($form->get('generos_musicales')->getData() as $genero) {
                $musico->addGeneroMusical($genero);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Perfil actualizado.');
            return $this->redirectToRoute('app_musico_show', ['id' => $musico->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('musico/edit.html.twig', [
            'musico' => $musico,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_musico_delete', methods: ['POST'])]
    public function delete(Request $request, Musico $musico, EntityManagerInterface $entityManager): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        if ($musico->getUsuario() !== $usuario) {
            throw $this->createAccessDeniedException('No se puede borrar este perfil desde este usuario.');
        }

        if ($this->isCsrfTokenValid('delete' . $musico->getId(), $request->getPayload()->getString('_token'))) {

            // Comprobar si el músico pertenece a alguna banda activa
            $bandasActivas = $musico->getMiembroBandas()->filter(
                fn($mb) => in_array($mb->getEstado(), ['aceptado', 'pendiente', 'invitado'])
            );

            if (!$bandasActivas->isEmpty()) {
                $this->addFlash('error', 'No puedes eliminar tu perfil mientras pertenezcas a una banda. Sal de todas las bandas primero.');
                return $this->redirectToRoute('app_musico_show', ['id' => $musico->getId()], Response::HTTP_SEE_OTHER);
            }

            $usuario->setMusico(null);
            $entityManager->remove($musico);
            $entityManager->flush();
            $this->addFlash('success', 'Perfil borrado correctamente.');
        }

        return $this->redirectToRoute('app_inicio', [], Response::HTTP_SEE_OTHER);
    }
}