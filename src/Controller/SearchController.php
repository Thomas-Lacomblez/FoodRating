<?php

namespace App\Controller;

use OpenFoodFacts\Api;
use App\Repository\ProduitRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
     /**
     * @Route("/recherche", name="recherche")
     */
    public function recherche(Request $request, ProduitRepository $repo) {
    	$api = new Api("food", "fr");

    	$result = array();
    	$term = trim(strip_tags($request->get('term')));
    	$recherche = $api->search($term);
		
		foreach ($recherche as $key => $prd) {
    		$data = $prd->getData();
    		if (stripos($data["product_name"], $term) !== false) {
    			$result[] = $data["product_name"];
			}
			else if (stripos($data["product_name_fr"], $term) !== false) {
				$result[] = $data["product_name_fr"];
			}
    	}
    	
    	$resultat = new JsonResponse();
    	$resultat->setData($result);
    	
    	return $resultat;
    }

    /**
	 * @Route("/resultat", name="resultat")
	 */
	public function resultat(Request $request, ProduitRepository $repo, PaginatorInterface $paginator) {
		$api = new Api("food", "fr");

    	$mot = $request->get('recherche');

    	$recherche = $api->search($mot);
    	$donnees = array();
    	
    	foreach ($recherche as $key => $prd) {
    		$data = $prd->getData();
    		if (stripos($data["product_name"], $mot) !== false || stripos($data["product_name_fr"], $mot) !== false) {
    			$donnees[] = $prd;
    		}
    	}
		
		$produits = $paginator->paginate(
			$donnees,
			$request->query->getInt("page", 1),
			10
		);
		
		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$produits->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
		
		// On aligne les sélecteurs au centre de la page
		$produits->setCustomParameters([
				"align" => "center"
		]);
		
    	return $this->render("food_rating/liste_produit.html.twig", [
			"produits" => $produits
		]);
	}
}