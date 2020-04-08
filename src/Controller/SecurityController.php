<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Form\InscriptionType;
use App\Repository\UtilisateursRepository;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/mon_compte", name="mon_compte")
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

        }
        return $this->render('security/mon_compte.html.twig', [
            'formInscription' => $form->createView()
        ]);
    }

    /**
     * @Route("/mon_compte/connexion", name="login_security")
     */
    public function connexion() {
        return $this->render('security/mon_compte.html.twig');
    }
    /**
     * @Route("/deconnexion", name="logout_security")
     */
    public function logout() {}
}
