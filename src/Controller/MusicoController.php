<?php

namespace App\Controller;

use App\Entity\Musico;
use App\Entity\InstrumentoMusico;
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
        // Obtener el usuario logueado:
        $usuario = $this->getUser();

        // Si no hay usuario logueado, lanza excepción:
        if (!$usuario) {
            throw $this->createAccessDeniedException();
        }

        // Comprobar que el usuario no tiene ya un perfil creado. Si lo tiene, lanza excepción:
        if ($usuario->getMusico() !== null) {
            throw $this->createAccessDeniedException('Ya existe un perfil creado por este usuario');
        }

        $musico = new Musico();
        $form = $this->createForm(MusicoType::class, $musico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $musico = $form->getData();

            // ASOCIAMOS EL USUARIO LOGUEADO CON EL PERFIL DE MUSICO QUE ESTAMOS CREANDO:
            $musico->setUsuario($usuario);


            // 1. Guardamos el músico primero para que Doctrine genere su ID
            $entityManager->persist($musico);

            // 2. Extraemos los instrumentos del campo NO mapeado
            $instrumentosSeleccionados = $form->get('instrumentos')->getData();

            // 3. Creamos manualmente las relaciones en la tabla intermedia
            foreach ($instrumentosSeleccionados as $instrumento) {
                $relacion = new InstrumentoMusico();
                $relacion->setMusico($musico);
                $relacion->setInstrumento($instrumento);

                $entityManager->persist($relacion);
            }


            // Ejecutamos todo en la base de datos
            $entityManager->flush();

            $fotoArchivo = $form->get('imagen_url')->getData();

            if ($fotoArchivo) {
                // 1. Crear un nombre único para que no se machaquen fotos con el mismo nombre
                $nuevoNombre = uniqid() . '.' . $fotoArchivo->guessExtension();

                // 2. Mover el archivo a la carpeta deseada (definida en el paso 3)
                try {
                    $fotoArchivo->move(
                        $this->getParameter('perfiles_directory'),
                        $nuevoNombre
                    );
                    // 3. Guardar el nombre en la base de datos
                    $musico->setImagenUrl($nuevoNombre);
                } catch (FileException $e) {
                    // ... gestionar error si falla la subida
                }
            }

            $this->addFlash('success', '¡Perfil creado con éxito!');
            return $this->redirectToRoute('app_musico_index'); // Ajusta a tu ruta
        }

        return $this->render('musico/new.html.twig', [
            'musico' => $musico,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_musico_show', methods: ['GET'])]
    public function show(Musico $musico): Response
    {
        return $this->render('musico/show.html.twig', [
            'musico' => $musico,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_musico_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Musico $musico, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MusicoType::class, $musico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_musico_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('musico/edit.html.twig', [
            'musico' => $musico,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_musico_delete', methods: ['POST'])]
    public function delete(Request $request, Musico $musico, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $musico->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($musico);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_musico_index', [], Response::HTTP_SEE_OTHER);
    }
}
