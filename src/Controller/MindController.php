<?php

namespace App\Controller;

use App\Entity\Mind;
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
    #[Route("/mind/{id}", name: "app_mind_show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Mind $mind): Response
    {
        return $this->render("mind/show.html.twig", [
            "mind" => $mind,
        ]);
    }

}
