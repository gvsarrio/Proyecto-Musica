<?php

namespace App\Controller;

use App\Entity\Instrumento;
use App\Entity\InstrumentoMusico;
use App\Entity\Musico;
use App\Form\MusicoType;
use App\Repository\InstrumentoMusicoRepository;
use App\Repository\MusicoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class MusicoController extends AbstractController
{
    #[Route('/musico', name: 'app_musico')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MusicoRepository $musicoRepository,
        InstrumentoMusicoRepository $instrumentoMusicoRepository,
        SluggerInterface $slugger
    ): Response {
        // Solo usuarios autenticados sin rol admin pueden acceder
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        /** @var \App\Entity\Usuario $usuario */
        $usuario = $this->getUser();

        // Si el usuario ya tiene perfil músico lo cargamos, si no, creamos uno nuevo
        $musico = $musicoRepository->findOneBy(['user_id' => $usuario]);
        if (!$musico) {
            $musico = new Musico();
            $musico->setUserId($usuario);
        }

        $form = $this->createForm(MusicoType::class, $musico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestión de la subida de imagen
            $imagenFile = $form->get('imagen_url')->getData();
            if ($imagenFile) {
                $nombreOriginal = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                $nombreSeguro = $slugger->slug($nombreOriginal);
                $nuevoNombre = $nombreSeguro . '-' . uniqid() . '.' . $imagenFile->guessExtension();
                $imagenFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/profile',
                    $nuevoNombre
                );
                $musico->setImagenUrl($nuevoNombre);
            }

            // Si es un nuevo perfil, establecemos la fecha de creación
            if (!$musico->getCreadoEn()) {
                $musico->setCreadoEn(new \DateTime());
            }
            $musico->setActualizadoEn(new \DateTime());

            $entityManager->persist($musico);
            $entityManager->flush();

            // Gestión de instrumentos — borramos los anteriores y añadimos los nuevos
            $instrumentosAnteriores = $instrumentoMusicoRepository->findBy(['musico' => $musico]);
            foreach ($instrumentosAnteriores as $anterior) {
                $entityManager->remove($anterior);
            }
            $entityManager->flush();

            // Guardamos los instrumentos seleccionados en instrumento_musico
            $instrumentosSeleccionados = $form->get('instrumentos')->getData();
            foreach ($instrumentosSeleccionados as $instrumento) {
                $instrumentoMusico = new InstrumentoMusico();
                $instrumentoMusico->setMusico($musico);
                $instrumentoMusico->setInstrumento($instrumento);
                $entityManager->persist($instrumentoMusico);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Perfil actualizado correctamente.');
            return $this->redirectToRoute('app_musico');
        }

        // Cargamos los instrumentos actuales del músico para mostrarlos seleccionados
        $instrumentosActuales = array_map(
            fn($im) => $im->getInstrumento(),
            $instrumentoMusicoRepository->findBy(['musico' => $musico])
        );

        return $this->render('musico/musico.html.twig', [
            'form' => $form,
            'musico' => $musico,
            'instrumentosActuales' => $instrumentosActuales,
        ]);
    }

    #[Route('/instrumento/nuevo', name: 'app_instrumento_nuevo', methods: ['POST'])]
    public function nuevoInstrumento(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Endpoint para que el JS pueda crear un instrumento nuevo
        $this->denyAccessUnlessGranted('ROLE_USER');

        $nombre = $request->request->get('nombre');
        if (!$nombre) {
            return $this->json(['error' => 'Nombre requerido'], 400);
        }

        // Comprueba si ya existe para evitar duplicados
        $existe = $entityManager->getRepository(Instrumento::class)->findOneBy(['nombre' => $nombre]);
        if ($existe) {
            return $this->json(['id' => $existe->getId(), 'nombre' => $existe->getNombre()]);
        }

        $instrumento = new Instrumento();
        $instrumento->setNombre($nombre);
        $entityManager->persist($instrumento);
        $entityManager->flush();

        return $this->json(['id' => $instrumento->getId(), 'nombre' => $instrumento->getNombre()]);
    }
}
