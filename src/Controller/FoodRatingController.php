<?php

namespace App\Controller;

use App\Entity\Notes;
use OpenFoodFacts\Api;

use App\Entity\Commentaires;

use App\Entity\Utilisateurs;
use App\Entity\MoyenneProduits;
use Symfony\Component\Mime\Email;
use App\Repository\NotesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UtilisateursRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\MoyenneProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FoodRatingController extends AbstractController
{
    
    /**
     * @Route("/", name="food_rating")
     */
    public function home(MoyenneProduitsRepository $repo) {
		$api = new Api("food", "fr");
		$manager = $this->getDoctrine()->getManager();
		$moyenneProduits = $repo->findAll();
		if($moyenneProduits != null) {
			$idProduits = array();
			$produits = array();
			$notes = array();
			$meilleursProduit = array();
			$categorieProduit = array();
			for ($i = 0; $i < sizeof($moyenneProduits); $i++) {
				$produits[$moyenneProduits[$i]->getProduitId()] = array($moyenneProduits[$i]->getMoyenne(), $moyenneProduits[$i]->getCategorieProduit());
			}
			$idProduits = array_keys($produits);
			arsort($produits);
			$notes = array_slice($produits, 0, 5);
		
			$idProduits = array_keys($produits);
			
			for ($i = 0; $i < sizeof($notes); $i++) {
				$produit = $api->getProduct($idProduits[$i]);
				$categorieProduit [] = $produits[$idProduits[$i]][1];
				$meilleursProduit [] = $produit; 
			}
			dump($categorieProduit);

			return $this->render('food_rating/accueil.html.twig', [
				"meilleursProduit" => $meilleursProduit,
				"categorieProduit" => $categorieProduit
			]);
		}
		else {
			return $this->render('food_rating/accueil.html.twig');
		}
    }
    
    /**
     * @Route("/categories/{categorie}/produit_v2/{id}", name="produit_v2")
     */
    public function afficheProduitV2($id, $categorie, ?UserInterface $user, PaginatorInterface $paginator, Request $request) {
    	$api = new Api("food", "fr");
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		$manager = $this->getDoctrine()->getManager();
		$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
		$commentaireProduit = $manager->getRepository(Commentaires::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
		$saveNote = array();
		
		$collection = $api->getByFacets(["categorie" => $categorie]);
		$similaires = array();
		
		foreach ($collection as $key => $elt) {
			$dataSim = $elt->getData();
			// On veut éviter de voir le produit de la page dans les "similaires"
			if ($dataSim["id"] == $id || $dataSim["code"] == $id)
				continue;
			if (!isset($dataSim["brands_tags"][0]))
				continue;
			else if (isset($data["brands_tags"], $dataSim["brands_tags"]) && array_search($dataSim["brands_tags"][0], $data["brands_tags"]) !== false) {
				$similaires[] = $dataSim;
			} else {
				if (array_search($data["categories_tags"][1], $dataSim["categories_tags"]) !== false) {
					$similaires[] = $dataSim;
				}
			}
		}
		
		shuffle($similaires);
				
		if (!empty($notesProduit[0])) {
			for ($i = 0; $i < sizeof($notesProduit); $i++) {
				$saveNote[] = $notesProduit[$i]->getNbEtoiles();
			}

			$moyenneNote = round((array_sum($saveNote)/count($saveNote)), 2);
			$nombreVote = count($saveNote);
			$nombreEtoile1 = count(array_keys($saveNote, 1));
			$nombreEtoile2 = count(array_keys($saveNote, 2));
			$nombreEtoile3 = count(array_keys($saveNote, 3));
			$nombreEtoile4 = count(array_keys($saveNote, 4));
			$nombreEtoile5 = count(array_keys($saveNote, 5));
			$pourcentageEtoile1 = 0;
			$pourcentageEtoile2 = 0;
			$pourcentageEtoile3 = 0;
			$pourcentageEtoile4 = 0;
			$pourcentageEtoile5 = 0;

			if ($nombreEtoile1 != 0) {
				$pourcentageEtoile1 = (100*$nombreEtoile1)/count($saveNote);
			}
			if ($nombreEtoile2 != 0) {
				$pourcentageEtoile2 = (100*$nombreEtoile2)/count($saveNote);
			}
			if ($nombreEtoile3 != 0) {
				$pourcentageEtoile3 = (100*$nombreEtoile3)/count($saveNote);
			}
			if ($nombreEtoile4 != 0) {
				$pourcentageEtoile4 = (100*$nombreEtoile4)/count($saveNote);
			}
			if ($nombreEtoile5 != 0) {
				$pourcentageEtoile5 = (100*$nombreEtoile5)/count($saveNote);
			}
		}
		else {
			$moyenneNote = 0;
			$nombreVote = 0;
			$pourcentageEtoile1 = 0;
			$pourcentageEtoile2 = 0;
			$pourcentageEtoile3 = 0;
			$pourcentageEtoile4 = 0;
			$pourcentageEtoile5 = 0;
		}
		if ($user) {
			$note = $manager->getRepository(Notes::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id'] ?? $data['code']]);
			$commentaire = $manager->getRepository(Commentaires::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id'] ?? $data['code']]);
			if (!empty($note[0])) {
				if(!empty($commentaire[0])) {
					$noteProduit = $paginator->paginate(
						$notesProduit,
						$request->query->getInt("page", 1),
						9
					);
				
					$noteProduit->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
					$noteProduit->setCustomParameters([
						"align" => "center"
					]);
					return $this->render("food_rating/produit_v2.html.twig", [
						"produit" => $produit,
						"note" => $note[0],
						"notesProduit" =>$noteProduit,
						"commentaire" => $commentaire[0],
						"commentairesProduit" => $commentaireProduit,
						"moyenneNote" => $moyenneNote,
						"nombreVote" => $nombreVote,
						"pourcentageEtoile1" => $pourcentageEtoile1,
						"pourcentageEtoile2" => $pourcentageEtoile2,
						"pourcentageEtoile3" => $pourcentageEtoile3,
						"pourcentageEtoile4" => $pourcentageEtoile4,
						"pourcentageEtoile5" => $pourcentageEtoile5,
						"similaires" => $similaires,
						"categorie" => $categorie
					]);
				}
				$noteProduit = $paginator->paginate(
					$notesProduit,
					$request->query->getInt("page", 1),
					9
				);
			
				$noteProduit->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
				$noteProduit->setCustomParameters([
					"align" => "center"
				]);
				return $this->render("food_rating/produit_v2.html.twig", [
					"produit" => $produit,
					"note" => $note[0],
					"notesProduit" =>$noteProduit,
					"commentairesProduit" => $commentaireProduit,
					"moyenneNote" => $moyenneNote,
					"nombreVote" => $nombreVote,
					"pourcentageEtoile1" => $pourcentageEtoile1,
					"pourcentageEtoile2" => $pourcentageEtoile2,
					"pourcentageEtoile3" => $pourcentageEtoile3,
					"pourcentageEtoile4" => $pourcentageEtoile4,
					"pourcentageEtoile5" => $pourcentageEtoile5,
					"similaires" => $similaires,
					"categorie" => $categorie
				]);
			}
			else {
				$noteProduit = $paginator->paginate(
					$notesProduit,
					$request->query->getInt("page", 1),
					9
				);
			
				$noteProduit->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
				$noteProduit->setCustomParameters([
					"align" => "center"
				]);
				return $this->render("food_rating/produit_v2.html.twig", [
					"produit" => $produit,
					"notesProduit" =>$noteProduit,
					"commentairesProduit" => $commentaireProduit,
					"moyenneNote" => $moyenneNote,
					"nombreVote" => $nombreVote,
					"pourcentageEtoile1" => $pourcentageEtoile1,
					"pourcentageEtoile2" => $pourcentageEtoile2,
					"pourcentageEtoile3" => $pourcentageEtoile3,
					"pourcentageEtoile4" => $pourcentageEtoile4,
					"pourcentageEtoile5" => $pourcentageEtoile5,
					"similaires" => $similaires,
					"categorie" => $categorie
				]);
			}
		}
		else {
			$noteProduit = $paginator->paginate(
				$notesProduit,
				$request->query->getInt("page", 1),
				9
			);
		
			$noteProduit->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
			$noteProduit->setCustomParameters([
				"align" => "center"
			]);
			return $this->render("food_rating/produit_v2.html.twig", [
					"produit" => $produit,
					"notesProduit" =>$noteProduit,
					"commentairesProduit" => $commentaireProduit,
					"moyenneNote" => $moyenneNote,
					"nombreVote" => $nombreVote,
					"pourcentageEtoile1" => $pourcentageEtoile1,
					"pourcentageEtoile2" => $pourcentageEtoile2,
					"pourcentageEtoile3" => $pourcentageEtoile3,
					"pourcentageEtoile4" => $pourcentageEtoile4,
					"pourcentageEtoile5" => $pourcentageEtoile5,
					"similaires" => $similaires,
					"categorie" => $categorie
			]);
		}
    }

    /**
     * @Route("/categories/{categorie}/produit_v2/{id}/notation", name="notation")
     */
    public function notationProduit($id, $categorie, Request $request, ?UserInterface $user) {
		$api = new Api("food", "fr");
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		//$categorieProduit = explode(",", $data['categories']);

		if($user) {
			$note = new Notes();
			$manager = $this->getDoctrine()->getManager();
			$noteForm = $request->get('note');
			$commentaireForm = $request->get('commentaire');
			$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
			$moyenne = $manager->getRepository(MoyenneProduits::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);

			if (!empty($noteForm)) {
				$note->setNbEtoiles($noteForm)
					->setUtilisateur($this->getUser())
					->setProduitId($id)
					->setCreatedAt(new \DateTime());
				$manager->persist($note);
				$manager->flush();

				if($moyenne == null) {
					$moyenneProduits = new MoyenneProduits();
					$moyenneProduits->setMoyenne($noteForm)
									->setProduitId($id)
									->setCategorieProduit($categorie);
					$manager->persist($moyenneProduits);
					$manager->flush();
				}
				else {
					for($i = 0; $i < sizeof($notesProduit); $i++) {
						for($j = 0; $j < sizeof($moyenne); $j++) {
							if($notesProduit[$i]->getProduitId() != $moyenne[$j]->getProduitId()) {
								$moyenneProduits = new MoyenneProduits();
								$moyenneProduits->setMoyenne($noteForm)
												->setProduitId($id)
												->setCategorieProduit($categorie);
								$manager->persist($moyenneProduits);
								$manager->flush();
							}
							else {
								$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
								$saveNote = array();
								for ($i = 0; $i < sizeof($notesProduit); $i++) {
									$saveNote[] = $notesProduit[$i]->getNbEtoiles();
								}
								$moyenneNote = round((array_sum($saveNote)/count($saveNote)), 2);
								$moyenne[$j]->setMoyenne($moyenneNote);
								$manager->flush();
							}
						}
					}
				}
				if (!empty($commentaireForm)) {
					$commentaire = new Commentaires();
					$commentaire->setMessage($commentaireForm)
								->setUtilisateur($this->getUser())
								->setProduitId($id);
					$manager->persist($commentaire);
					$manager->flush();
				}
				return $this->redirectToRoute('produit_v2', [
					"id" => $id,
					"categorie" => $categorie
				]);
			}
		
			return $this->redirectToRoute('produit_v2', [
				"id" => $id,
				"categorie" => $categorie
			]);
		}
		else {
			return $this->redirectToRoute('produit_v2', [
				"id" => $id,
				"categorie" => $categorie
			]);
		}
	}

	/**
	 * @Route("/categories/{categorie}/produit_v2/{id}/modifier_notation", name="modifier_notation")
	 */
	public function modifierNotationProduit($id, $categorie, Request $request, ?UserInterface $user) {
		$api = new Api("food", "fr");
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		// $categorieProduit = explode(",", $data['categories']);
		if($user) {
			$manager = $this->getDoctrine()->getManager();
			$note = $manager->getRepository(Notes::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id'] ?? $data['code']]);
			$noteForm = $request->get('note');
			$commentaire = $manager->getRepository(Commentaires::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id'] ?? $data['code']]);
			$commentaireForm = $request->get('commentaire');
			$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
			$moyenne = $manager->getRepository(MoyenneProduits::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);

			if (!empty($noteForm)) {
				$note[0]->setNbEtoiles($noteForm)
						->setCreatedAt(new \DateTime());
				$manager->flush();

				for($i = 0; $i < sizeof($notesProduit); $i++) {
					for($j = 0; $j < sizeof($moyenne); $j++) {
						if($notesProduit[$i]->getProduitId() == $moyenne[$j]->getProduitId()) {
							$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
							$saveNote = array();
							for ($i = 0; $i < sizeof($notesProduit); $i++) {
								$saveNote[] = $notesProduit[$i]->getNbEtoiles();
							}
							$moyenneNote = round((array_sum($saveNote)/count($saveNote)), 2);
							$moyenne[$j]->setMoyenne($moyenneNote);
							$manager->flush();
						}
					}
				}
		
				if((!empty($commentaireForm) && ctype_space($commentaireForm) == false) && $commentaire != null) {
					$commentaire[0]->setMessage($commentaireForm);
					$manager->flush();
				}
				else if((empty($commentaireForm) || ctype_space($commentaireForm) == true) && $commentaire != null) {
					$manager->remove($commentaire[0]);
					$manager->flush();
				}
				else if($commentaire != null) {
					$commentaire[0]->setMessage($commentaire[0]->getMessage());
					$manager->flush();
				}
				else if((!empty($commentaireForm) && ctype_space($commentaireForm) == false) && $commentaire == null) {
					$newCommentaire = new Commentaires();
					$newCommentaire->setMessage($commentaireForm)
								   ->setUtilisateur($this->getUser())
								   ->setProduitId($id);
					$manager->persist($newCommentaire);
					$manager->flush();
				}
				return $this->redirectToRoute('produit_v2', [
					"id" => $id,
					"categorie" => $categorie
				]);
			}
			else if(!empty($note[0]->getNbEtoiles())) {
				$note[0]->setNbEtoiles($note[0]->getNbEtoiles())
						->setCreatedAt(new \DateTime());
				$manager->flush();

				for($i = 0; $i < sizeof($notesProduit); $i++) {
					for($j = 0; $j < sizeof($moyenne); $j++) {
						if($notesProduit[$i]->getProduitId() == $moyenne[$j]->getProduitId()) {
							$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
							$saveNote = array();
							for ($i = 0; $i < sizeof($notesProduit); $i++) {
								$saveNote[] = $notesProduit[$i]->getNbEtoiles();
							}
							$moyenneNote = round((array_sum($saveNote)/count($saveNote)), 2);
							$moyenne[$j]->setMoyenne($moyenneNote);
							$manager->flush();
						}
					}
				}

				if((!empty($commentaireForm) && ctype_space($commentaireForm) == false) && $commentaire != null) {
					$commentaire[0]->setMessage($commentaireForm);
					$manager->flush();
				}
				else if((empty($commentaireForm) || ctype_space($commentaireForm) == true) && $commentaire != null) {
					$manager->remove($commentaire[0]);
					$manager->flush();
				}
				else if($commentaire != null) {
					$commentaire[0]->setMessage($commentaire[0]->getMessage());
					$manager->flush();
				}
				else if((!empty($commentaireForm) && ctype_space($commentaireForm) == false) && $commentaire == null) {
					$newCommentaire = new Commentaires();
					$newCommentaire->setMessage($commentaireForm)
								   ->setUtilisateur($this->getUser())
								   ->setProduitId($id);
					$manager->persist($newCommentaire);
					$manager->flush();
				}
				return $this->redirectToRoute('produit_v2', [
					"id" => $id,
					"categorie" => $categorie
				]);
			}

			return $this->redirectToRoute('produit_v2', [
				"id" => $id,
				"categorie" => $categorie
			]);
		}
		else {
			return $this->redirectToRoute('produit_v2', [
				"id" => $id,
				"categorie" => $categorie
			]);
		}
	}

	/**
	 * @Route("/categories/{categorie}/produit_v2/{id}/supprimer_notation", name="supprimer_notation")
	 */
	public function supprimerNotationProduit($id, $categorie, Request $request, ?UserInterface $user) {
		$api = new Api("food", "fr");
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		// $categorieProduit = explode(",", $data['categories']);
		if($user) {
			$manager = $this->getDoctrine()->getManager();
			$note = $manager->getRepository(Notes::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id']]);
			$commentaire = $manager->getRepository(Commentaires::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id']]);
			$manager->remove($note[0]);

			if($commentaire != null) {
				$manager->remove($commentaire[0]);
			}

			for($i = 0; $i < sizeof($notesProduit); $i++) {
				for($j = 0; $j < sizeof($moyenne); $j++) {
					if($notesProduit[$i]->getProduitId() == $moyenne[$j]->getProduitId()) {
						if(sizeof($notesProduit) == 1) {
							$manager->remove($moyenne[$j]);
						}
						else {
							$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id']]);
							$saveNote = array();
							for ($i = 0; $i < sizeof($notesProduit); $i++) {
								$saveNote[] = $notesProduit[$i]->getNbEtoiles();
							}
							$moyenneNote = round((array_sum($saveNote)/count($saveNote)), 2);
							$moyenne[$j]->setMoyenne($moyenneNote);
						}
					}
				}
			}

			$manager->flush();

			return $this->redirectToRoute('produit_v2', [
				"id" => $id,
				"categorie" => $categorie
			]);
		}
		else {
			return $this->redirectToRoute('produit_v2', [
				"id" => $id,
				"categorie" => $categorie
			]);
		}
	}
    
    /**
     * @Route("/espace", name="espace")
     */
    public function espace( ?UserInterface $user) {
		if ($user) {
			if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
				return $this->redirectToRoute('compte_admin');
			}
			else {
				return $this->redirectToRoute('compte_client');
			}
		}
		else {
			return $this->render('food_rating/accueil.html.twig');
		}
	}
	
	/**
	 * @Route("/client", name="compte_client")
	 */
	public function compteClient() {
		return $this->render('food_rating/espace.html.twig');
	}

	/**
	 * @Route("/admin", name="compte_admin")
	 */
	public function compteAdmin() {
		return $this->render('food_rating/espace_admin.html.twig');
	}

    /**
     * @Route("/compte/info_compte", name="user_show")
     */
    public function show() {
        return $this->render('food_rating/info_compte.html.twig');
    }
    
    /**
     * @Route("/categories", name="liste_categorie")
     */
    public function listeCategorie(PaginatorInterface $paginator, CategoriesRepository $repo, Request $request) {
    	$donnees = $repo->createQueryBuilder("c")
    					// "40" correspond au nombre de caractères avant le dernier slash de l'url inclus
    					->select("c.name, c.products, substring(c.url, 40) as url")
    					->getQuery()
    					->getResult();
    	
    	$categories = $paginator->paginate(
    			$donnees,
    			$request->query->getInt("page", 1),
    			40
    	);
    	
    	// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
    	$categories->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
    	
    	// On aligne les sélecteurs au centre de la page
    	$categories->setCustomParameters([
    			"align" => "center"
    	]);
    					
    	return $this->render("food_rating/liste_categorie.html.twig", [
    			"categories" => $categories
    	]);
    }
    
    /**
     * @Route("/categories/{categorie}", name="categorie")
     */
    public function pageCategorie($categorie, PaginatorInterface $paginator, Request $request) {
		$api = new Api("food", "fr");
    	
    	$collection = $api->getByFacets(["category" => $categorie], $request->query->getInt("page", 1));
    	$tab = array();
    	foreach ($collection as $key => $elt) {
    		$tab[] = $elt;
    	}
    	
    	$donnees = $paginator->paginate(
    			$tab,
    			$request->query->getInt("page", 1),
    			$collection->pageCount()
    	);
    	
    	$donnees->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
    	$donnees->setCustomParameters([
    			"align" => "center"
    	]);
    	
    	return $this->render("food_rating/liste_produit.html.twig", [
				"produits" => $donnees,
    			"categorie" => $categorie
    	]);
    }
    
    /**
     * @Route("/debug/{id}", name="debug")
     */
    public function debugProduit($id) {
    	$api = new Api("food", "fr");
    	$prd = $api->getProduct($id);
    	print("<pre>".print_r($prd,true)."</pre>");
    	
    	return null;
    }
    
    /**
     * @Route("/test", name="test")
     * @param Api $api
     */
    public function testWrapper(PaginatorInterface $paginator, Request $request) {
		$api = new Api("food", "fr");
		$produit = $api->getProduct("3242274000059");
//     	$prd = $api->getProduct('3057640385148');
//     	print("<pre>".print_r($prd,true)."</pre>");

    	$mot = "couscous";

		$recherche = $api->search($mot, 1, 30);
		$compteur = $recherche->searchCount();
		$result = array();
		
		dump($recherche);
		$i = 0;
		foreach ($recherche as $key => $prd) {
			$i = $i +1;
			$data = $prd->getData();
			$result[] = $prd;
		}
		echo $i;
    	    	
    	$result = $paginator->paginate(
				$result,
    			$request->query->getInt("page", 1),
    			30
    	);
    	
    	$result->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
    	$result->setCustomParameters([
    			"align" => "center"
    	]);
    	
    	return $this->render("food_rating/liste_produit.html.twig", [
    			"produits" => $result
    	]);
    	
	}


}

?>
