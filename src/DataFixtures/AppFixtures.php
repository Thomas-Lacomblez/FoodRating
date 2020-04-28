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

		foreach($records as $row) {
			$produit = new Produit();

			$produit->setCode($row['code']);

			if (empty($row['product_name'])) {
				$produit->setNom("Nom du produit non renseigné");
			}
			else {
				$produit->setNom($row['product_name']);
			}

			if (empty($row['brands'])) {
				$produit->setMarque("Marque non renseigné");
			}
			else {
				$produit->setMarque($row['brands']);
			}

			if (empty($row['image_url'])) {
				$produit->setImage("Image non disponible pour ce produit");
			}
			else {
				$produit->setImage($row['image_url']);
			}

			if (empty($row['ingredients_text'])) {
				$produit->setIngredient("Ingrédients non renseignés");
			}
			else {
				$produit->setIngredient($row['ingredients_text']);
			}

			if (empty($row['stores'])) {
				$produit->setDistributeur("Distributeurs non renseignés");
			}
			else {
				$produit->setDistributeur($row['stores']);
			}

			if (empty($row['energy-kcal_100g'])) {
				$produit->setKcal("Kcal non renseignée");
			}
			else {
				$produit->setKcal($row['energy-kcal_100g']);
			}

			if (empty($row['nutriscore_grade'])) {
				$produit->setNutriscore("-");
			}
			else {
				$produit->setNutriscore($row['nutriscore_grade']);
			}

			if (empty($row['traces'])) {
				$produit->setTrace("Non renseignée");
			}
			else {
				$produit->setTrace($row['traces']);
			}

			if (empty($row['labels'])) {
				$produit->setEtiquette("Etiquette non renseignée");
			}
			else {
				$produit->setEtiquette($row['labels']);
			}

			if (empty($row['additives'])) {
				$produit->setAdditif("Additifs non renseignés");
			}
			else {
				$produit->setAdditif($row['additives']);
			}

			if (empty($row['categories'])) {
				$produit->setCategorie("Non catégorisé");
			}
			else {
				$produit->setCategorie($row['categories']);
			}

			$manager->persist($produit);
		}
		$manager->flush();
    }
}

?>

