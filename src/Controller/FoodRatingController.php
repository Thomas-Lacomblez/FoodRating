<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Repository\ProduitRepository;

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
     */
    public function listeProduit(ProduitRepository $repo) {
    	$produits = $repo->findAll();
    	
    	return $this->render("food_rating/liste_produit.html.twig", [
    			"produits" => $produits
    	]);
    }
    
    /**
     * @Route("/produit/{categorie}/{nom}", name="produit")
     */
    public function afficheProduit(Produit $produit) {
    	return $this->render("food_rating/produit.html.twig", [
    			"produit" => $produit
    	]);
    }
}

?>
