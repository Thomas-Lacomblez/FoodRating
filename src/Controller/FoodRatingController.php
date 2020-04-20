<?php

namespace App\Controller;

use App\Repository\UtilisateursRepository;
use App\Entity\Utilisateurs;

use Symfony\Component\Routing\Annotation\Route;
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
