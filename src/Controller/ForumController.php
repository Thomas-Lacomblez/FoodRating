<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\Discussion;
use App\Entity\Utilisateurs;
use App\Form\DiscussionType;
use App\Repository\ReponseRepository;
use App\Repository\DiscussionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateursRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    /**
     * @Route("/forum", name="forum")
     */
    public function accueil(DiscussionRepository $repo, PaginatorInterface $paginator, Request $request )
    {
    	if (count($repo->findAll()) == 0) {
    		return $this->render("forum/no_result.html.twig");
    	}
    	
    	$derniereDisc = $repo->findAllDesc();
        
        $derniereDisc = $paginator->paginate(
        		$derniereDisc,
        		$request->query->getInt("page", 1),
        		10
        );
        
        // On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
        $derniereDisc->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
        
        // On aligne les sélecteurs au centre de la page
        $derniereDisc->setCustomParameters([
        		"align" => "center"
        ]);

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
     * @Route("/forum/signaler_utilisateur", name="signaler_utilisateur_forum")
     */
	public function signalerUtilisateur(Request $request, UtilisateursRepository $repoU){
		$manager = $this->getDoctrine()->getManager();
		if ($request->query->has("signaler_user")) {
			$user = $repoU->findOneBy(["id" => $request->query->get("signaler_user")]);
			if (!empty($user)){
				$user->setNombreSignalement($user->getNombreSignalement() + 1);
				$manager->persist($user);
            	$manager->flush();
				$this->addFlash(
					'signal_notice',
					'Vous avez signalé ' . $user->getUsername() . "."
				);
			}
		}
		if ($request->query->has("numerodiscussion")){
			return $this->redirectToRoute("readDisc", ["id" => $request->query->getInt("numerodiscussion")]);
		}
		else {
			return $this->redirectToRoute("forum");
		}

	}


    /**
     * @Route("/forum/{id}", name="readDisc")
     */
    public function afficheDiscussion($id, Request $request, Discussion $discussion, ReponseRepository $repo, PaginatorInterface $paginator, ?UserInterface $user) {
        function multi_in_array($value, $array) {
    		foreach ($array AS $item) {
        		if (!is_array($item)) {
            		if ($item == $value) {
                		return true;
            		}
            		continue;
       			}
        		if (in_array($value, $item)) {
            		return true;
        		}
        		else if (multi_in_array($value, $item)) {
            		return true;
        		}
    		}
    		return false;
        }
        
        if($user && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $filesystem = new Filesystem();
			if(!$filesystem->exists('csv/'. $user->getId())) {
				$filesystem->mkdir('csv/'. $user->getId(), 0700);
			}
			if(!$filesystem->exists('csv/'. $user->getId().'/forum.csv')) {
				$filesystem->touch('csv/'. $user->getId().'/forum.csv');
				$csv = fopen('csv/'. $user->getId().'/forum.csv', 'w');
				fputcsv($csv, array("id"), ";");
				fclose($csv);
			}
			$stream = fopen('csv/'. $user->getId().'/forum.csv', 'r');
			$ajoutCsv = array($id);
			$tabCsv = array();

			while (($result = fgetcsv($stream, 100, ";")) !== false) {
    			$tabCsv [] = $result;
			}

			if (count(file('csv/'. $user->getId().'/forum.csv')) == 1) {
				$csvModif = fopen('csv/'. $user->getId().'/forum.csv', 'a+');
				fputcsv($csvModif, $ajoutCsv, ";");
				fclose($csvModif);
			}
			elseif (!multi_in_array($ajoutCsv[0], $tabCsv)) {
				$csvModif = fopen('csv/'. $user->getId().'/forum.csv', 'a+');
				fputcsv($csvModif, $ajoutCsv, ";");
				fclose($csvModif);
			}
        }
    	$reponses = $repo->findBy(
    			['idDiscussion' => $discussion->getIdDiscussion()],
    			['createdAt' => 'asc']
    	);
    	
        $contenu = array_merge(array($discussion), (array) $reponses);
        dump($contenu);
    	$contenu = $paginator->paginate(
    			$contenu,
    			$request->query->getInt("page", 1),
    			10
    	);
    	
    	// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
    	$contenu->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
    	
    	// On aligne les sélecteurs au centre de la page
    	$contenu->setCustomParameters([
    			"align" => "center"
    	]);
    	    	
        return $this->render('forum/discussion_v2.html.twig', [
        	'discussion' => $discussion,
            'contenu' => $contenu
        ]);
    }
    
    /**
     * @Route("/forum/{id}/reponse", name="repDisc")
     */
    public function reponseDiscussion($id, Request $request, EntityManagerInterface $manager, ReponseRepository $repoR, UtilisateursRepository $repoU, DiscussionRepository $repoD) {
    	$reponse = new Reponse();
    	
    	$user = $repoU->findOneBy(["username" => $this->getUser()->getUsername()]);
    	$disc = $repoD->find($id);
    	
    	$message = $request->request->get("reponse");
    	if ($user != null && ($message != null && $message != "")) {
    		$reponse->setIdUtilisateur($user);
    		$reponse->setIdDiscussion($disc);
    		$reponse->setMessage($message);
    		$reponse->setCreatedAt(new \DateTime());	
    		
    		$manager->persist($reponse);
    		$manager->flush();
    		//dump($message, $user);
    		return $this->redirectToRoute("readDisc", ["id" => $id]);
    	}
    }

    /**
     * @Route("/forum/{id}", name="readDisc", methods={"GET","POST"}, requirements={"id"="\d+"})
     */
    /*public function afficher( $id, Request $request, EntityManagerInterface $manager, DiscussionRepository $repo)
    {
        $discussion = $repo->find($id);
        
        
    }*/
}
