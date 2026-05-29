<?php

namespace App\Controller;

use App\Entity\Musico;
use App\Entity\Usuario;
use App\Form\MusicoType;
use App\Repository\MusicoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/musico')]
final class MusicoController extends AbstractController
{
    #[Route('/list', name: 'app_musico_index', methods: ['GET'])]
    public function index(MusicoRepository $musicoRepository): Response
    {
        return $this->render('musico/index.html.twig', [
            'musicos' => $musicoRepository->findAll(),
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
            $entityManager->flush();

            foreach ($form->get('instrumentos_sistema')->getData() as $instrumento) {
                $musico->getInstrumentosSistema()->add($instrumento);
            }

            foreach ($form->get('instrumentos_personalizados')->getData() as $instrumento) {
                $musico->getInstrumentosPersonalizados()->add($instrumento);
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
            $usuario->setMusico(null);
            $entityManager->remove($musico);
            $entityManager->flush();
            $this->addFlash('success', 'Perfil borrado correctamente.');
        }

        return $this->redirectToRoute('app_inicio', [], Response::HTTP_SEE_OTHER);
    }
}
