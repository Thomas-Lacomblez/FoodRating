<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FoodRatingController extends AbstractController
{
    /**
     * @Route("/", name="food_rating")
     */

    public function home() {
        return $this->render('food_rating/accueil.html.twig');
    }

}

?>
