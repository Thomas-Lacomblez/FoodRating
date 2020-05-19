<?php

namespace App\Controller;

use App\Entity\Notes;
use OpenFoodFacts\Api;

use App\Entity\Utilisateurs;

use Symfony\Component\Mime\Email;
use App\Repository\NotesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UtilisateursRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * @Route("/categories/{categorie}/produit_v2/{id}", name="produit_v2")
     */
    public function afficheProduitV2($id, ?UserInterface $user) {
    	$api = new Api("food", "fr");
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		if ($user) {
			$manager = $this->getDoctrine()->getManager();
			$note = $manager->getRepository(Notes::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id']]);
			if (!empty($note[0])) {
				return $this->render("food_rating/produit_v2.html.twig", [
					"produit" => $produit,
					"note" => $note[0]
				]);
			}
			else {
				return $this->render("food_rating/produit_v2.html.twig", [
					"produit" => $produit
				]);
			}
		}
		else {
			echo "aaa";
			return $this->render("food_rating/produit_v2.html.twig", [
					"produit" => $produit
			]);
		}
    }

    /**
     * @Route("/categories/{categorie}/produit_v2/{id}/notation", name="notation")
     */
    public function notationProduit($id, Request $request) {
		$api = new Api("food", "fr");
		$note = new Notes();
		$manager = $this->getDoctrine()->getManager();
		$noteForm = $request->get('note');
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		$categorieProduit = explode(",", $data['categories']);
	
		if (!empty($noteForm)) {
			$note->setNbEtoiles($noteForm)
				 ->setUtilisateur($this->getUser())
				 ->setProduitId($id);
			$manager->persist($note);
			$manager->flush();
			return $this->redirectToRoute('produit_v2', [
				"id" => $id,
				"categorie" => $categorieProduit[0]
			]);
		}
	
		return $this->render("food_rating/produit_v2.html.twig", [
				"produit" => $produit
		]);
	}

	/**
	 * @Route("/categories/{categorie}/produit_v2/{id}/modifier_notation", name="modifier_notation")
	 */
	public function modifierNotationProduit($id, Request $request, UserInterface $user) {
		$api = new Api("food", "fr");
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		$categorieProduit = explode(",", $data['categories']);
		$manager = $this->getDoctrine()->getManager();
		$note = $manager->getRepository(Notes::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id']]);
		$noteForm = $request->get('note');

		if (!empty($noteForm)) {
			$note[0]->setNbEtoiles($noteForm);
			$manager->flush();
			return $this->redirectToRoute('produit_v2', [
				"id" => $id,
				"categorie" => $categorieProduit[0]
			]);
		}

		return $this->render("food_rating/produit_v2.html.twig", [
			"produit" => $produit
		]);
	}

	/**
	 * @Route("/categories/{categorie}/produit_v2/{id}/supprimer_notation", name="supprimer_notation")
	 */
	public function supprimerNotationProduit($id, Request $request, UserInterface $user) {
		$api = new Api("food", "fr");
		$produit = $api->getProduct($id);
		$data = $produit->getData();
		$categorieProduit = explode(",", $data['categories']);
		$manager = $this->getDoctrine()->getManager();
		$note = $manager->getRepository(Notes::class)->findBy(['utilisateur' => $user, 'produit_id' => $data['id']]);
		$manager->remove($note[0]);
		$manager->flush();

		return $this->redirectToRoute('produit_v2', [
			"id" => $id,
			"categorie" => $categorieProduit[0]
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
     * @Route("/categories", name="liste_categorie")
     */
    public function listeCategorie(PaginatorInterface $paginator, CategoriesRepository $repo, Request $request) {
    	$donnees = $repo->createQueryBuilder("c")
    					// "40" correspond au nombre de caractères avant le dernier slash de l'url inclus
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
     * @Route("/categories/{categorie}", name="categorie")
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
     * @Route("/debug/{id}", name="debug")
     */
    public function debugProduit($id) {
    	$api = new Api("food", "fr");
    	$prd = $api->getProduct($id);
    	print("<pre>".print_r($prd,true)."</pre>");
    	
    	return null;
    }
    
    /**
     * @Route("/test", name="test")
     * @param Api $api
     */
    public function testWrapper(PaginatorInterface $paginator, Request $request) {
		$api = new Api("food", "fr");
		$produit = $api->getProduct("3242274000059");
//     	$prd = $api->getProduct('3057640385148');
//     	print("<pre>".print_r($prd,true)."</pre>");

    	$mot = "couscous";

		$recherche = $api->search($mot, 1, 30);
		$compteur = $recherche->searchCount();

		$result = array();
		
		dump($recherche);

		foreach ($recherche as $key => $prd) {
			$data = $prd->getData();
			$result[] = $prd;
		}
    	    	
    	$result = $paginator->paginate(
				$categorieProduit,
    			$request->query->getInt("page", 1),
    			20
    	);
    	
    	$result->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
    	$result->setCustomParameters([
    			"align" => "center"
    	]);
    	
    	return $this->render("food_rating/liste_produit.html.twig", [
    			"produits" => $result
    	]);
    	
	}
	
	/**
     * @Route("/mail", name="mail")
     */
	public function testMail(MailerInterface $mailer){
		$email = (new Email())
            ->from('zorgthomas92@gmail.com')
            ->to('thomasmn.martin@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
	}

}

?>
