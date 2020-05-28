<?php

namespace App\Controller;

use App\Entity\Notes;
use OpenFoodFacts\Api;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\CategoriesRepository;

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
	public function resultat(Request $request, PaginatorInterface $paginator, CategoriesRepository $repoC) {
	
		$api = new Api("food", "fr");
    	$mot = $request->get('recherche');

		$recherche = $api->search($mot, $request->query->getInt("page", 1));
		$compteur = $recherche->searchCount();
		$donnees = array();
		$manager = $this->getDoctrine()->getManager();
		$notesProduits = $manager->getRepository(Notes::class)->findAll();
				
    	for ($i = 1 ; $i < $compteur/20 + 1 ; $i++){
			foreach ($recherche as $key => $prd) {
				$data = $prd->getData();
				$categorie = explode(",", $data["categories"])[0];
				
				if (strpos($data["categories_tags"][0], "en:") !== false && strpos($categorie, ":") === false) {
					$categorie = $this->transfoCategorieURL($categorie);
				} else {
					$categorie = substr($repoC->find($data["categories_tags"][0])->getUrl(), 40);
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
						
		if (count($produits) == 1) {
			return $this->redirectToRoute('produit_v2', [
					"id" => $produits[0]->getData()["id"] ?? $produits[0]->getData()["code"],
					"categorie" => $produits[0]->getData()["categorie_url"],
					"from_search" => " "
			]);
		}
		
    	return $this->render("food_rating/liste_produit.html.twig", [
			"produits" => $produits,
			"notes" => $notesProduits,
    		"from_search" => " "
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