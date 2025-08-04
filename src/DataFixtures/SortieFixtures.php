<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create("fr_FR");

        for ($i = 0; $i < 30; $i++) {
            $sortie = new Sortie;
            $sortie->setNom($faker->sentence());
            $dateHeureDebut = $faker->dateTimeBetween("-1 year");;
            $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
            $sortie->setDuree($faker->numberBetween(15, 3 * 24 * 60));
            $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P1D")));
            $sortie->setNbInscriptionMax($faker->optional(90)->numberBetween(5, 30));
            $sortie->setInfosSortie($faker->sentence());
            $sortie->setEtat("EC");
            $manager->persist($sortie);
        }
        $manager->flush();
    }
}
