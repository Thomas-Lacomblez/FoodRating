<?php

namespace App\Controller;

use League\Csv\Reader;
use OpenFoodFacts\Api;
use App\Repository\NotesRepository;
use App\Repository\CommentairesRepository;
use App\Repository\UtilisateursRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\MoyenneProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EspaceUtilisateurController extends AbstractController
{
    /**
	 * @Route("/client", name="compte_client")
	 */
	public function compteClient(?UserInterface $user, MoyenneProduitsRepository $repo, NotesRepository $repoN, CommentairesRepository $repoC) {
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
		return $this->render('espace_utilisateur/espace.html.twig', [
			"produitsVisite" => $tableauProduitsVisite,
			"categories" => $categories,
			"moyennesProduits" => $moyennesProduits,
			"userProduitsNotes" => $userProduitsNotes,
			"categoriesNotes" => $categoriesNotes,
			"userProduitsCommentaires" => $userProduitsCommentaires,
			"categoriesCommentaires" => $categoriesCommentaires
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

