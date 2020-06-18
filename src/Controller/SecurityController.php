<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Form\InscriptionType;
use App\Form\DonneesModifType;
use App\Form\MotDePasseModifType;

use App\Repository\NotesRepository;
use App\Repository\UtilisateursRepository;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\MoyenneProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="login_security")
     */

    public function connexion(Request $request, AuthenticationUtils $authenticationUtils) {
        $erreur = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/connexion.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $erreur
        ));
    }

    /**
     * @Route("/inscription_admin", name="inscription_admin")
     * A utiliser une fois
     */

    public function formInscriptionAdmin(Request $request, UserPasswordEncoderInterface $encoder) {

        $admin = new Utilisateurs();
        $manager = $this->getDoctrine()->getManager();
        $password = "admin";
        $hash = $encoder->encodePassword($admin, $password);
        $admin->setUsername("admin")
              ->setEmail("admin@foodrating.fr")
              ->setPassword($hash)
              ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $manager->flush();

        return $this->render('food_rating/accueil.html.twig');
    }
    /**
     * @Route("/inscription", name="inscription")
     */

    public function formInscription(Request $request, UserPasswordEncoderInterface $encoder) {

        $user = new Utilisateurs();
        $manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(InscriptionType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash)
                 ->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('inscription', 'Inscription réussie');
            return $this->redirectToRoute('login_security');

        }
        return $this->render('security/inscription.html.twig', [
            'formInscription' => $form->createView()
        ]);
    }

    /**
     * @Route("/client/info_compte/modifier_donnees", name="modifier_donnees")
     */
    public function formModifierDonnees(Request $request) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $manager = $this->getDoctrine()->getManager();
        $userModif = $manager->getRepository(Utilisateurs::class)->find($user->getId());

        $form = $this->createForm(DonneesModifType::class, $userModif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageTest = $form->get('imagebase64')->getData();
            if($imageTest) {
                $blob = fopen($imageTest, 'rb');
                $user->setImageBase64(base64_encode(stream_get_contents($blob)));
            }
            $manager->flush();
            return $this->redirectToRoute('user_show');

        }
        return $this->render('security/modifier_donnees.html.twig', [
            'formModifierDonnees' => $form->createView()
        ]);
    }
    /**
     * @Route("/client/info_compte/modifier_donnees/modifier_mdp", name="modifier_mdp")
     */
    public function modifierMotDePasse(Request $request, UserPasswordEncoderInterface $encoder) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $manager = $this->getDoctrine()->getManager();
        $userModif = $manager->getRepository(Utilisateurs::class)->find($user->getId());

        $form = $this->createForm(MotDePasseModifType::class, $userModif);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $manager->flush();
            return $this->redirectToRoute('modifier_donnees');
        }
        return $this->render('security/modifier_mdp.html.twig', [
            'formModifierMdp' => $form->createView(),
        ]);
    }

    /**
     * @Route("/client/info_compte/modifier_donnees/supprimer_photo", name="supprimer_photo")
     */
    public function supprimerPhoto(Request $request) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $manager = $this->getDoctrine()->getManager();
        $userModif = $manager->getRepository(Utilisateurs::class)->find($user->getId());

        if($userModif->getImageBase64() != null) {
            $user->setImageBase64(null);
            $manager->flush();
        }
        return $this->redirectToRoute('modifier_donnees');
    }

    /**
     * @Route("/client/info_compte/modifier_donnees/desinscription", name="desinscription")
     */
     public function desinscription(MoyenneProduitsRepository $repoM, NotesRepository $repoN) {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $manager = $this->getDoctrine()->getManager();
        $notesUser = $repoN->findBy(["utilisateur" => $user]);
        $tabProduitMoyenne = array();
        $idProduitNotesSupprimees = array();

        $filesystem = new Filesystem();
		if($filesystem->exists('csv/'. $user->getId())) {
			$filesystem->remove('csv/'. $user->getId());
		}
        
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

            for($i = 0; $i < sizeof($idProduitNotesSupprimees); $i++) {
                if($idProduitNotesSupprimees[$i] == $tabProduitMoyenne[$i]) {
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
        }

        $manager->remove($user);
        $manager->flush();
        $session = $this->get('session');
        $session = new Session();
        $session->invalidate();
        return $this->redirectToRoute('food_rating');

     }

    /**
     * @Route("/deconnexion", name="logout_security")
     */

    public function logout() {}
}
