<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Form\InscriptionType;
use App\Form\DonneesModifType;
use App\Repository\UtilisateursRepository;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
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
     * @Route("/inscription", name="inscription")
     */

    public function formInscription(Request $request, UserPasswordEncoderInterface $encoder) {

        $user = new Utilisateurs();
        $manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(InscriptionType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('food_rating');

        }
        return $this->render('security/inscription.html.twig', [
            'formInscription' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/info_compte/modifier_donnees", name="modifier_donnees")
     */

    public function formModifierDonnees(Request $request, UserPasswordEncoderInterface $encoder) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $manager = $this->getDoctrine()->getManager();
        $userModif = $manager->getRepository(Utilisateurs::class)->find($user->getId());

        $form = $this->createForm(DonneesModifType::class, $userModif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($userModif, $userModif->getPassword());

            $user->setPassword($hash);
            $manager->flush();
            return $this->redirectToRoute('espace');

        }
        return $this->render('security/modifier_donnees.html.twig', [
            'formModifierDonnees' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/info_compte/modifier_donnees/desinscription", name="desinscription")
     */

     public function desinscription() {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $manager = $this->getDoctrine()->getManager();
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
