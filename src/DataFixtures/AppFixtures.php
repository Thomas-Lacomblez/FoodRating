<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Produit;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        
    	for ($i = 0; $i < 10; $i++) {
    		$produit = new Produit();
    		
    		$produit->setNom("Produit n°$i")
    				->setMarque("Une marque")
    				->setCode("$i$i$i$i$i$i$i")
    				->setImage("http://placehold.it/250x125")
    				->setIngredient("Bcp de bonnes choses !")
    				->setCategorie("Cat $i")
    				->setDistributeur([
    						"Distributeur 1",
    						"Distributeur 2",
    						"Distributeur 3"
    				])
    				->setKcal($i * 100)
    				->setNutriscore("c")
    				->setTrace("trace 1, trace 2, trace 3");
    		
    		$manager->persist($produit);
    		
    	}

        $manager->flush();
    }
}
