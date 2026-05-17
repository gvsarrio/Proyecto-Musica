<?php

namespace App\Controller;

use App\Entity\Musico;
use App\Entity\Instrumento;
use App\Entity\InstrumentoMusico;
use App\Entity\Usuario;
use App\Form\MusicoType;
use App\Repository\MusicoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $form = $this->createForm(MusicoType::class, $musico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $musico = $form->getData();
            $musico->setUsuario($usuario);

            $fotoArchivo = $form->get('imagen_url')->getData();

            if ($fotoArchivo) {
                $nuevoNombre = uniqid() . '.' . $fotoArchivo->guessExtension();

                try {
                    $fotoArchivo->move(
                        $this->getParameter('perfiles_directory'),
                        $nuevoNombre
                    );
                    $musico->setImagenUrl($nuevoNombre);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo guardar la imagen.');
                }
            }

            $entityManager->persist($musico);

            $instrumentosSeleccionados = $form->get('instrumentos')->getData();

            // Si el usuario escribió un instrumento nuevo y no pulsó el botón AJAX,
            // lo creamos aquí para asegurarnos de que exista y se asocie.
            $nuevoInstrumento = trim($form->get('instrumentos_nuevos')->getData() ?? '');
            if ($nuevoInstrumento !== '') {
                $repoInstrumento = $entityManager->getRepository(Instrumento::class);
                $instrumentoExistente = $repoInstrumento->findOneBy(['nombre' => $nuevoInstrumento]);
                if (!$instrumentoExistente) {
                    $instrumentoExistente = new Instrumento();
                    $instrumentoExistente->setNombre($nuevoInstrumento);
                    $entityManager->persist($instrumentoExistente);
                    // flush later
                }
                $instrumentosSeleccionados[] = $instrumentoExistente;
            }

            foreach ($instrumentosSeleccionados as $instrumento) {
                $relacion = new InstrumentoMusico();
                $relacion->setMusico($musico);
                $relacion->setInstrumento($instrumento);
                $entityManager->persist($relacion);
            }

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
        return $this->render('musico/show.html.twig', [
            'musico' => $musico,
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

        // --- LÓGICA DE CARGA DE INSTRUMENTOS ---
        // Extraemos los instrumentos actuales de la tabla intermedia para que aparezcan marcados en el form
        $instrumentosActuales = [];
        foreach ($musico->getInstrumentoMusicos() as $relacion) {
            $instrumentosActuales[] = $relacion->getInstrumento();
        }

        $form = $this->createForm(MusicoType::class, $musico);
        
        // Seteamos los datos en el campo NO mapeado 'instrumentos'
        $form->get('instrumentos')->setData($instrumentosActuales);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // --- PROCESAMIENTO DE IMAGEN EN EDICIÓN ---
            $fotoArchivo = $form->get('imagen_url')->getData();
            if ($fotoArchivo) {
                $nuevoNombre = uniqid() . '.' . $fotoArchivo->guessExtension();
                try {
                    $fotoArchivo->move(
                        $this->getParameter('perfiles_directory'),
                        $nuevoNombre
                    );
                    $musico->setImagenUrl($nuevoNombre);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo actualizar la imagen.');
                }
            }

            // --- ACTUALIZACIÓN DE INSTRUMENTOS (BORRAR Y CREAR) ---
            // 1. Borramos las relaciones antiguas
            foreach ($musico->getInstrumentoMusicos() as $relacionAntigua) {
                $entityManager->remove($relacionAntigua);
            }
            // Hacemos un flush intermedio para evitar conflictos de claves únicas si los hubiera
            $entityManager->flush();

                        // 2. Creamos las nuevas relaciones seleccionadas
                        $instrumentosSeleccionados = $form->get('instrumentos')->getData();

                        // Si el usuario escribió un instrumento nuevo y no pulsó el botón AJAX,
                        // lo creamos aquí para asegurarnos de que exista y se asocie.
                        $nuevoInstrumento = trim($form->get('instrumentos_nuevos')->getData() ?? '');
                        if ($nuevoInstrumento !== '') {
                            $repoInstrumento = $entityManager->getRepository(Instrumento::class);
                            $instrumentoExistente = $repoInstrumento->findOneBy(['nombre' => $nuevoInstrumento]);
                            if (!$instrumentoExistente) {
                                $instrumentoExistente = new Instrumento();
                                $instrumentoExistente->setNombre($nuevoInstrumento);
                                $entityManager->persist($instrumentoExistente);
                            }
                            $instrumentosSeleccionados[] = $instrumentoExistente;
                        }

                        foreach ($instrumentosSeleccionados as $instrumento) {
                            $relacion = new InstrumentoMusico();
                            $relacion->setMusico($musico);
                            $relacion->setInstrumento($instrumento);
                            $entityManager->persist($relacion);
                        }

            $entityManager->flush();

            $this->addFlash('success', 'Perfil actualizado.');
            
            return $this->redirectToRoute('app_musico_show', [
                'id' => $musico->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('musico/edit.html.twig', [
            'musico' => $musico,
            'form' => $form,
        ]);
    }

    #[Route('/instrumento/add', name: 'app_instrumento_add', methods: ['POST'])]
    public function addInstrument(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('add_instrument', $token)) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        $nombre = trim((string) $request->request->get('nombre', ''));
        if ($nombre === '') {
            return new JsonResponse(['error' => 'Nombre vacío'], Response::HTTP_BAD_REQUEST);
        }

        $repo = $entityManager->getRepository(Instrumento::class);
        $existing = $repo->findOneBy(['nombre' => $nombre]);
        if ($existing) {
            return new JsonResponse(['id' => $existing->getId(), 'nombre' => $existing->getNombre(), 'exists' => true]);
        }

        $instrumento = new Instrumento();
        $instrumento->setNombre($nombre);
        $entityManager->persist($instrumento);
        $entityManager->flush();

        return new JsonResponse(['id' => $instrumento->getId(), 'nombre' => $instrumento->getNombre()]);
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