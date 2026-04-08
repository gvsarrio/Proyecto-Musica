<?php

namespace App\Controller;

use App\Entity\Musico;
use App\Form\MusicoType;
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
    public function index(Request $request, EntityManagerInterface $entityManager, MusicoRepository $musicoRepository, SluggerInterface $slugger): Response
    {
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
                // Genera un nombre seguro y único para evitar colisiones
                $nombreOriginal = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                $nombreSeguro = $slugger->slug($nombreOriginal);
                $nuevoNombre = $nombreSeguro . '-' . uniqid() . '.' . $imagenFile->guessExtension();

                // Mueve el archivo a la carpeta pública de imágenes de perfil
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

            // Siempre actualizamos la fecha cuando se guarda el perfil
            $musico->setActualizadoEn(new \DateTime());

            $entityManager->persist($musico);
            $entityManager->flush();

            $this->addFlash('success', 'Perfil actualizado correctamente.');

            return $this->redirectToRoute('app_musico');
        }

        return $this->render('musico/musico.html.twig', [
            'form' => $form,
            'musico' => $musico,
        ]);
    }
}
