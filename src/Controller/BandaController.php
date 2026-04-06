<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BandaController extends AbstractController
{
    #[Route('/banda', name: 'app_banda')]
    public function index(): Response
    {
        return $this->render('banda/index.html.twig', [
            'controller_name' => 'BandaController',
        ]);
    }
}
