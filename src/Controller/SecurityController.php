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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends AbstractController
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }
    /**
     * @Route("/connexion", name="login_security")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/connexion.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
            ]);
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

    public function formInscription(Request $request, UserPasswordEncoderInterface $encoder, MailerInterface $mailer) {
        $emailAdmin = "Lacomblez.thomas@gmail.com";
        $user = new Utilisateurs();
        $manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(InscriptionType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $vkey = md5(time().$user->getUsername());
            $user->setVkey($vkey);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            $email = new TemplatedEmail();
            $email->from( $emailAdmin );
            $email->to( $user->getEmail() );
            $email->htmlTemplate( 'registration/confirmation_email.html.twig' );
            $signedUrl = $this->router->generate('verify', [
                'vkey' => $vkey,
            ], UrlGeneratorInterface::ABSOLUTE_URL
            );

            echo $signedUrl;
            $email->context( ['signedUrl' => $signedUrl] );

            $mailer->send( $email );

            //return $this->redirectToRoute('login_security');
            return $this->render('security/attente_confirmation.html.twig');
        }
        return $this->render('security/inscription.html.twig', [
            'formInscription' => $form->createView()
        ]);
    }

    /**
     * @Route("/verify/{vkey}", name="verify")
     */
    public function verifyUserEmail(Request $request, UtilisateursRepository $repoUser, $vkey) {

        try {
            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(Utilisateurs::class)->findOneBy( ['vkey' => $vkey] );

            if (!$user) {
                throw $this->createNotFoundException(
                    'No User found. Retry later or contact your administrator'
                );
            }

            echo $user->getUsername();
            $user->setVerified(True);
            $entityManager->flush();
            $this->addFlash('success', 'Your e-mail address has been verified.');
        }
        catch (Exception $e) {
            $this->addFlash('UserUpdateError', $e->getMessage());
            return $this->redirectToRoute('inscription');
        }
        catch (NotFoundException $e) {
            $this->addFlash('UserNotFound', $e->getReason());

            return $this->redirectToRoute('inscription');
        }

        // Mark your user as verified. e.g. switch a User::verified property to true

        return $this->redirectToRoute('login_security');
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
        $emailAdmin = "Lacomblez.thomas@gmail.com";

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($userModif, $userModif->getPassword());
            $contactUrl = $this->router->generate('contact', UrlGeneratorInterface::ABSOLUTE_URL 
            );
            $user->setPassword($hash);
            $email = (new Email())
                ->from($emailAdmin)
                ->to($user->getEmail())
                ->subject("Modification des information de votre compte")
                ->text("vos donnez ont été modifier avec succès, si vous n'avez pas fait cette modification vous êtes peut-être la cible d'un piratage de votre compte, si c'est le cas nous vous conseillons de changer votre mot de passe le plus rapidement possible, si vous rencontrez un quelconque problème vous pouvez contacter un administrateur par le biais du formulaire de contact, à l'adresse suivant :" . $contactUrl);
            $mailer->send($email);
            $manager->flush();

            return $this->redirectToRoute('espace');

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
      * @Route("/logout", name="logout_security")
      */
     public function logout()
     {
         //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
     }

    public function getAutoIncrement() {
        $query = "SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_schema = 'foodrating' AND table_name = 'utilisateurs'";
        $manager = $this->getDoctrine()->getManager();
        $conn = $manager->getConnection();
        return $conn->query($query)->fetchAll();
    }


}
