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

        $emplacementsLieu = ['Quartier', 'Place', 'Parc', 'Jardin', 'Promenade', 'Esplanade', 'Sentier', 'Square'];
        $nomEmplacements = ['de la République', 'du Marché', 'des Fleurs', 'du Château', 'des Tilleuls', 'de la Liberté', 'du lac', 'de la plage', 'du moulin'];

        $nomsLieux = [];

        for ($i = 1; $i <= 20; $i++) {
            do{
                $nom = $faker->randomElement($emplacementsLieu)." ".$faker->randomElement($nomEmplacements);
            } while (in_array($nom, $nomsLieux));

            $nomsLieux[] = $nom;

            $lieu = new Lieu();
            $lieu->setNom($nom);
            $lieu->setRue(ucfirst($faker->streetName()));
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
