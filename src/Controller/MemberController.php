<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/member')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class MemberController extends AbstractController
{
    #[Route(name: 'app_member_index', methods: ['GET'])]
    public function index(MemberRepository $memberRepository): Response
    {
        if($this->isGranted('ROLE_ADMIN')) {
            $members = $memberRepository->findAll();
        }
        else {
            // a normal member only has access to his own data
            $members = [$this->getUser()];
        }

        return $this->render('member/index.html.twig', [
            'members' => $members,
        ]);
    }

    #[Route('/{id}', name: 'app_member_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Member $member): Response
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $member);
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot access another member's data!");
        }

        return $this->render('member/show.html.twig', [
            'member' => $member,
        ]);
    }
}
