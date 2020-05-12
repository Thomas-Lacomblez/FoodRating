<?php

namespace App\Controller;

use App\Entity\Notes;
use App\Entity\Produit;

use App\Entity\Utilisateurs;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateursRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenFoodFacts\Api;
use App\Repository\CategoriesRepository;

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
    
    /**
     * @Route("/produit/{id}", name="produit")
     */
    public function afficheProduit(Produit $produit) {
    	return $this->render("food_rating/produit.html.twig", [
				"produit" => $produit
    	]);
    }
    
    /**
     * @Route("/produit_v2/{id}", name="produit_v2")
     */
    public function afficheProduitV2($id) {
    	$api = new Api("food", "fr");
    	$produit = $api->getProduct($id);
    	return $this->render("food_rating/produit_v2.html.twig", [
    			"produit" => $produit
    	]);
    }

    /**
     * @Route("/produit/{id}/notation", name="notation")
     */
    public function notationProduit($id, Produit $produit, Request $request) {
        $note = new Notes();
        $manager = $this->getDoctrine()->getManager();
        $noteForm = $request->get('note');
        $repo = $this->getDoctrine()->getRepository(Produit::class);
        $produitCourant = $repo->find($id);
        
        if (!empty($noteForm)) {
            $note->setNbEtoiles($noteForm)
                 ->setUtilisateur($this->getUser())
                 ->setProduit($produitCourant);
            $manager->persist($note);
            $manager->flush();
        }
        
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
    public function listeCategorie(PaginatorInterface $paginator, CategoriesRepository $repo, Request $request) {
    	$donnees = $repo->createQueryBuilder("c")
    					->select("c.name, c.products, substring(c.url, 40) as url")
    					->getQuery()
    					->getResult();
    	
    	$categories = $paginator->paginate(
    			$donnees,
    			$request->query->getInt("page", 1),
    			10
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
     * @Route("/categorie/{categorie}", name="categorie")
     */
    public function pageCategorie($categorie, PaginatorInterface $paginator, Request $request) {
    	$api = new Api("food", "fr");
    	
    	$collection = $api->getByFacets(["category" => $categorie]);
    	$tab = array();
    	foreach ($collection as $key => $elt) {
    		$tab[] = $elt;
    	}
    	
    	$donnees = $paginator->paginate(
    			$tab,
    			$request->query->getInt("page", 1),
    			10
    	);
    	
    	$donnees->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
    	$donnees->setCustomParameters([
    			"align" => "center"
    	]);
    	
    	return $this->render("food_rating/liste_produit.html.twig", [
    			"produits" => $donnees
    	]);
    }
    
    /**
     * @Route("/test", name="test")
     * @param Api $api
     */
    public function testWrapper(PaginatorInterface $paginator, Request $request) {
    	$api = new Api("food", "fr");
//     	$prd = $api->getProduct('3057640385148');
//     	print("<pre>".print_r($prd,true)."</pre>");

    	$mot = "chips";

    	$recherche = $api->search($mot);
    	$result = array();
    	
    	foreach ($recherche as $key => $prd) {
    		$data = $prd->getData();
    		if (stripos($data["product_name"], $mot) !== false || stripos($data["product_name_fr"], $mot) !== false) {
    			$result[] = $prd;
    		}
    	}
    	    	
    	$result = $paginator->paginate(
    			$result,
    			$request->query->getInt("page", 1),
    			10
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
