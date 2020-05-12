<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Form\DiscussionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DiscussionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    /**
     * @Route("/forum", name="forum")
     */
    public function accueil( DiscussionRepository $repo )
    {
        $derniereDisc = $repo->findAllDesc();

        return $this->render('forum/index.html.twig', [
            'discussions' =>  $derniereDisc,
        ]);
    }

    /**
     * @Route("/forum/creer", name="createDisc")
     */
    public function creation(Request $request, EntityManagerInterface $manager, DiscussionRepository $repo )
    {
        $discussion = new Discussion();
        $formDiscussion = $this->createForm(DiscussionType::class, $discussion);

        $formDiscussion->handleRequest($request);

        if($formDiscussion->isSubmitted() && $formDiscussion->isValid())
        {
            $user = $this->getUser();

            $discussion->setId_utilisateur( $user );
            $discussion->setCreation( new \DateTime() );
            $manager->persist($discussion);
            $manager->flush();
            
            return $this->redirectToRoute('forum');
        }

        
            return $this->render('forum/ajout.html.twig', [
                'fromDisc' =>  $formDiscussion->createview()
        ]);
        
    }

    /**
     * @Route("/forum/{id}", name="readDisc")
     */
    public function afficheDiscussion(Discussion $discussion) {
        return $this->render('forum/discussion.html.twig', [
            'discussion' => $discussion
        ]);
    }

    /**
     * @Route("/forum/{id}", name="readDisc", methods={"GET","POST"}, requirements={"id"="\d+"})
     */
    /*public function afficher( $id, Request $request, EntityManagerInterface $manager, DiscussionRepository $repo)
    {
        $discussion = $repo->find($id);
        
        
    }*/
}
