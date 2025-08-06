<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
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
        $allSorties = [];

        for ($i = 0; $i < 100; $i++) {
            $sortie = new Sortie;
            $sortie->setNom($faker->catchPhrase());
            $sortie->setDuree($faker->numberBetween(15, 3 * 24 * 60));
            $sortie->setInfosSortie($faker->sentence(20, true));
            $sortie->setCampus($faker->randomElement($allCampuses));
            $sortie->setOrganisateur($faker->randomElement($allUsers));
            $sortie->setLieu($this->getReference('lieu'.$faker->numberBetween(1,20), Lieu::class));
            $sortie->setNbInscriptionMax(rand(3, 100));
            $allSorties[] = $sortie;
        }
        foreach ($allSorties as $sortie) {
            $random = rand(0,100);
            switch (true) {
                case $random < 15:
                    $sortie->setEtat(Etat::EN_CREATION->value);
                    $dateHeureDebut = $faker->dateTimeBetween("1 week", "1 year");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P1D")));
                    break;
                case $random < 30:
                    $sortie->setEtat(Etat::OUVERTE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("1 week", "1 year");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P1D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 12) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < 45:
                    $sortie->setEtat(Etat::CLOTUREE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("5 day", "1 month");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P1D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < 60:
                    $sortie->setEtat(Etat::CLOTUREE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("5 day", "1 month");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription((new DateTimeImmutable())->sub(new DateInterval("P". rand(1, 8) ."D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 85) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < 70:
                    $sortie->setEtat(Etat::EN_COURS->value);
                    $dateHeureDebut = $faker->dateTimeBetween("-2 day", "now");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription((new DateTimeImmutable())->sub(new DateInterval("P". rand(1, 8) ."D")));
                    $sortie->setDuree(60 * 24 * 3);
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 85) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < 80:
                    $sortie->setEtat(Etat::TERMINEE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("-20 days", "-1 day");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription((new DateTimeImmutable())->sub(new DateInterval("P". rand(1, 8) ."D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 85) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < 85:
                    $sortie->setEtat(Etat::ANNULEE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("-10 days", "10 day");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription((new DateTimeImmutable())->sub(new DateInterval("P". rand(1, 8) ."D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 30) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                default:
                    $sortie->setEtat(Etat::HISTORISEE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("-3 months", "-1 month");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription((new DateTimeImmutable())->sub(new DateInterval("P". rand(1, 8) ."D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 80) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
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
            LieuFixtures::class
        ];
    }
}
