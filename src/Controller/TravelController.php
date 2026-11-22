<?php

namespace App\Controller;

use App\Entity\Member;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/travel')]
final class TravelController extends AbstractController
{
    #[Route(name: 'app_travel_index', methods: ['GET'])]
    public function index(TravelRepository $travelRepository): Response
    {
        if($this->isGranted('ROLE_ADMIN')) {
            $travels = $travelRepository->findAll();
        }
        else {
            $travels = $travelRepository->findBy(['published' => true]);
            //dump($travels);
            if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
                // fetch the member's travels, either published or not
                $memberTravels = $this->getUser()->getTravels();
                // merge both arrays and remove duplicates
                $travels = array_merge($travels, $memberTravels->toArray());
                $travels = array_unique($travels);
            }
        }
        return $this->render('travel/index.html.twig', [
            'travel' => $travels,
        ]);
    }

    #[Route('/new/{id}', name: 'app_travel_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, Member $member): Response
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $member);
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot create a travel for another member!");
        }
        $travel = new Travel();
        $travel->setCreator($member);
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($travel);
            $entityManager->flush();
            // Make sure message will be displayed after redirect
            $this->addFlash('message', 'Voyage bien ajouté');

            return $this->redirectToRoute('app_member_show',
                                          ['id' => $member->getId()],
                                          Response::HTTP_SEE_OTHER);
        }

        return $this->render('travel/new.html.twig', [
            'member' => $member,
            'travel' => $travel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travel_show', methods: ['GET'])]
    public function show(Travel $travel): Response
    {
        $hasAccess = false;
        if($this->isGranted('ROLE_ADMIN') || $travel->isPublished()) {
                $hasAccess = true;
        }
        else {
                $member = $this->getUser();
                if ( $member &&  ($member == $travel->getCreator()) ) {
                    $hasAccess = true;
                }
        }
        if(! $hasAccess) {
                throw $this->createAccessDeniedException("You cannot access the requested resource!");
        }
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
        $hasAccess = false;
        if($this->isGranted('ROLE_ADMIN') || $travel->isPublished()) {
          $hasAccess = true;
        }
        else {
          $member = $this->getUser();
          if ( $member &&  ($member == $travel->getCreator()) ) {
              $hasAccess = true;
          }
        }
        if(! $hasAccess) {
          throw $this->createAccessDeniedException("You cannot access the requested ressource!");
        }
        return $this->render('travel/memory_show.html.twig', [
            'memory' => $memory,
            'travel' => $travel
        ]);
    }

    /**
     * Mark a memory of a travel as bookmarked in the user's session (or unmark it)
     *
     * @param Travel $travel the travel which displays the memory (galerie)
     * @param Memory $memory the memory to bookmark (objet)
     */
    #[Route('/{travel_id}/memory/{memory_id}/mark',
        methods: ['GET'],
        name: 'app_travel_memory_mark',
        requirements: ['travel_id' => '\d+',
                       'memory_id' => '\d+'
                      ])]
    public function memoryMark(
        Request $request,
        #[MapEntity(id: 'travel_id')]
        Travel $travel,
        #[MapEntity(id: 'memory_id')]
        Memory $memory
    ): Response
    {
        if(! $travel->getMemories()->contains($memory)) {
            throw $this->createNotFoundException("Couldn't find such a memory in this travel!");
        }
        $hasAccess = false;
        if($this->isGranted('ROLE_ADMIN') || $travel->isPublished()) {
          $hasAccess = true;
        }
        else {
          $member = $this->getUser();
          if ( $member &&  ($member == $travel->getCreator()) ) {
              $hasAccess = true;
          }
        }
        if(! $hasAccess) {
          throw $this->createAccessDeniedException("You cannot access the requested ressource!");
        }

        // Récupération du tableau d'id des favoris dans la session
        $bookmarks = $request->getSession()->get('bookmarks');
        if( ! is_array($bookmarks) ) {
            $bookmarks = array();
        }
        $id = $memory->getId();
        // si l'identifiant n'est pas présent dans le tableau des favoris, l'ajouter
        if (! in_array($id, $bookmarks) )
        {
            $bookmarks[] = $id;
            $this->addFlash('message', 'Souvenir ajouté aux favoris');
        }
        else
        // sinon, le retirer du tableau
        {
            // substract two arrays
            $bookmarks = array_diff($bookmarks, array($id));
            $this->addFlash('message', 'Souvenir retiré des favoris');
        }
        // Sauvegarde du tableau d'id des favoris dans la session
        $request->getSession()->set('bookmarks', $bookmarks);

        return $this->redirectToRoute('app_travel_memory_show',
                                      ['travel_id' => $travel->getId(),
                                       'memory_id' => $memory->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_travel_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Travel $travel, EntityManagerInterface $entityManager): Response
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $travel->getCreator());
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot modify another member's travel!");
        }
        $form = $this->createForm(TravelType::class, $travel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('message', 'Voyage bien modifié');

            return $this->redirectToRoute('app_member_show', ['id' => $travel->getCreator()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('travel/edit.html.twig', [
            'travel' => $travel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travel_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Travel $travel, EntityManagerInterface $entityManager): Response
    {
        $member = $travel->getCreator();
        $hasAccess = $this->isGranted('ROLE_ADMIN') ||
            ($this->getUser() == $travel->getCreator());
        if(! $hasAccess) {
            throw $this->createAccessDeniedException("You cannot modify another member's travel!");
        }
        if ($this->isCsrfTokenValid('delete'.$travel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($travel);
            $entityManager->flush();
            $this->addFlash('message', 'Voyage bien supprimé');
        }

        return $this->redirectToRoute('app_member_show', ['id' => $member->getId()], Response::HTTP_SEE_OTHER);
    }
}
