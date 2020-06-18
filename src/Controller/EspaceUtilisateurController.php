<?php

namespace App\Controller;

use App\Entity\Amis;
use League\Csv\Reader;
use OpenFoodFacts\Api;
use App\Entity\DemandeAmi;
use App\Entity\ReponsePrivee;
use App\Entity\DiscussionPrivee;
use App\Repository\AmisRepository;
use App\Repository\NotesRepository;
use App\Repository\ReponseRepository;
use App\Repository\DemandeAmiRepository;
use App\Repository\DiscussionRepository;
use App\Repository\CommentairesRepository;
use App\Repository\UtilisateursRepository;
use App\Repository\ReponsePriveeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\MoyenneProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DiscussionPriveeRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EspaceUtilisateurController extends AbstractController
{
    /**
	 * @Route("/client", name="compte_client")
	 */
	public function compteClient(?UserInterface $user, MoyenneProduitsRepository $repo, NotesRepository $repoN, CommentairesRepository $repoC, DiscussionRepository $repoD, ReponseRepository $repoR) {
		$api = new Api("food", "fr");
		$filesystem = new Filesystem();
		$tableauProduitsVisite = array();
		$categories = array();
		$sortie = 0;
		$moyennesProduits = $repo->findAll();
		$userProduitsNotes = array();
		$categoriesNotes = array();
		$userProduitsCommentaires = array();
		$categoriesNotes2 = array();
		$tableauSujetsVisite = array();

		$notesUser = $repoN->createQueryBuilder('n')
						   ->where('n.utilisateur = :user')
						   ->setParameter('user', $user)
						   ->orderBy('n.id', 'DESC')
						   ->setMaxResults(5)
						   ->getQuery()
            			   ->getResult();

		$commentairesUser = $repoC->createQueryBuilder('c')
								  ->where('c.utilisateur = :user')
								  ->setParameter('user', $user)
								  ->orderBy('c.id', 'DESC')
								  ->setMaxResults(5)
								  ->getQuery()
								  ->getResult();
		
		$sujetsUser = $repoD->createQueryBuilder('d')
							->where('d.id_utilisateur = :user')
							->setParameter('user', $user)
							->orderBy('d.idDiscussion', 'DESC')
							->setMaxResults(5)
							->getQuery()
							->getResult();
		
		$participationSujets = $repoR->createQueryBuilder('r')
									 ->where('r.idUtilisateur = :user')
									 ->setParameter('user', $user)
									 ->orderBy('r.createdAt', 'ASC')
									 ->groupBy('r.idDiscussion')
									 ->setMaxResults(5)
									 ->getQuery()
									 ->getResult();
		if($filesystem->exists('csv/'. $user->getId().'/produit.csv')) {
			$stream = fopen('csv/'. $user->getId().'/produit.csv', 'r');
			$csv = Reader::createFromStream($stream);
			$csv->setDelimiter(';');
			$csv->setHeaderOffset(0);
			$records = $csv->getRecords();
			if (count(file('csv/'. $user->getId().'/produit.csv')) == 1) {
				$tableauProduitsVisite [] = "Aucun produit";
			}
			else {
				foreach($records as $row) {
					$sortie++;
					$produit = $api->getProduct($row['id']);
					$tableauProduitsVisite [] = $produit;
					$categories [] = $row['categorieUrl'];
					if($sortie == 5) {
						break;	
					}
				}
			}
		}
		else {
			$tableauProduitsVisite = null;
		}
		if($notesUser != null) {
			for($i = 0; $i < sizeof($notesUser); $i++) {
				$produitsNotes = $api->getProduct($notesUser[$i]->getProduitId());
				$userProduitsNotes [] = $produitsNotes;
				$data = $produitsNotes->getData();
					
				if (empty($data["categories"]) || empty($data["categories_tags"][0])) {
					$categorie = "unknown";
				} else {
					$categorie = explode(",", $data["categories"])[0];
					
					if (strpos($data["categories_tags"][0], "en:") !== false && strpos($categorie, ":") === false) {
						$categorie = $this->transfoCategorieURL($categorie);
					} else {
						$row = $repoC->find($data["categories_tags"][0]);
						if (! is_null($row))
							$categorie = substr($row->getUrl(), 40);
					}
				}
				
				$data["categorie_url"] = $categorie;
				$categoriesNotes[] = $data;
			}
		}
		else {
			$userProduitsNotes = null;
			$categoriesNotes = array();
		}
		if($commentairesUser != null) {
			for($i = 0; $i < sizeof($commentairesUser); $i++) {
				$produitsCommentaires = $api->getProduct($commentairesUser[$i]->getProduitId());
				$userProduitsCommentaires [] = $produitsCommentaires;
				$data = $produitsCommentaires->getData();
					
				if (empty($data["categories"]) || empty($data["categories_tags"][0])) {
					$categorie = "unknown";
				} else {
					$categorie = explode(",", $data["categories"])[0];
					
					if (strpos($data["categories_tags"][0], "en:") !== false && strpos($categorie, ":") === false) {
						$categorie = $this->transfoCategorieURL($categorie);
					} else {
						$row = $repoC->find($data["categories_tags"][0]);
						if (! is_null($row))
							$categorie = substr($row->getUrl(), 40);
					}
				}
				
				$data["categorie_url"] = $categorie;
				$categoriesCommentaires[] = $data;
			}
		}
		else {
			$userProduitsCommentaires = null;
			$categoriesCommentaires = array();
		}

		if($filesystem->exists('csv/'. $user->getId().'/forum.csv')) {
			$stream = fopen('csv/'. $user->getId().'/forum.csv', 'r');
			$csv = Reader::createFromStream($stream);
			$csv->setDelimiter(';');
			$csv->setHeaderOffset(0);
			$records = $csv->getRecords();
			if (count(file('csv/'. $user->getId().'/forum.csv')) == 1) {
				$tableauSujetsVisite [] = "Aucun forum";
			}
			else {
				foreach($records as $row) {
					$sortie++;
					$sujet = $repoD->find($row['id']);
					$tableauSujetsVisite [] = $sujet;
					if($sortie == 5) {
						break;	
					}
				}
			}
		}
		else {
			$tableauSujetsVisite = null;
		}

		return $this->render('espace_utilisateur/espace.html.twig', [
			"produitsVisite" => $tableauProduitsVisite,
			"categories" => $categories,
			"moyennesProduits" => $moyennesProduits,
			"userProduitsNotes" => $userProduitsNotes,
			"categoriesNotes" => $categoriesNotes,
			"userProduitsCommentaires" => $userProduitsCommentaires,
			"categoriesCommentaires" => $categoriesCommentaires,
			"sujetsVisite" => $tableauSujetsVisite,
			"sujetsUser" => $sujetsUser,
			"participation" => $participationSujets
		]);
	}
	
	/**
	 * @Route("/client/produit_visite", name="produit_visite")
	 */
	public function produitsVisite(?UserInterface $user, MoyenneProduitsRepository $repo, PaginatorInterface $paginator, Request $request) {
		$api = new Api("food", "fr");
		$filesystem = new Filesystem();
		$tableauProduitsVisite = array();
		$categories = array();
		$moyennesProduits = $repo->findAll();

		if($filesystem->exists('csv/'. $user->getId().'/produit.csv')) {
			$stream = fopen('csv/'. $user->getId().'/produit.csv', 'r');
			$csv = Reader::createFromStream($stream);
			$csv->setDelimiter(';');
			$csv->setHeaderOffset(0);
			$records = $csv->getRecords();
			if (count(file('csv/'. $user->getId().'/produit.csv')) == 1) {
				$tableauProduitsVisite [] = "Aucun produit";
			}
			else {
				foreach($records as $row) {
					$produit = $api->getProduct($row['id']);
					$tableauProduitsVisite [] = $produit;
					$categories [] = $row['categorieUrl'];
				}
			}
			$produits = $paginator->paginate(
				$tableauProduitsVisite,
				$request->query->getInt("page", 1),
				5
			);
					
			// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
			$produits->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
			
			// On aligne les sélecteurs au centre de la page
			$produits->setCustomParameters([
					"align" => "center"
			]);
			return $this->render("espace_utilisateur/produit_visite.html.twig", [
				"produits" => $produits,
				"categories" => $categories,
				"moyennesProduits" => $moyennesProduits
			]);
		}
		else {
			return $this->render("espace_utilisateur/produit_visite.html.twig");
		}
		
	}

	/**
	 * @Route("/client/produit_visite/supprimer", name="produit_visite_supprimer")
	 */
	public function supprimerProduitsVisite(?UserInterface $user) {
		$filesystem = new Filesystem();
		if($filesystem->exists('csv/'. $user->getId().'/produit.csv')) {
			$filesystem->remove('csv/'. $user->getId().'/produit.csv');
		}
		return $this->redirectToRoute('produit_visite');
	}

	/**
	 * @Route("/client/produit_notes", name="produit_notes")
	 */
	public function produitNotes(?UserInterface $user, NotesRepository $repoN, MoyenneProduitsRepository $repo, PaginatorInterface $paginator, Request $request) {
		$api = new Api("food", "fr");
		$notesUser = $repoN->createQueryBuilder('n')
						   ->where('n.utilisateur = :user')
						   ->setParameter('user', $user)
						   ->orderBy('n.id', 'DESC')
						   ->getQuery()
						   ->getResult();
		$moyennesProduits = $repo->findAll();
		$userProduitsNotes = array();
		$categoriesNotes = array();
		if($notesUser != null) {
			for($i = 0; $i < sizeof($notesUser); $i++) {
				$produitsNotes = $api->getProduct($notesUser[$i]->getProduitId());
				$userProduitsNotes [] = $produitsNotes;
				$data = $produitsNotes->getData();
					
				if (empty($data["categories"]) || empty($data["categories_tags"][0])) {
					$categorie = "unknown";
				} else {
					$categorie = explode(",", $data["categories"])[0];
					
					if (strpos($data["categories_tags"][0], "en:") !== false && strpos($categorie, ":") === false) {
						$categorie = $this->transfoCategorieURL($categorie);
					} else {
						$row = $repoC->find($data["categories_tags"][0]);
						if (! is_null($row))
							$categorie = substr($row->getUrl(), 40);
					}
				}
				
				$data["categorie_url"] = $categorie;
				$categoriesNotes[] = $data;
			}
			$produits = $paginator->paginate(
				$userProduitsNotes,
				$request->query->getInt("page", 1),
				5
			);
					
			// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
			$produits->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
			
			// On aligne les sélecteurs au centre de la page
			$produits->setCustomParameters([
					"align" => "center"
			]);
			return $this->render("espace_utilisateur/produit_notes.html.twig", [
				"produits" => $produits,
				"categories" => $categoriesNotes,
				"moyennesProduits" => $moyennesProduits
			]);
		}
		else {
			return $this->render("espace_utilisateur/produit_notes.html.twig");
		}
	}

	/**
	 * @Route("/client/produit_commentaires", name="produit_commentaires")
	 */
	public function produitCommentaires(?UserInterface $user, CommentairesRepository $repoC, MoyenneProduitsRepository $repo, PaginatorInterface $paginator, Request $request) {
		$api = new Api("food", "fr");
		$commentairesUser = $repoC->createQueryBuilder('c')
						   ->where('c.utilisateur = :user')
						   ->setParameter('user', $user)
						   ->orderBy('c.id', 'DESC')
						   ->getQuery()
						   ->getResult();
		$moyennesProduits = $repo->findAll();
		$userProduitsCommentaires = array();
		$categoriesNotes2 = array();
		if($commentairesUser != null) {
			for($i = 0; $i < sizeof($commentairesUser); $i++) {
				$produitsCommentaires = $api->getProduct($commentairesUser[$i]->getProduitId());
				$userProduitsCommentaires [] = $produitsCommentaires;
				$data = $produitsCommentaires->getData();
					
				if (empty($data["categories"]) || empty($data["categories_tags"][0])) {
					$categorie = "unknown";
				} else {
					$categorie = explode(",", $data["categories"])[0];
					
					if (strpos($data["categories_tags"][0], "en:") !== false && strpos($categorie, ":") === false) {
						$categorie = $this->transfoCategorieURL($categorie);
					} else {
						$row = $repoC->find($data["categories_tags"][0]);
						if (! is_null($row))
							$categorie = substr($row->getUrl(), 40);
					}
				}
				
				$data["categorie_url"] = $categorie;
				$categoriesCommentaires[] = $data;
			}

			$produits = $paginator->paginate(
				$userProduitsCommentaires,
				$request->query->getInt("page", 1),
				5
			);
					
			// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
			$produits->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
			
			// On aligne les sélecteurs au centre de la page
			$produits->setCustomParameters([
					"align" => "center"
			]);
			return $this->render("espace_utilisateur/produit_commentaires.html.twig", [
				"produits" => $produits,
				"categories" => $categoriesCommentaires,
				"moyennesProduits" => $moyennesProduits
			]);
		}
		else {
			return $this->render("espace_utilisateur/produit_commentaires.html.twig");
		}
	}

	/**
	 * @Route("/client/forum_visite", name="forum_visite")
	 */
	public function forumVisite(?UserInterface $user, DiscussionRepository $repoD, PaginatorInterface $paginator, Request $request) {
		$filesystem = new Filesystem();
		$tableauSujetsVisite = array();
		if($filesystem->exists('csv/'. $user->getId().'/forum.csv')) {
			$stream = fopen('csv/'. $user->getId().'/forum.csv', 'r');
			$csv = Reader::createFromStream($stream);
			$csv->setDelimiter(';');
			$csv->setHeaderOffset(0);
			$records = $csv->getRecords();
			if (count(file('csv/'. $user->getId().'/forum.csv')) == 1) {
				$tableauSujetsVisite [] = "Aucun forum";
			}
			else {
				foreach($records as $row) {
					$sujet = $repoD->find($row['id']);
					$tableauSujetsVisite [] = $sujet;
				}
			}
			$sujets = $paginator->paginate(
				$tableauSujetsVisite,
				$request->query->getInt("page", 1),
				10
			);
					
			// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
			$sujets->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
			
			// On aligne les sélecteurs au centre de la page
			$sujets->setCustomParameters([
					"align" => "center"
			]);
			return $this->render("espace_utilisateur/forum_visite.html.twig", [
				"sujetsVisite" => $sujets
			]);
		}
		else {
			return $this->render("espace_utilisateur/forum_visite.html.twig");
		}
	}

	/**
	 * @Route("/client/forum_visite/supprimer", name="forum_visite_supprimer")
	 */
	public function supprimerForumsVisite(?UserInterface $user) {
		$filesystem = new Filesystem();
		if($filesystem->exists('csv/'. $user->getId().'/forum.csv')) {
			$filesystem->remove('csv/'. $user->getId().'/forum.csv');
		}
		return $this->redirectToRoute('forum_visite');
	}

	/**
	 * @Route("/client/forum_discussion", name="forum_discussion")
	 */
	public function forumDiscussion(?UserInterface $user, DiscussionRepository $repoD, PaginatorInterface $paginator, Request $request) {
		$sujetsUser = $repoD->createQueryBuilder('d')
							->where('d.id_utilisateur = :user')
							->setParameter('user', $user)
							->orderBy('d.idDiscussion', 'DESC')
							->getQuery()
							->getResult();
		if ($sujetsUser != null) {
			$sujets = $paginator->paginate(
				$sujetsUser,
				$request->query->getInt("page", 1),
				10
			);
					
			// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
			$sujets->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
			
			// On aligne les sélecteurs au centre de la page
			$sujets->setCustomParameters([
					"align" => "center"
			]);
			return $this->render("espace_utilisateur/forum_discussion.html.twig", [
				"sujetsUser" => $sujets
			]);	
		}
		else {
			return $this->render("espace_utilisateur/forum_discussion.html.twig");
		}
	}

	/**
	 * @Route("/client/forum_participation", name="forum_participation")
	 */
	public function forumParticipation(?UserInterface $user, ReponseRepository $repoR, PaginatorInterface $paginator, Request $request) {
		$participationSujets = $repoR->createQueryBuilder('r')
									 ->where('r.idUtilisateur = :user')
									 ->setParameter('user', $user)
									 ->orderBy('r.createdAt', 'ASC')
									 ->groupBy('r.idDiscussion')
									 ->getQuery()
									 ->getResult();
		if ($participationSujets != null) {
			$sujets = $paginator->paginate(
				$participationSujets,
				$request->query->getInt("page", 1),
				10
			);
					
			// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
			$sujets->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
			
			// On aligne les sélecteurs au centre de la page
			$sujets->setCustomParameters([
					"align" => "center"
			]);
			return $this->render("espace_utilisateur/forum_participation.html.twig", [
				"participation" => $sujets
			]);	
		}
		else {
			return $this->render("espace_utilisateur/forum_participation.html.twig");
		}
	}

	/**
	 * @Route("/client/liste_utilisateurs", name="liste_utilisateur")
	 */
	public function listeUtilisateurs(?UserInterface $user, UtilisateursRepository $repoU, DemandeAmiRepository $repoD, AmisRepository $repoA, PaginatorInterface $paginator, Request $request) {
		$donnees = $repoU->createQueryBuilder("u")
						 ->where("u.username != :user")
						 ->setParameter("user", $user->getUsername())
						 ->orderBy('u.username')
						 ->getQuery()
						 ->getResult();
		$demande = $repoD->findBy(array("demandeur" => $user));
		$recept = $repoD->findBy(array("recepteur" => $user));
		$listeAmis1 = $repoA->findBy(array("utilisateur1" => $user));
		$listeAmis2 = $repoA->findBy(array("utilisateur2" => $user));
		$saveRecepteur = array();
		$saveDemandeur = array();
		$saveListeAmis1 = array();
		$saveListeAmis2 = array();
		$demandePourRecepteur = array();
		$utilisateursTab = array();
		$utilisateursFinal = array();
		
		if ($demande != null) {
			for($i = 0; $i < sizeof($demande); $i++) {
				$saveRecepteur [] = $demande[$i]->getRecepteur();
			}
		}

		if($recept != null) {
			for($i = 0; $i < sizeof($recept); $i++) {
				$saveDemandeur[] = $recept[$i]->getDemandeur();
			}
		}

		if($listeAmis1 != null) {
			for($i = 0; $i < sizeof($listeAmis1); $i++) {
				$saveListeAmis1[] = $listeAmis1[$i]->getUtilisateur2();
			}
		}

		if($listeAmis2 != null) {
			for($i = 0; $i < sizeof($listeAmis2); $i++) {
				$saveListeAmis2[] = $listeAmis2[$i]->getUtilisateur1();
			}
		}

		for($j = 0; $j < sizeof($donnees); $j++) {
			if($demande != null && $recept == null && $listeAmis1 == null && $listeAmis2 == null) {
				if(!in_array($donnees[$j], $saveRecepteur)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande != null && $recept == null && $listeAmis1 != null && $listeAmis2 == null) {
				if(!in_array($donnees[$j], $saveRecepteur) && !in_array($donnees[$j], $saveListeAmis1)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande != null && $recept == null && $listeAmis1 == null && $listeAmis2 != null) {
				if(!in_array($donnees[$j], $saveRecepteur) && !in_array($donnees[$j], $saveListeAmis2)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande != null && $recept == null && $listeAmis1 != null && $listeAmis2 != null) {
				if(!in_array($donnees[$j], $saveRecepteur) && !in_array($donnees[$j], $saveListeAmis1) && !in_array($donnees[$j], $saveListeAmis2)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande == null && $recept != null && $listeAmis1 == null && $listeAmis2 == null) {
				if(!in_array($donnees[$j], $saveDemandeur)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande == null && $recept != null && $listeAmis1 != null && $listeAmis2 == null) {
				if(!in_array($donnees[$j], $saveDemandeur) && !in_array($donnees[$j], $saveListeAmis1)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande == null && $recept != null && $listeAmis1 == null && $listeAmis2 != null) {
				if(!in_array($donnees[$j], $saveDemandeur) && !in_array($donnees[$j], $saveListeAmis2)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande == null && $recept != null && $listeAmis1 != null && $listeAmis2 != null) {
				if(!in_array($donnees[$j], $saveDemandeur) && !in_array($donnees[$j], $saveListeAmis1) && !in_array($donnees[$j], $saveListeAmis2)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande != null && $recept != null && $listeAmis1 == null && $listeAmis2 == null) {
				if(!in_array($donnees[$j], $saveRecepteur) && !in_array($donnees[$j], $saveDemandeur)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande != null && $recept != null && $listeAmis1 != null && $listeAmis2 == null) {
				if(!in_array($donnees[$j], $saveRecepteur) && !in_array($donnees[$j], $saveDemandeur) && !in_array($donnees[$j], $saveListeAmis1)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande != null && $recept != null && $listeAmis1 != null && $listeAmis2 == null) {
				if(!in_array($donnees[$j], $saveRecepteur) && !in_array($donnees[$j], $saveDemandeur) && !in_array($donnees[$j], $saveListeAmis1)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande != null && $recept != null && $listeAmis1 == null && $listeAmis2 != null) {
				if(!in_array($donnees[$j], $saveRecepteur) && !in_array($donnees[$j], $saveDemandeur) && !in_array($donnees[$j], $saveListeAmis2)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande != null && $recept != null && $listeAmis1 != null && $listeAmis2 != null) {
				if(!in_array($donnees[$j], $saveRecepteur) && !in_array($donnees[$j], $saveDemandeur) && !in_array($donnees[$j], $saveListeAmis1) && !in_array($donnees[$j], $saveListeAmis2)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande == null && $recept == null && $listeAmis1 != null && $listeAmis2 == null) {
				if(!in_array($donnees[$j], $saveListeAmis1)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande == null && $recept == null && $listeAmis1 == null && $listeAmis2 != null) {
				if(!in_array($donnees[$j], $saveListeAmis2)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			elseif($demande == null && $recept == null && $listeAmis1 != null && $listeAmis2 != null) {
				if(!in_array($donnees[$j], $saveListeAmis1) && !in_array($donnees[$j], $saveListeAmis2)) {
					$utilisateursTab [] = $donnees[$j];
				}
			}
			else {
				break;
			}
		
		}
		asort($utilisateursTab);

		if($demande == null && $recept == null && $listeAmis1 == null && $listeAmis2 == null) {
			$utilisateurs = $paginator->paginate(
				$donnees,
				$request->query->getInt("page", 1),
				30
			);
		}
		else {
			$utilisateurs = $paginator->paginate(
				$utilisateursTab,
				$request->query->getInt("page", 1),
				20
			);
		}


		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$utilisateurs->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');

		// On aligne les sélecteurs au centre de la page
		$utilisateurs->setCustomParameters([
				"align" => "center"
		]);

		$demandeUtilisateurs = $paginator->paginate(
			$demande,
			$request->query->getInt("pageDemande", 1),
			5,
			['pageParameterName' => 'pageDemande']
		);

		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$demandeUtilisateurs->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');

		// On aligne les sélecteurs au centre de la page
		$demandeUtilisateurs->setCustomParameters([
				"align" => "center"
		]);

		$receptionUtilisateurs = $paginator->paginate(
			$recept,
			$request->query->getInt("pageReception", 1),
			5,
			['pageParameterName' => 'pageReception']
		);

		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$receptionUtilisateurs->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');

		// On aligne les sélecteurs au centre de la page
		$receptionUtilisateurs->setCustomParameters([
				"align" => "center"
		]);
				
		return $this->render("espace_utilisateur/liste_utilisateur.html.twig", [
				"utilisateurs" => $utilisateurs,
				"demande" => $demandeUtilisateurs,
				"reception" => $receptionUtilisateurs
		]);
	}

	/**
	 * @Route("/client/demandes", name="demandes")
	 */
	public function demandes(?UserInterface $user, DemandeAmiRepository $repoD, PaginatorInterface $paginator, Request $request) {
		$donneesDemandeur = $repoD->createQueryBuilder("r")
						 ->where("r.demandeur = :user")
						 ->setParameter("user", $user)
						 ->orderBy("r.createdAt", "ASC")
						 ->getQuery()
						 ->getResult();
		$donneesRecepteur = $repoD->createQueryBuilder("r")
								  ->where("r.recepteur = :user")
								  ->setParameter("user", $user)
								  ->orderBy("r.createdAt", "ASC")
								  ->getQuery()
								  ->getResult();
		$demandesUser = $paginator->paginate(
			$donneesDemandeur,
			$request->query->getInt("page", 1),
			10
		);
					
		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$demandesUser->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');

		// On aligne les sélecteurs au centre de la page
		$demandesUser->setCustomParameters([
				"align" => "center"
		]);

		$receptionUser = $paginator->paginate(
			$donneesRecepteur,
			$request->query->getInt("page2", 1),
			10,
			['pageParameterName' => 'page2']
		);
					
		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$receptionUser->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');

		// On aligne les sélecteurs au centre de la page
		$receptionUser->setCustomParameters([
				"align" => "center"
		]);

		return $this->render("espace_utilisateur/demandes.html.twig", [
			"demandesUser" => $demandesUser,
			"receptionUser" => $receptionUser
		]);
							
	}

	/**
	 * @Route("/client/demandes/{id}", name="demande_ami")
	 */
	public function demandeAmi($id, ?UserInterface $user, UtilisateursRepository $repoU) {
		$manager = $this->getDoctrine()->getManager();
		$demandeAmi= new DemandeAmi();
		$user2 = $repoU->find($id);
		if($user2 != null) {
			$demandeAmi->setDemandeur($user)
					->setRecepteur($user2)
					->setCreatedAt(new \DateTime());
			$manager->persist($demandeAmi);
			$manager->flush();
		}
		return $this->redirectToRoute('demandes');
	}

	/**
	 * @Route("/client/demandes/supprimer/{id}", name="supprimer_demande")
	 */
	public function supprimerDemande($id, ?UserInterface $user, DemandeAmiRepository $repoD) {
		$manager = $this->getDoctrine()->getManager();
		$demande = $repoD->findBy(["demandeur" => $user, "id" => $id]);
		if($demande != null) {
			$manager->remove($demande[0]);
			$manager->flush();
		}
		return $this->redirectToRoute('demandes');
	}

	/**
	 * @Route("/client/demandes/refuser/{id}", name="refuser_demande")
	 */
	public function refuserDemande($id, ?UserInterface $user, DemandeAmiRepository $repoD) {
		$manager = $this->getDoctrine()->getManager();
		$demande = $repoD->findBy(["recepteur" => $user, "id" => $id]);
		if($demande != null) {
			$manager->remove($demande[0]);
			$manager->flush();
		}
		return $this->redirectToRoute('demandes');
	}

	/**
	 * @Route("/client/demandes/accepter/{id}", name="accepter_demande")
	 */
	public function accepterDemande($id, ?UserInterface $user, DemandeAmiRepository $repoD) {
		$manager = $this->getDoctrine()->getManager();
		$demande = $repoD->findBy(["recepteur" => $user, "id" => $id]);
		if($demande != null) {
			$ami = new Amis();
			$ami->setUtilisateur1($demande[0]->getDemandeur())
				->setUtilisateur2($user)
				->setCreatedAt(new \DateTime());
			$manager->persist($ami);
			$manager->remove($demande[0]);
			$manager->flush();
		}
		return $this->redirectToRoute('demandes');
	}

	/**
	 * @Route("/client/liste_amis", name="liste_amis")
	 */
    public function listeAmis(?UserInterface $user, AmisRepository $repoA, PaginatorInterface $paginator, Request $request) {

		$listeAmis = $repoA->createQueryBuilder("a")
						   ->where("a.utilisateur1 = :user or a.utilisateur2 = :user")
						   ->setParameter("user", $user)
						   ->orderBy("a.id", "ASC")
						   ->getQuery()
						   ->getResult();

		$amis = $paginator->paginate(
			$listeAmis,
			$request->query->getInt("page", 1),
			20
		);

		$amis->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');

		// On aligne les sélecteurs au centre de la page
		$amis->setCustomParameters([
				"align" => "center"
		]);
		return $this->render('espace_utilisateur/liste_ami.html.twig', [
			"listeAmis1" => $amis
		]);
	}

	/**
	 * @Route("/client/liste_amis/supprimer/{id}", name="supprimer_amis")
	 */
	public function supprimerAmis($id, ?UserInterface $user, AmisRepository $repoA) {
		$manager = $this->getDoctrine()->getManager();
		$ami1 = $repoA->findBy(["utilisateur1" => $user, "id" => $id]);
		$ami2 = $repoA->findBy(["utilisateur2" => $user, "id" => $id]);
		if($ami1 != null || $ami2 != null) {
			if($ami1 != null) {
				$manager->remove($ami1[0]);
			}
			else {
				$manager->remove($ami2[0]);
			}
			$manager->flush();
		}
		return $this->redirectToRoute('liste_amis');
	}

	/**
	 * @Route("/client/affiche_message/{id}", name="affiche_message")
	 */
	public function afficheMessage($id, DiscussionPriveeRepository $repoD, ReponsePriveeRepository $repoR , AmisRepository $repoA, UtilisateursRepository $repoU, ?UserInterface $user, Request $request, PaginatorInterface $paginator) {
		$manager = $this->getDoctrine()->getManager();
		$listeAmis = $repoA->createQueryBuilder("a")
						   ->where("(a.utilisateur1 = :user or a.utilisateur2 = :user) and a.id = :id")
						   ->setParameter("user", $user)
						   ->setParameter("id", $id)
						   ->getQuery()
						   ->getResult();
		if($listeAmis != null) {
			$discussion = $repoD->findBy(array("amis" => $listeAmis[0]));
			$listeReponses = $repoR->findBy(array("amis" => $listeAmis[0]));

			$reponses = $paginator->paginate(
				$listeReponses,
				$request->query->getInt("page", 1),
				10
			);

			$reponses->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');

			// On aligne les sélecteurs au centre de la page
			$reponses->setCustomParameters([
					"align" => "center"
			]);
			
			if($discussion == null) {
				return $this->render('espace_utilisateur/messages.html.twig', [
					"discussion" => null,
					"amis" => $listeAmis[0]
				]);
			}
			else {
				return $this->render('espace_utilisateur/messages.html.twig', [
					"discussion" => $discussion[0],
					"reponses" => $reponses,
					"amis" => $listeAmis[0]
				]);
			}
		}
		else {
			return $this->redirectToRoute("liste_amis");
		}
	}

	/**
	 * @Route("/client/affiche_message/discussion/{id}", name="discussion")
	 */
	public function discussion($id, DiscussionPriveeRepository $repoD, AmisRepository $repoA, UtilisateursRepository $repoU, ?UserInterface $user, Request $request) {
		$manager = $this->getDoctrine()->getManager();
		$listeAmis = $repoA->createQueryBuilder("a")
						   ->where("(a.utilisateur1 = :user or a.utilisateur2 = :user) and a.id = :id")
						   ->setParameter("user", $user)
						   ->setParameter("id", $id)
						   ->getQuery()
						   ->getResult();
		if($listeAmis != null) {
			$discussion1 = $repoD->findBy(array("amis" => $listeAmis[0]));

			if($discussion1 == null) {
				if(!empty($request->get('message'))) {
					if($listeAmis[0]->getUtilisateur1() == $user) {
						$discussion = new DiscussionPrivee();
						$discussion->setAmis($listeAmis[0])
								->setMessage($request->get('message'))
								->setCreatedAt(new \DateTime())
								->setEnvoyeurDisc($user)
								->setRecepteurDisc($listeAmis[0]->getUtilisateur2());
						$manager->persist($discussion);
						$manager->flush();
					}
					else {
						$discussion = new DiscussionPrivee();
						$discussion->setAmis($listeAmis[0])
								->setMessage($request->get('message'))
								->setCreatedAt(new \DateTime())
								->setEnvoyeurDisc($user)
								->setRecepteurDisc($listeAmis[0]->getUtilisateur1());
						$manager->persist($discussion);
						$manager->flush();
					}
				}
			}
			return $this->redirectToRoute('affiche_message', [
				"id" => $id
			]);
		}
		else {
			return $this->redirectToRoute("liste_amis");
		}
	}

	/**
	 * @Route("/client/affiche_message/reponses/{id}", name="reponses")
	 */
	public function reponses($id, DiscussionPriveeRepository $repoD, ReponsePriveeRepository $repoR, AmisRepository $repoA, UtilisateursRepository $repoU, ?UserInterface $user, Request $request) {
		$manager = $this->getDoctrine()->getManager();
		$listeAmis = $repoA->createQueryBuilder("a")
						   ->where("(a.utilisateur1 = :user or a.utilisateur2 = :user) and a.id = :id")
						   ->setParameter("user", $user)
						   ->setParameter("id", $id)
						   ->getQuery()
						   ->getResult();
		if($listeAmis != null) {
			$discussion1 = $repoD->findBy(array("amis" => $listeAmis[0]));

			if($discussion1 != null) {
				if(!empty($request->get('message'))) {
					if($listeAmis[0]->getUtilisateur1() == $user) {
						$discussion = new ReponsePrivee();
						$discussion->setAmis($listeAmis[0])
								->setMessage($request->get('message'))
								->setCreatedAt(new \DateTime())
								->setDiscussion($discussion1[0])
								->setEnvoyeurRep($user)
								->setRecepteurRep($listeAmis[0]->getUtilisateur2());
						$manager->persist($discussion);
						$manager->flush();
					}
					else {
						$discussion = new ReponsePrivee();
						$discussion->setAmis($listeAmis[0])
								->setMessage($request->get('message'))
								->setCreatedAt(new \DateTime())
								->setDiscussion($discussion1[0])
								->setEnvoyeurRep($user)
								->setRecepteurRep($listeAmis[0]->getUtilisateur1());
						$manager->persist($discussion);
						$manager->flush();
					}
				}
			}
			return $this->redirectToRoute('affiche_message', [
				"id" => $id
			]);
		}
		else {
			return $this->redirectToRoute("liste_amis");
		}
	}

    /**
     * @Route("/client/info_compte", name="user_show")
     */
	public function show(UtilisateursRepository $repo, ?UserInterface $user) {
		$utilisateur = $repo->findBy(["id" => $user->getId()]);
		if ($utilisateur[0]->getImageBase64() != null) {
			return $this->render('espace_utilisateur/info_compte.html.twig', [
				"image" => $utilisateur[0]->getImageBase64()
			]);
		}
        return $this->render('espace_utilisateur/info_compte.html.twig');
	}
	
	private function transfoCategorieURL( $str, $charset='utf-8' ) {
		
		$str = htmlentities( $str, ENT_NOQUOTES, $charset );
		
		$str = preg_replace( '#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str );
		$str = preg_replace( '#&([A-za-z]{2})(?:lig);#', '\1', $str );
		$str = preg_replace( '#&[^;]+;#', '', $str );
		
		$str = strtolower($str);
		$str = str_replace(" ", "-", $str);
		
		return $str;
	}
}

