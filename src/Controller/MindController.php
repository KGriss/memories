<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MindController extends AbstractController
{
    #[Route('/mind', name: 'app_mind')]
    public function index(): Response
    {
        return $this->render('mind/index.html.twig', [
            'controller_name' => 'MindController',
        ]);
    }
}
