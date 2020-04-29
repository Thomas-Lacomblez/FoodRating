<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateurs;

use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateursRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FoodRatingController extends AbstractController
{
    
    /**
     * @Route("/", name="food_rating")
     */
    public function home() {
        return $this->render('food_rating/accueil.html.twig');
    }
    
    
    /**
     * @Route("/liste_des_produits", name="liste_produit")
     * 
     * Fonction temporaire
     * Le paramètre Request permet de récupérer le numéro de la page en cours.
     */
    public function listeProduit(PaginatorInterface $paginator, Request $request, ProduitRepository $repo) {
    	$donnees = $repo->findAll();
    	
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
    
    /**
     * @Route("/produit/{nom}", name="produit")
     */
    public function afficheProduit(Produit $produit) {
    	return $this->render("food_rating/produit.html.twig", [
				"produit" => $produit
    	]);
    }
    
    /**
     * @Route("/compte", name="espace")
     */
    public function espaceClient() {
        return $this->render('food_rating/espace.html.twig');
    }

    /**
     * @Route("/compte/info_compte", name="user_show")
     */
    public function show() {
        return $this->render('food_rating/info_compte.html.twig');
    }
    
    /**
     * @Route("/liste_des_categories", name="liste_categorie")
     * @param ProduitRepository $repo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categorie(PaginatorInterface $paginator, ProduitRepository $repo, Request $request) {
    	$donnees = $repo->createQueryBuilder("p")
    					->select("p.categorie")
    					->distinct()
    					->getQuery()
    					->getResult();
    	
    	$categories = $paginator->paginate(
    			$donnees,
    			$request->query->getInt("page", 1),
    			10
    	);
    					
    	return $this->render("food_rating/liste_categorie.html.twig", [
    			"categories" => $categories,
    			"nbPage" => round(count($donnees) / 10),
    			"pageActuelle" => $request->query->getInt("page", 1),
    			"chemin" => "liste_categorie"
    	]);
    }

}

?>
