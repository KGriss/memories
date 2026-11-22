<?php

namespace App\Controller;

use App\Entity\Memory;
use App\Entity\Travel;
use App\Form\TravelType;
use App\Repository\TravelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/travel')]
final class TravelController extends AbstractController
{
    #[Route(name: 'app_travel_index', methods: ['GET'])]
    public function index(TravelRepository $travelRepository): Response
    {
        return $this->render('travel/index.html.twig', [
            'travel' => $travelRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_travel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $travel = new Travel();
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($travel);
            $entityManager->flush();

            return $this->redirectToRoute('app_travel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('travel/new.html.twig', [
            'travel' => $travel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travel_show', methods: ['GET'])]
    public function show(Travel $travel): Response
    {
        return $this->render('travel/show.html.twig', [
            'travel' => $travel,
        ]);
    }

    /**
     * Show a memory in the context of a travel
     *
     * @param Travel $travel the travel which displays the memory (galerie)
     * @param Memory $memory the memory to display (objet)
     */
    #[Route('/{travel_id}/memory/{memory_id}',
        methods: ['GET'],
        name: 'app_travel_memory_show',
        requirements: ['travel_id' => '\d+',
                       'memory_id' => '\d+'
                      ])]
    public function memoryShow(
        #[MapEntity(id: 'travel_id')]
        Travel $travel,
        #[MapEntity(id: 'memory_id')]
        Memory $memory
    ): Response
    {
        if(! $travel->getMemories()->contains($memory)) {
            throw $this->createNotFoundException("Couldn't find such a memory in this travel!");
        }
        // if(! $travel->isPublished()) {
        //   throw $this->createAccessDeniedException("You cannot access the requested ressource!");
        //}
        return $this->render('travel/memory_show.html.twig', [
            'memory' => $memory,
            'travel' => $travel
        ]);
    }

    #[Route('/{id}/edit', name: 'app_travel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Travel $travel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_travel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('travel/edit.html.twig', [
            'travel' => $travel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travel_delete', methods: ['POST'])]
    public function delete(Request $request, Travel $travel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$travel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($travel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_travel_index', [], Response::HTTP_SEE_OTHER);
    }
}
