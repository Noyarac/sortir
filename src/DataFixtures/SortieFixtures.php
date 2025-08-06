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
    const QTE_SORTIE = 100;
    const REPARTITION = [
        Etat::EN_CREATION->value => 1,
        Etat::OUVERTE->value => 3,
        Etat::CLOTUREE->value => 1,
        Etat::EN_COURS->value => 1,
        Etat::TERMINEE->value => 5,
        Etat::ANNULEE->value => 1,
        Etat::HISTORISEE->value => 10
    ];

    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create("fr_FR");
        $allCampuses = $manager->getRepository(Campus::class)->findAll();
        $allUsers = $manager->getRepository(User::class)->findAll();
        $allSorties = [];

        for ($i = 0; $i < self::QTE_SORTIE; $i++) {
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
            $random = rand(0, array_sum(self::REPARTITION));
            switch (true) {
                case $random < self::seuil(Etat::EN_CREATION):
                    $sortie->setEtat(Etat::EN_CREATION->value);
                    $dateHeureDebut = $faker->dateTimeBetween("1 week", "1 year");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P1D")));
                    break;
                case $random < self::seuil(Etat::OUVERTE):
                    $sortie->setEtat(Etat::OUVERTE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("1 week", "1 year");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P1D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 12) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < self::seuil(Etat::CLOTUREE):
                    if (rand(0, 1)) {
                        $sortie->setEtat(Etat::CLOTUREE->value);
                        $dateHeureDebut = $faker->dateTimeBetween("5 day", "1 month");;
                        $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                        $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P1D")));
                        for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                            $sortie->addParticipant($faker->randomElement($allUsers));
                        }
                        break;
                    }
                    $sortie->setEtat(Etat::CLOTUREE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("5 day", "1 month");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription((new DateTimeImmutable())->sub(new DateInterval("P". rand(1, 8) ."D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 85) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < self::seuil(Etat::EN_COURS):
                    $sortie->setEtat(Etat::EN_COURS->value);
                    $dateHeureDebut = $faker->dateTimeBetween("-2 day", "now");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription((new DateTimeImmutable())->sub(new DateInterval("P". rand(1, 8) ."D")));
                    $sortie->setDuree(60 * 24 * 3);
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 85) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < self::seuil(Etat::TERMINEE):
                    $sortie->setEtat(Etat::TERMINEE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("-20 days", "-1 day");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P". rand(1, 8) ."D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 85) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                case $random < self::seuil(Etat::ANNULEE):
                    $sortie->setEtat(Etat::ANNULEE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("-10 days", "10 day");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P". rand(1, 8) ."D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 30) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
                default:
                    $sortie->setEtat(Etat::HISTORISEE->value);
                    $dateHeureDebut = $faker->dateTimeBetween("-3 months", "-1 month");;
                    $sortie->setDateHeureDebut(DateTimeImmutable::createFromMutable($dateHeureDebut));
                    $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new DateInterval("P". rand(1, 8) ."D")));
                    for ($j = 0; $j < $sortie->getNbInscriptionMax(); $j++) {
                        if (rand(0, 100) < 80) $sortie->addParticipant($faker->randomElement($allUsers));
                    }
                    break;
            }
            $manager->persist($sortie);
        }
        $manager->flush();
    }

    private static function seuil(Etat $etat) : int {
        $seuil = 0;
        foreach (self::REPARTITION as $key => $value) {
            $seuil += $value;
            if ($key == $etat->value) break;
        }
        return $seuil;
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
