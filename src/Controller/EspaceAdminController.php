<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\Discussion;
use App\Entity\Utilisateurs;
use App\Repository\NotesRepository;
use App\Repository\MoyenneProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EspaceAdminController extends AbstractController
{

    /**
	 * @Route("/admin", name="compte_admin")
	 */
	public function compteAdmin() {
		$manager = $this->getDoctrine()->getManager();
		$utilisateurs = $manager->getRepository(Utilisateurs::class)->findByRole('ROLE_USER');
		dump($utilisateurs);

		return $this->render("food_rating/espace_admin.html.twig", [
			"utilisateurs" => $utilisateurs
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

		return $this->redirectToRoute("compte_admin");
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
	 * @Route("/admin/discussion/suppression", name="suppression_discussion")
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
}

?>