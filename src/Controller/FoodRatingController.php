<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Inscription;
use App\Repository\InscriptionRepository;
use App\Form\InscriptionType;

class FoodRatingController extends AbstractController
{
    /**
     * @Route("/food_rating", name="food_rating")
     */

    public function home() {
        return $this->render('food_rating/accueil.html.twig');
    }

    /**
     * @Route("/food_rating/inscription", name="inscription")
     */

    public function formInscription(Inscription $inscription = null, Request $request) {
        
        if(!$inscription) {
            $inscription = new Inscription();
        }

        $manager = $this->getDoctrine()->getManager();
        $form = $this->createForm(InscriptionType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form['mdp']->getData() == $form['mdp_confirmation']->getData()) {
                $manager->persist($inscription);
                $manager->flush();

                return $this->redirectToRoute('food_rating');
            }
        }
        return $this->render('food_rating/inscription.html.twig', [
            'formInscription' => $form->createView()
        ]);

    }
}

?>
