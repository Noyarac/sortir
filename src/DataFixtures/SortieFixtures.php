<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create("fr_FR");
        $allCampuses = $manager->getRepository(Campus::class)->findAll();
        $allUsers = $manager->getRepository(User::class)->findAll();

        for ($i = 0; $i < 100; $i++) {
            $sortie = new Sortie;
            $sortie->setNom($faker->sentence());
            $dateHeureDebut = $faker->dateTimeBetween("-1 year");;
            $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
            $sortie->setDuree($faker->numberBetween(15, 3 * 24 * 60));
            $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P1D")));
            $sortie->setNbInscriptionMax($faker->optional(90)->numberBetween(5, 30));
            $sortie->setInfosSortie($faker->sentence());
            $sortie->setEtat($faker->randomElement(Etat::values()));
            $sortie->setCampus($faker->randomElement($allCampuses));
            $sortie->setOrganisateur($faker->randomElement($allUsers));
            for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                if (rand(0, 100) < 8) $sortie->addParticipant($faker->randomElement($allUsers));
            }
            $manager->persist($sortie);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
            UserFixtures::class,
        ];
    }
}
