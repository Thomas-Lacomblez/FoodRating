<?php

namespace App\DataFixtures;

use League\Csv\Reader;
use App\Entity\Categories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixturesCategorie extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $stream = fopen('%kernel.root_dir%/../public/csv/cat_aa.csv', 'r');
		$csv = Reader::createFromStream($stream);
		$csv->setDelimiter(';');
		$csv->setHeaderOffset(0);
		$records = $csv->getRecords();

		foreach($records as $row) {
            $categorie = New Categories();
            $categorie->setId($row['id'])
                      ->setKnown($row['known'])
                      ->setName($row['name'])
                      ->setProducts($row['products'])
                      ->setUrl($row['url'])
                      ->setSameAs($row['sameAs']);
            $manager->persist($categorie);
        }
        $manager->flush();
    }
}