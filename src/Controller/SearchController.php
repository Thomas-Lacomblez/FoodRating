<?php

namespace App\Controller;

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
    	$noms = array();
    	$term = trim(strip_tags($request->get('term')));
    	
    	// Permet de faire un SELECT dans la table Produit
    	$entities = $repo->CreateQueryBuilder('p')
    	->where('p.nom LIKE :nom')
    	->setParameter('nom', '%'.$term.'%')
    	->getQuery()
    	->getResult();
    	
    	foreach ($entities as $entity) {
    		$noms[] = $entity->getNom();
    	}
    	
    	$resultat = new JsonResponse();
    	$resultat->setData($noms);
    	
    	return $resultat;
    }

    /**
	 * @Route("/resultat", name="resultat")
	 */
	public function resultat(Request $request, ProduitRepository $repo, PaginatorInterface $paginator) {
		$recherche = $request->get('recherche');
		$donnees = $repo->CreateQueryBuilder('p')
    					->where('p.nom LIKE :nom')
    					->setParameter('nom', '%'.$recherche.'%')
    					->getQuery()
						->getResult();
		$produits = $paginator->paginate(
			$donnees,
			$request->query->getInt("page", 1),
			10
		);
    	return $this->render("food_rating/liste_produit.html.twig", [
			"produits" => $produits,
			"nbPage" => round(count($donnees) / 10),
			"pageActuelle" => $request->query->getInt("page", 1),
			"chemin" => "liste_produit"
		]);
	}
}
