<?php

namespace App\Controller;

use App\Entity\Memory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MemoryController extends AbstractController
{
    #[Route('/memory/{id}', name: 'app_memory_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Memory $memory): Response
    {
        return $this->render('memory/show.html.twig', [
            'memory' => $memory,
        ]);
    }
}
