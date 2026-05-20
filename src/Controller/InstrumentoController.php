<?php

namespace App\Controller;

use App\Entity\Instrumento;
use App\Repository\InstrumentoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/instrumento')]
final class InstrumentoController extends AbstractController
{
    #[Route('/add', name: 'app_instrumento_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em, InstrumentoRepository $repo): JsonResponse
    {
        if (!$this->isCsrfTokenValid('add_instrument', $request->request->get('_token'))) {
            return $this->json(['error' => 'Token inválido'], 403);
        }

        $nombre = trim($request->request->get('nombre', ''));

        if ($nombre === '') {
            return $this->json(['error' => 'El nombre no puede estar vacío'], 400);
        }

        $existente = $repo->findOneBy(['nombre' => $nombre]);
        if ($existente) {
            return $this->json(['error' => 'Ese instrumento ya existe', 'id' => $existente->getId(), 'nombre' => $existente->getNombre()], 409);
        }

        $instrumento = new Instrumento();
        $instrumento->setNombre($nombre);
        $instrumento->setUsuario($this->getUser());
        $em->persist($instrumento);
        $em->flush();

        return $this->json(['id' => $instrumento->getId(), 'nombre' => $instrumento->getNombre()], 201);
    }
}
