<?php

namespace App\DataFixtures;

use League\Csv\Reader;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
ini_set('memory_limit', '-1');
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
		$stream = fopen('%kernel.root_dir%/../public/csv/fr.openfoodfacts.org.products_0.csv', 'r');
		$csv = Reader::createFromStream($stream);
		$csv->setDelimiter('	');
		$csv->setHeaderOffset(0);
		$records = $csv->getRecords();
		//$resultat = $reader->fetchAssoc();

		foreach($records as $row) {
			$produit = new Produit();

			$produit->setNom($row['product_name'])
					->setMarque($row['brands'])
					->setCode($row['code'])
					->setImage($row['image_url'])
					->setIngredient($row['ingredients_text'])
					->setCategorie($row['categories'])
					->setDistributeur($row['stores'])
					->setKcal($row['energy-kcal_100g'])
					->setNutriscore($row['nutriscore_grade'])
					->setTrace($row['traces'])
					->setEtiquette($row['labels'])
					->setAdditif($row['additives']);

			$manager->persist($produit);
		}
		$manager->flush();
    }
}

?>

