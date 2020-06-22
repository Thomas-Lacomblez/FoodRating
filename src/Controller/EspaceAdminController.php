<?php

namespace App\Controller;

use App\Entity\Notes;
use OpenFoodFacts\Api;
use App\Entity\Reponse;
use App\Entity\Discussion;
use App\Entity\Commentaires;
use App\Entity\Utilisateurs;
use App\Repository\NotesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UtilisateursRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\MoyenneProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;

class EspaceAdminController extends AbstractController
{

    /**
	 * @Route("/admin", name="compte_admin")
	 */
	public function compteAdmin() {
		$manager = $this->getDoctrine()->getManager();
		$utilisateurs = $manager->getRepository(Utilisateurs::class)->findByRole('ROLE_USER');

		return $this->render("food_rating/espace_admin.html.twig");
	}

	/**
	 * @Route("/admin/liste_utilisateurs", name="liste_utilisateurs_admin")
	 */
	public function afficheUtilisateurs(Request $request){
		$manager = $this->getDoctrine()->getManager();
		$utilisateurs = $manager->getRepository(Utilisateurs::class)->findByRole('ROLE_USER');

		return $this->render("food_rating/admin_liste_utilisateurs.html.twig", [
			"utilisateurs" => $utilisateurs
		]);
	}

	/**
	 * @Route("/admin/liste_utilisateurs_signales", name="liste_utilisateurs_signales_admin")
	 */
	public function afficheUtilisateursSignales(Request $request, UtilisateursRepository $repoU){
		$manager = $this->getDoctrine()->getManager();
		$utilisateurs = $manager->getRepository(Utilisateurs::class)->findByRole('ROLE_USER');

		$utilisateurs = $repoU->createQueryBuilder('u')
			->orderBy('u.nombreSignalement', 'DESC')
			->where('u.nombreSignalement > :signal')
			->setParameter('signal', 0)
			->andWhere('u.roles LIKE :role')
			->setParameter('role', '%"'.'ROLE_USER'.'"%')
			->getQuery()
			->getResult();

		return $this->render("food_rating/admin_liste_signales.html.twig", [
			"utilisateurs" => $utilisateurs
		]);
	}
	
	/**
	 * @Route("/admin/produit_note", name="produit_note_admin")
	 */
	public function afficheProduitNote(Request $request, NotesRepository $repoN, CategoriesRepository $repoC, MoyenneProduitsRepository $repoMoy, PaginatorInterface $paginator){
		$api = new Api("food", "fr");
		$notes = $repoN->createQueryBuilder('n')
           ->groupBy('n.produit_id')
           ->getQuery()
		   ->getResult();
		   
		$categorie = "";
		$saveProduit = array();
		for ($i = 0; $i < sizeof($notes); $i++) {
			$produit = $api->getProduct($notes[$i]->getProduitId());
			$saveProduit [] = $produit;

			$data = $saveProduit[$i]->getData();
		
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
			$categoriesNotes[] = $data;
		}

		$produits = $paginator->paginate(
			$saveProduit,
			$request->query->getInt("page", 1),
			20
		);
				
		// On utilise un template basé sur Bootstrap, celui par défaut ne l'est pas
		$produits->setTemplate('@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig');
		
		// On aligne les sélecteurs au centre de la page
		$produits->setCustomParameters([
				"align" => "center"
		]);

		$moyennesProduits = $repoMoy->findAll();

		return $this->render("food_rating/liste_produit.html.twig", [
			"produits" => $produits,
			"categorie" => $categorie,
			"moyennesProduits" => $moyennesProduits
		]);
	}
    
	/**
	 * @Route("/admin/users/suppression", name="suppression_user")
	 */
	public function suppressionUser(Request $request, MoyenneProduitsRepository $repoM, NotesRepository $repoN){

		$manager = $this->getDoctrine()->getManager();
		$pseudo = "";
		if ($request->query->has("uas")){
			$user = $manager->getRepository(Utilisateurs::class)->findOneBy(['email' => $request->query->get("uas")]);

			// Dans le cas où l'admin envoie en paramètre URL une adresse qui n'existe pas
			if (empty($user)){
				$this->addFlash(
					'notice',
					"L'utilisateur que vous avez demandé de supprimer n'existe pas."
				);
			}
			// Dans le cas où l'admin envoie en paramètre URL une adresse d'un admin
			elseif ($user->getRoles() == ['ROLE_ADMIN']) {
				$pseudo = $user->getUsername();
				$this->addFlash(
					'notice',
					'L\'utilisateur ' . $pseudo . " est un admin ! Il ne peut pas être supprimé."
				);
			}
			// Dans le cas où l'admin envoie en paramètre URL une adresse d'un user ou a cliqué sur le bouton Supprimer à côté d'un user
			else {
                $notesUser = $repoN->findBy(["utilisateur" => $user]);
                $tabProduitMoyenne = array();
                $idProduitNotesSupprimees = array();

                /*
                $filesystem = new Filesystem();
                if($filesystem->exists('csv/'. $user->getId())) {
                    $filesystem->remove('csv/'. $user->getId());
                }*/
                
                if($notesUser != null) {
                    for($note = 0; $note < sizeof($notesUser); $note++) {
                        $produitMoyenne = $repoM->findBy(["produit_id" => $notesUser[$note]->getProduitId()]);
                        echo sizeof($notesUser). " ".sizeof($produitMoyenne) ;
                        dump($produitMoyenne);
                        $tabProduitMoyenne [] = $produitMoyenne[0]->getProduitId();
                        $idProduitNotesSupprimees [] = $notesUser[$note]->getProduitId();
                        $manager->remove($notesUser[$note]);
                        $manager->flush();
                    }

                    asort($tabProduitMoyenne);
                    asort($idProduitNotesSupprimees);

                    for($i = 0; $i < sizeof($idProduitNotesSupprimees); $i++) {
                        $notes = $repoN->findBy(["produit_id" => $idProduitNotesSupprimees[$i]]);
                        $moyennes = $repoM->findBy(["produit_id" => $tabProduitMoyenne[$i]]);
                        $saveNote = array();
                        for ($m = 0; $m < sizeof($notes); $m++) {
                            $saveNote[] = $notes[$m]->getNbEtoiles();
                        }
                        $moyenneNote = round((array_sum($saveNote)/count($saveNote)), 2);
                        $moyennes[0]->setMoyenne($moyenneNote);
                        $manager->flush();
                    }
                }

				$manager->remove($user);
				$manager->flush();
				$pseudo = $user->getUsername();
				$this->addFlash(
					'notice',
					'L\'utilisateur ' . $pseudo . " a été supprimé"
				);
			}
		}

		$utilisateurs = $manager->getRepository(Utilisateurs::class)->findAll();

		return $this->redirectToRoute("liste_utilisateurs_admin");
	}

	/**
	 * @Route("/admin/reponse/suppression", name="suppression_reponse")
	 */
	public function suppressionReponse(Request $request){

		$manager = $this->getDoctrine()->getManager();
		if ($request->query->has("aas")){
			$reponse = $manager->getRepository(Reponse::class)->findOneBy(['id' => $request->query->get("aas")]);

			// Dans le cas où l'admin envoie en paramètre URL une id de reponse qui n'existe pas
			if (empty($reponse)){
				$this->addFlash(
					'suppr_rep',
					"La réponse que vous avez demandé de supprimer n'existe pas."
				);
			}
			else {
				$manager->remove($reponse);
				$manager->flush();
				$this->addFlash(
					'suppr_rep',
					"La réponse a été supprimée"
				);
			}
		}
		if ($request->query->has("numerodiscussion")){
			return $this->redirectToRoute("readDisc", [
				'id' => $request->query->getInt("numerodiscussion")
			]);
		}
		else {
			return $this->redirectToRoute("forum");
		}
	}

	/**
	 * @Route("/admin/suppression/discussion", name="suppression_discussion")
	 */
	public function suppressionDiscussion(Request $request){
		$manager = $this->getDoctrine()->getManager();
		if ($request->query->has("das")){
			$discussion = $manager->getRepository(Discussion::class)->findOneBy(['idDiscussion' => $request->query->get("das")]);

			// Dans le cas où l'admin envoie en paramètre URL une id de reponse qui n'existe pas
			if (empty($discussion)){
				$this->addFlash(
					'suppr_disc',
					"La discussion que vous avez demandé de supprimer n'existe pas."
				);
			}
			else {
				$manager->remove($discussion);
				$manager->flush();
				$this->addFlash(
					'suppr_disc',
					"La discussion a été supprimée"
				);
			}
		}
		return $this->redirectToRoute("forum");
		
	}

	/**
	 * @Route("/admin/suppression/commentaire_et_note", name="suppression_commentaire_et_note")
	 */
	public function suppressionCommentaireEtNote(Request $request, MoyenneProduitsRepository $repoM, NotesRepository $repoN){
		$manager = $this->getDoctrine()->getManager();
		$commentaire = "";
		if ($request->query->has("nas") && $request->query->has("cas") && $request->query->has("id")){
			$note = $manager->getRepository(Notes::class)->findOneBy(['id' => $request->query->get("nas")]);
			if ($request->query->has("cas")){
				$commentaire = $manager->getRepository(Commentaires::class)->findOneBy(['id' => $request->query->get("cas")]);
			}

			// Dans le cas où l'admin envoie en paramètre URL une id de reponse qui n'existe pas
			if (empty($note)){
				$this->addFlash(
					'suppr_note',
					"La note que vous avez demandé de supprimer n'existe pas."
				);
			}
			else {
				$manager->remove($note);
				$manager->flush();
				if (!empty($commentaire) && $commentaire != ""){
					$manager->remove($commentaire);
					$manager->flush();
				}
				$this->addFlash(
					'suppr_note',
					"La note a été supprimée"
				);
				$notes = $repoN->findBy(["produit_id" => $request->query->get("id")]);
				$moyenne = $repoM->findBy(["produit_id" => $request->query->get("id")]);
				$saveNote = array();
				for ($m = 0; $m < sizeof($notes); $m++) {
					$saveNote[] = $notes[$m]->getNbEtoiles();
				}
				$moyenneNote = round((array_sum($saveNote)/count($saveNote)), 2);
				$moyenne[0]->setMoyenne($moyenneNote);
				$manager->flush();
			}
		}
		if ($request->query->has("id") && $request->query->has("categorie")){
			return $this->redirectToRoute("produit_v2", [
				'id' => $request->query->get("id"),
				'categorie' => $request->query->get("categorie")
			]);
		}
		else {
			return $this->redirectToRoute("food_rating");
		}
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
	 /**
	 * @Route("/admin/newsletter", name="newsletter")
	 */

	public function newsletter(MailerInterface $mailer, UtilisateursRepository $repoU, Request $request ) {
		$emailAdmin = "Lacomblez.thomas@gmail.com";
		$userAdresse = array();
		$formMessage = $this->createFormBuilder(null)
			->add('Sujet', TextType::class)
            ->add('Message', TextType::class)
            ->add('Envoyer', SubmitType::class, ['label' => 'Envoyer'])
			->getForm();

		$formMessage->handleRequest($request);
		
		if($formMessage->isSubmitted() && $formMessage->isValid() ) {
			//echo "dans if submit";
			$data = $formMessage->getData();
			$users = $repoU->createQueryBuilder('u')
				->select("u")
				->where("u.roles = :roles")
				->setParameter(':roles','a:0:{}')
				->getQuery()
				->getResult();
			//dump($userAdresse);
			foreach($users as $user) {
				//$userAdresse[] = $user->getEmail();
				$email = (new Email())
					->from($emailAdmin)
					->to( $user->getEmail())
					->subject($data["Sujet"])
					->text($data["Message"]);
				$mailer->send($email);
			}
		}
		//
		return $this->render("food_rating/newsletter.htmlt.twig", [
			'form' => $formMessage->createView(),
			"userAdresse" => $userAdresse
		]);
	}

}

?>