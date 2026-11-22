<?php

namespace App\Controller;

use App\Entity\Mind;
use App\Repository\MindRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MindController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->redirectToRoute('app_member_index');
    }

    #[Route('/mind', name: 'app_mind', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(MindRepository $mindRepository): Response
    {
        if($this->isGranted('ROLE_ADMIN')) {
            $minds = $mindRepository->findAll();
        }
        else {
            // a normal member only has access to his own mind
            $minds = $mindRepository->findBy(['member' => $this->getUser()]);
        }

        return $this->render('mind/index.html.twig', [
            'minds' => $minds,
        ]);
    }
    #[Route('/mind/{id}', name: 'app_mind_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(Mind $mind): Response
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $mind->getMember());
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot access another member's mind!");
        }

        return $this->render('mind/show.html.twig', [
            'mind' => $mind,
        ]);
    }

}
