<?php

namespace App\Controller;

use App\Entity\Aime;
use App\Entity\Notes;

use OpenFoodFacts\Api;

use App\Entity\Commentaires;
use App\Entity\Utilisateurs;
use App\Entity\MoyenneProduits;
use Symfony\Component\Mime\Email;
use App\Repository\AimeRepository;
use App\Repository\NotesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\CommentairesRepository;
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
    public function home(MoyenneProduitsRepository $repo, CategoriesRepository $repoC) {
		$api = new Api("food", "fr");
		$manager = $this->getDoctrine()->getManager();
		$moyenneProduits = $repo->findAll();
		$categories = $repoC->createQueryBuilder('c')
							->select("c.name, c.url")
							->where("c.name not like '%:%' and c.products >= 4")
							->getQuery()
							->getResult();
		
		$tabCategories = array();
		$tabCategoriesRandom = array();
		$tab = array();
		$tabPrdRandom = array();
		for ($i = 0; $i < sizeof($categories); $i++) {
			$tabCategories [] = [
					"name" => $categories[$i]["name"],
					"categorie_url" => substr($categories[$i]["url"], 39)
			];
		}
		for($random = 0; $random < 3; $random++) {
			$tabCategoriesRandom [] = $tabCategories[rand(0, sizeof($tabCategories))];
		}
		
		for ($prd = 0; $prd < sizeof($tabCategoriesRandom); $prd++) {
			$recherche = $api->search($tabCategoriesRandom[$prd]["name"]);
			foreach ($recherche as $key => $elt) {
				$data = $elt->getData();
				$data["categorie_url"] = $tabCategoriesRandom[$prd]["categorie_url"];
				$tab[] = $data ;
			}
		}
				
		for ($prdRandom = 0; $prdRandom < 3; $prdRandom++) {
			$tabPrdRandom [] = $tab[rand(0, sizeof($tab) - 1)];
		}

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

			return $this->render('food_rating/accueil.html.twig', [
				"meilleursProduit" => $meilleursProduit,
				"categorieProduit" => $categorieProduit,
				"produit_carousel" => $tabPrdRandom
			]);
		}
		else {
			return $this->render('food_rating/accueil.html.twig', [
				"produit_carousel" => $tabPrdRandom
			]);
		}
    }
    
    /**
     * @Route("/categories/{categorie}/produit_v2/{id}", name="produit_v2")
     */
    public function afficheProduitV2($id, $categorie, ?UserInterface $user, PaginatorInterface $paginator, Request $request, CommentairesRepository $repoC) {
    	$api = new Api("food", "fr");
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		$manager = $this->getDoctrine()->getManager();
		$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
		$commentaireProduit = $manager->getRepository(Commentaires::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
		$saveNote = array();
		$aimeCommentaire = $manager->getRepository(Aime::class)->findBy(["produit" => $id]);
		
		$keyAime = array();
		$valueAime = array();
		$fonctionKey = function($val) { return $val->getIdCommentaire()->getId(); };
		$fonctionValue = function($val) { return $val->getIdUtilisateur()->getId(); };
		
		$keyAime = array_map($fonctionKey, $aimeCommentaire);
		reset($aimeCommentaire);
		$valueAime = array_map($fonctionValue, $aimeCommentaire);
		
		$aimeCommentaire = array_combine($keyAime, $valueAime);
		
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
			
			$nombreEtoiles = array();
			$pourcentageEtoiles = array();
			
			for ($i = 0; $i < 5; $i++) {
				$nombreEtoiles [] = count(array_keys($saveNote, $i + 1));
				
				if ($nombreEtoiles[$i] != 0)
					$pourcentageEtoiles [] = (100 * $nombreEtoiles[$i]) / count($saveNote);
				else
					$pourcentageEtoiles [] = 0;
			}
		}
		else {
			$moyenneNote = 0;
			$nombreVote = 0;
			$pourcentageEtoiles = array(0,0,0,0,0);
		}
		if ($user && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
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
						"pourcentageEtoiles" => $pourcentageEtoiles,
						"similaires" => $similaires,
						"categorie" => $categorie,
						"aimeCommentaire" => $aimeCommentaire
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
					"pourcentageEtoiles" => $pourcentageEtoiles,
					"similaires" => $similaires,
					"categorie" => $categorie,
					"aimeCommentaire" => $aimeCommentaire
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
					"pourcentageEtoiles" => $pourcentageEtoiles,
					"similaires" => $similaires,
					"categorie" => $categorie,
					"aimeCommentaire" => $aimeCommentaire
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
					"pourcentageEtoiles" => $pourcentageEtoiles,
					"similaires" => $similaires,
					"categorie" => $categorie,
					"aimeCommentaire" => $aimeCommentaire
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

		if($user && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
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
								->setProduitId($id)
								->setUtile(0);
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
		if($user && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
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
								   ->setProduitId($id)
								   ->setUtile(0);
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
								   ->setProduitId($id)
								   ->setUtile(0);
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
		if($user && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			$manager = $this->getDoctrine()->getManager();
			$note = $manager->getRepository(Notes::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id']]);
			$commentaire = $manager->getRepository(Commentaires::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id']]);
			$manager->remove($note[0]);

			if($commentaire != null) {
				$manager->remove($commentaire[0]);
			}
				
			$notesProduit = $manager->getRepository(Notes::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
			$moyenne = $manager->getRepository(MoyenneProduits::class)->findBy(['produit_id' => $data['id'] ?? $data['code']]);
			
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
	 * @Route("/categories/{categorie}/produit_v2/{id}/{utile}", name="commentaire_utile")
	 */
	public function commentaireUtile($id, $categorie, $utile, ?UserInterface $user, CommentairesRepository $repo) {
		if($user && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			$manager = $this->getDoctrine()->getManager();
			$commentaire = $repo->find($utile);
			
			if ($commentaire->getUtile() == null || $commentaire->getUtile() == 0) {
				$commentaire->setUtile(1);
			}
			else {
				$commentaire->setUtile($commentaire->getUtile() + 1);
			}
			
			$manager->flush();
			$aime = new Aime();
			$aime->setIdUtilisateur($user)
				 ->setIdCommentaire($commentaire)
				 ->setProduit($id);
			$manager->persist($aime);
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
	 * @Route("/categories/{categorie}/produit_v2/{id}/{utile}/suppresion", name="supprimer_vote")
	 */
	public function supprimerVote($id, $categorie, $utile, ?UserInterface $user, CommentairesRepository $repo, AimeRepository $repoAime) {
		if($user && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
			$manager = $this->getDoctrine()->getManager();
			$commentaire = $repo->find($utile);
			$aimeUser = $repoAime->findBy(['idUtilisateur' => $user->getId(), 'idCommentaire' => $commentaire->getId()]);
			if ($commentaire->getUtile() == 0) {
				$commentaire->setUtile(0);
				$manager->remove($aimeUser[0]);
				$manager->flush();
			}
			else {
				$commentaire->setUtile($commentaire->getUtile() - 1);
				$manager->remove($aimeUser[0]);
				$manager->flush();
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
     * @Route("/espace", name="espace")
     */
    public function espace( ?UserInterface $user) {
		if ($user) {
			if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
				return $this->redirectToRoute('compte_admin');
			}
			else {
				return $this->redirectToRoute('user_show');
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
		$manager = $this->getDoctrine()->getManager();
		$utilisateurs = $manager->getRepository(Utilisateurs::class)->findByRole('ROLE_USER');
		dump($utilisateurs);

		return $this->render("food_rating/espace_admin.html.twig", [
			"utilisateurs" => $utilisateurs
		]);
	}

    /**
     * @Route("/client/info_compte", name="user_show")
     */
	public function show(UtilisateursRepository $repo, ?UserInterface $user) {
		$utilisateur = $repo->findBy(["id" => $user->getId()]);
		if ($utilisateur[0]->getImageBase64() != null) {
			return $this->render('food_rating/info_compte.html.twig', [
				"image" => $utilisateur[0]->getImageBase64()
			]);
		}
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
    public function pageCategorie($categorie, PaginatorInterface $paginator, Request $request, MoyenneProduitsRepository $repo) {
		$api = new Api("food", "fr");
    	
		$collection = $api->getByFacets(["category" => $categorie], $request->query->getInt("page", 1));
		$compteur = $collection->searchCount();
		$tab = array();

		$moyennesProduits = $repo->findAll();

		for ($i = 0; $i < $compteur/20; $i++){
    		foreach ($collection as $key => $elt) {
    			$tab[] = $elt;
			}
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
		dump($moyennesProduits);
		if ($moyennesProduits != null) {
			return $this->render("food_rating/liste_produit.html.twig", [
					"produits" => $donnees,
					"categorie" => $categorie,
					"moyennesProduits" => $moyennesProduits
			]);
		}
		else {
			return $this->render("food_rating/liste_produit.html.twig", [
				"produits" => $donnees,
				"categorie" => $categorie
			]);
		}
	}
	

	/**
	 * @Route("/admin/users/suppression", name="suppression_user")
	 */
	public function suppressionUser(Request $request){
		$manager = $this->getDoctrine()->getManager();
		$pseudo = "";
		dump($request);
		if ($request->query->has("uas")){
			$user = $manager->getRepository(Utilisateurs::class)->findOneBy(['email' => $request->query->get("uas")]);

			// Dans le cas où l'admin envoie en paramètre URL une adresse qui n'existe pas
			if (empty($user)){
				$this->addFlash(
					'notice',
					"L'utilisateur que vous avez demandé de supprimer n'existe pas."
				);
			}
			// Dans le cas où l'admin envoie en paramètre URL une adresse d'un admin
			elseif ($user->getRoles() == ['ROLE_ADMIN']) {
				$pseudo = $user->getUsername();
				$this->addFlash(
					'notice',
					'L\'utilisateur ' . $pseudo . " est un admin ! Il ne peut pas être supprimé."
				);
			}
			// Dans le cas où l'admin envoie en paramètre URL une adresse d'un user ou a cliqué sur le bouton Supprimer à côté d'un user
			else {
				$manager->remove($user);
				$manager->flush();
				$pseudo = $user->getUsername();
				$this->addFlash(
					'notice',
					'L\'utilisateur ' . $pseudo . " a été supprimé"
				);
			}
		}

		$utilisateurs = $manager->getRepository(Utilisateurs::class)->findAll();

		return $this->redirectToRoute("compte_admin");
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
