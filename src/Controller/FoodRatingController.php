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
     * @Route("/recherche", name="recherche")
     */
    public function recherche(Request $request) {
        $noms = array();
        $term = trim(strip_tags($request->get('term')));

        $manager = $this->getDoctrine()->getManager();

        // Permet de faire un SELECT dans la table Produit
        $entities = $manager->getRepository(Produit::class)->CreateQueryBuilder('p')
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
    			"pageActuelle" => $request->query->getInt("page", 1)
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

}

?>
