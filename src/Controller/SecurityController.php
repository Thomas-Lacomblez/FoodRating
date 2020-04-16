<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Form\InscriptionType;
use App\Repository\UtilisateursRepository;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
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
     * @Route("/compte", name="espace")
     */

     public function espaceClient(Request $request, UserPasswordEncoderInterface $encoder) {

        /*$manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(DonneesModifType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();

        }*/
         return $this->render('security/espace.html.twig');
     }

    /**
     * @Route("/deconnexion", name="logout_security")
     */

    public function logout() {}
}
