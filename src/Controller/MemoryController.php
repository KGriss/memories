<?php

namespace App\Controller;

use App\Entity\Memory;
use App\Entity\Mind;
use App\Form\MemoryType;
use App\Repository\MemoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/memory')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class MemoryController extends AbstractController
{
    #[Route(name: 'app_memory_index', methods: ['GET'])]
    public function index(MemoryRepository $memoryRepository): Response
    {
        if($this->isGranted('ROLE_ADMIN')) {
            $memories = $memoryRepository->findAll();
        }
        else {
            // a normal member only has access to the memories of his own mind
            $memories = $memoryRepository->findBy(['mind' => $this->getUser()->getMind()]);
        }

        return $this->render('memory/index.html.twig', [
            'memories' => $memories,
        ]);
    }

    #[Route('/new/{id}', name: 'app_memory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Mind $mind): Response
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $mind->getMember());
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot access another member's mind!");
        }
        $memory = new Memory();
        $memory->setMind($mind);
        $form = $this->createForm(MemoryType::class, $memory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($memory);
            $entityManager->flush();
            // Make sure message will be displayed after redirect
            $this->addFlash('message', 'Souvenir bien ajouté');

            return $this->redirectToRoute('app_mind_show',
                                          ['id' => $mind->getId()],
                                          Response::HTTP_SEE_OTHER);
        }

        return $this->render('memory/new.html.twig', [
            'mind' => $mind,
            'memory' => $memory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_memory_show', methods: ['GET'])]
    public function show(Memory $memory): Response
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $memory->getMind()->getMember());
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot access another member's memory!");
        }
        return $this->render('memory/show.html.twig', [
            'memory' => $memory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_memory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Memory $memory, EntityManagerInterface $entityManager): Response
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $memory->getMind()->getMember());
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot access another member's memory!");
        }
        $form = $this->createForm(MemoryType::class, $memory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('message', 'Souvenir bien modifié');

            return $this->redirectToRoute('app_mind_show', ['id' => $memory->getMind()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('memory/edit.html.twig', [
            'memory' => $memory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_memory_delete', methods: ['POST'])]
    public function delete(Request $request, Memory $memory, EntityManagerInterface $entityManager): Response
    {
        $mind = $memory->getMind();
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $memory->getMind()->getMember());
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot access another member's memory!");
        }
        if ($this->isCsrfTokenValid('delete'.$memory->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($memory);
            $entityManager->flush();
            $this->addFlash('message', 'Souvenir bien supprimé');
        }

        return $this->redirectToRoute('app_mind_show', ['id' => $mind->getId()], Response::HTTP_SEE_OTHER);
    }
}
