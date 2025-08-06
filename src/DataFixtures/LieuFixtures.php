<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $villeRepository = $manager->getRepository(Ville::class);
        $villes = $villeRepository->findAll();

        $emplacementsLieu = ['Quartier', 'Place', 'Parc'];
        $nomEmplacements = ['de la République', 'du Marché', 'des Fleurs', 'du Château', 'des Tilleuls', 'de la liberté'];

        for ($i = 1; $i <= 20; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->randomElement($emplacementsLieu)." ".$faker->randomElement($nomEmplacements));
            $lieu->setRue($faker->streetName());
            $lieu->setVille($faker->randomElement($villes));
            $lieu->setLatitude($faker->latitude());
            $lieu->setLongitude($faker->longitude());
            $this->addReference('lieu'.$i, $lieu);
            $manager->persist($lieu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [VilleFixtures::class];
    }
}
