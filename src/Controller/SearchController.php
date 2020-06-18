<?php

namespace App\Controller;

use App\Entity\Notes;
use OpenFoodFacts\Api;
use App\Entity\Utilisateurs;
use App\Repository\CategoriesRepository;
use App\Repository\UtilisateursRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\MoyenneProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
     /**
     * @Route("/recherche", name="recherche")
     */
    public function recherche(Request $request) {
    	$api = new Api("food", "fr");

    	$result = array();
    	$term = trim(strip_tags($request->get('term')));

		$recherche = $api->search($term, 1, 30);
		$compteur = $recherche->searchCount();
		$result = array();

		foreach ($recherche as $key => $prd) {
			$data = $prd->getData();
			$result[] = $data['product_name'];
		}
    	
    	$resultat = new JsonResponse();
    	$resultat->setData($result);
    	
		return $resultat;
    }

    /**
	 * @Route("/resultat", name="resultat")
	 */
	public function resultat(Request $request, PaginatorInterface $paginator, CategoriesRepository $repoC, MoyenneProduitsRepository $repo) {
	
		$api = new Api("food", "fr");
    	$mot = $request->get('recherche');
    	
    	if (is_null($mot)) { return $this->redirectToRoute("food_rating"); }

		$recherche = $api->search($mot, $request->query->getInt("page", 1));
		$compteur = $recherche->searchCount();
		$donnees = array();
		$manager = $this->getDoctrine()->getManager();
		$notesProduits = $manager->getRepository(Notes::class)->findAll();
		$moyennesProduits = $repo->findAll();
		
    	for ($i = 1 ; $i < $compteur/20 + 1 ; $i++){
			foreach ($recherche as $key => $prd) {
				$data = $prd->getData();
				
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
				$donnees[] = $data;
			}
		}
		
		$produits = $paginator->paginate(
			$donnees,
			$request->query->getInt("page", 1),
			$recherche->pageCount()
		);
				
		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$produits->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
		
		// On aligne les sélecteurs au centre de la page
		$produits->setCustomParameters([
				"align" => "center"
		]);


				
		if (count($produits) == 1 && $moyennesProduits != null) {
			return $this->redirectToRoute('produit_v2', [
					"id" => $produits[0]->getData()["id"] ?? $produits[0]->getData()["code"],
					"categorie" => $produits[0]->getData()["categorie_url"],
					"from_search" => " ",
					"moyennesProduits" => $moyennesProduits
			]);
		}

		else if(count($produits) == 1 && $moyennesProduits == null) {
			return $this->redirectToRoute('produit_v2', [
				"id" => $produits[0]->getData()["id"] ?? $produits[0]->getData()["code"],
				"categorie" => $produits[0]->getData()["categorie_url"],
				"from_search" => " "
			]);
		}
		else if(count($produits) != 1 && $moyennesProduits != null) {
			return $this->render("food_rating/liste_produit.html.twig", [
				"produits" => $produits,
				"notes" => $notesProduits,
				"from_search" => " ",
				"moyennesProduits" => $moyennesProduits
			]);
		}
		
    	return $this->render("food_rating/liste_produit.html.twig", [
			"produits" => $produits,
			"notes" => $notesProduits,
    		"from_search" => " "
		]);
	}

	 /**
     * @Route("/recherche_user", name="recherche_user")
     */
    public function rechercheUtilisateur(Request $request, ?UserInterface $user) {

    	$result = array();
		$term = trim(strip_tags($request->get('term')));
		
		$manager = $this->getDoctrine()->getManager();

		$entities = $manager->getRepository(Utilisateurs::class)->CreateQueryBuilder('u')
							->where('u.username LIKE :username and u.username not like :user')
							->setParameter('username', '%'.$term.'%')
							->setParameter('user', '%'.$user->getUsername().'%')
							->getQuery()
							->getResult();
		$result = array();

		foreach ($entities as $entity) {
			$result[] = $entity->getUsername();
		}
    	
    	$resultat = new JsonResponse();
    	$resultat->setData($result);
    	
    	return $resultat;
	}
	
	/**
	 * @Route("/resultat_user", name="resultat_user")
	 */
	public function resultatUtilisateurs(Request $request, PaginatorInterface $paginator, UtilisateursRepository $repoU, ?UserInterface $user) {
	
    	$mot = $request->get('recherche_utilisateur');
    	
    	if (is_null($mot)) { return $this->redirectToRoute("food_rating"); }

		$donnees = array();
		$manager = $this->getDoctrine()->getManager();
		$recherche = $repoU->CreateQueryBuilder('u')
						   ->where('u.username LIKE :username and u.username not like :user')
						   ->setParameter('username', '%'.$mot.'%')
						   ->setParameter('user', '%'.$user->getUsername().'%')
						   ->getQuery()
						   ->getResult();
		
		$users = $paginator->paginate(
			$recherche,
			$request->query->getInt("page", 1),
			20
		);
				
		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$users->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
		
		// On aligne les sélecteurs au centre de la page
		$users->setCustomParameters([
				"align" => "center"
		]);
		
    	return $this->render("espace_utilisateur/liste_utilisateur.html.twig", [
			"utilisateurs" => $users
		]);
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