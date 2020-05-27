<?php

namespace App\Controller;

use OpenFoodFacts\Api;
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
	public function resultat(Request $request, PaginatorInterface $paginator) {
	
		$api = new Api("food", "fr");

    	$mot = $request->get('recherche');

		$recherche = $api->search($mot, $request->query->getInt("page", 1));
		$compteur = $recherche->searchCount();
    	$donnees = array();
    	
    	for ($i = 1 ; $i < $compteur/20 + 1 ; $i++){
			foreach ($recherche as $key => $prd) {
				$data = $prd->getData();
				$donnees[] = $prd;
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
		
		dump($produits);
		
		if (count($produits) == 1) {
			return $this->redirectToRoute('produit_v2', [
					"id" => $produits[0]->getData()["id"] ?? $produits[0]->getData()["code"],
					"categorie" => explode(",", $produits[0]->getData()["categories"])[0]
			]);
		}
		
    	return $this->render("food_rating/liste_produit.html.twig", [
			"produits" => $produits
		]);
	}
}