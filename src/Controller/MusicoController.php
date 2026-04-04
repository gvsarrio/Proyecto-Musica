<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MusicoController extends AbstractController
{
    #[Route('/musico', name: 'app_musico')]
    public function index(): Response
    {
        return $this->render('musico/index.html.twig', [
            'controller_name' => 'MusicoController',
        ]);
    }
}
