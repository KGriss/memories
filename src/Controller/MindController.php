<?php

namespace App\Controller;

use App\Repository\MindRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MindController extends AbstractController
{
    #[Route('/mind', name: 'app_mind', methods: ['GET'])]
    public function index(MindRepository $mindRepository): Response
    {
        $minds = $mindRepository->findAll();

        return $this->render('mind/index.html.twig', [
            'minds' => $minds,
        ]);
    }
}
