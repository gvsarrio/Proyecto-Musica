<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Usuario;
use App\Entity\Mensaje;
use App\Form\MensajeType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Conversacion;
use App\Repository\ConversacionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MensajeRepository;

final class MensajesController extends AbstractController
{
    #[Route('/mensajes', name: 'app_mensajes')]
    public function index(ConversacionRepository $conversacionRepository, MensajeRepository $mensajeRepository): Response
    {
        $usuarioActual = $this->getUser();

        if (!$usuarioActual instanceof Usuario) {
            throw $this->createAccessDeniedException();
        }

        $conversaciones = $conversacionRepository
            ->buscarPorUsuario($usuarioActual);

        $noLeidosPorConversacion = [];

        foreach ($conversaciones as $conversacion) {

            $noLeidosPorConversacion[$conversacion->getId()] =
                $mensajeRepository->contarNoLeidosEnConversacion(
                    $conversacion,
                    $usuarioActual
                );
        }

        return $this->render(
            'mensajes/index.html.twig',
            [
                'conversaciones' => $conversaciones,
                'noLeidosPorConversacion' => $noLeidosPorConversacion,
            ]
        );
    }

    #[Route('/mensajes/nuevo/{id}', name: 'mensaje_nuevo')]
    public function nuevo(Request $request, Usuario $destinatario, ConversacionRepository $conversacionRepository, EntityManagerInterface $entityManager): Response
    {
        $usuarioActual = $this->getUser();

        if (!$usuarioActual instanceof Usuario) {
            throw $this->createAccessDeniedException();
        }

        if ($usuarioActual === $destinatario) {
            throw $this->createAccessDeniedException(
                'No puedes enviarte mensajes a ti mismo.'
            );
        }

        $conversacionExistente = $conversacionRepository
            ->buscarEntreUsuarios(
                $usuarioActual,
                $destinatario
            );

        if ($conversacionExistente) {
            return $this->redirectToRoute(
                'mensaje_conversacion',
                [
                    'id' => $conversacionExistente->getId(),
                ]
            );
        }

        $mensaje = new Mensaje();

        $form = $this->createForm(
            MensajeType::class,
            $mensaje
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $conversacion = $conversacionRepository
                ->buscarEntreUsuarios(
                    $usuarioActual,
                    $destinatario
                );

            if (!$conversacion) {

                $conversacion = new Conversacion();

                $conversacion->setUsuarioUno($usuarioActual);
                $conversacion->setUsuarioDos($destinatario);

                $entityManager->persist($conversacion);
            }

            $mensaje->setConversacion($conversacion);
            $mensaje->setRemitente($usuarioActual);
            $mensaje->setLeido(false);

            $conversacion->setFechaUltimoMensaje(
                $mensaje->getFechaEnvio()
            );

            $entityManager->persist($mensaje);

            $entityManager->flush();

            return $this->redirectToRoute(
                'mensaje_conversacion',
                [
                    'id' => $conversacion->getId(),
                ]
            );
        }

        return $this->render('mensajes/nuevo.html.twig', [
            'destinatario' => $destinatario,
            'form' => $form,
        ]);
    }

    #[Route('/mensajes/conversacion/{id}', name: 'mensaje_conversacion')]
    public function conversacion(Request $request, Conversacion $conversacion, EntityManagerInterface $entityManager): Response
    {
        $usuarioActual = $this->getUser();

        if (!$usuarioActual instanceof Usuario) {
            throw $this->createAccessDeniedException();
        }

        if (
            $conversacion->getUsuarioUno() !== $usuarioActual
            && $conversacion->getUsuarioDos() !== $usuarioActual
        ) {
            throw $this->createAccessDeniedException(
                'No tienes acceso a esta conversación.'
            );
        }

        foreach ($conversacion->getMensajes() as $mensaje) {

            if (
                $mensaje->getRemitente() !== $usuarioActual
                && !$mensaje->isLeido()
            ) {
                $mensaje->setLeido(true);
            }
        }

        $entityManager->flush();

        $mensaje = new Mensaje();

        $form = $this->createForm(
            MensajeType::class,
            $mensaje
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $mensaje->setConversacion($conversacion);
            $mensaje->setRemitente($usuarioActual);

            $conversacion->setFechaUltimoMensaje(
                $mensaje->getFechaEnvio()
            );

            $entityManager->persist($mensaje);

            $entityManager->flush();

            return $this->redirectToRoute(
                'mensaje_conversacion',
                [
                    'id' => $conversacion->getId(),
                ]
            );
        }

        return $this->render(
            'mensajes/conversacion.html.twig',
            [
                'conversacion' => $conversacion,
                'form' => $form,
            ]
        );
    }
}
