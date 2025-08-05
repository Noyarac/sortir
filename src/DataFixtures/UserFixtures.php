<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    const USER_QUANTITY = 10;

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher){}

    public function load(ObjectManager $manager): void
    {
        $campusRepository = $manager->getRepository(Campus::class);
        $allCampuses = $campusRepository->findAll();

        $faker = \Faker\Factory::create('fr_FR');
        $admin = new User();
        $prenom = $faker->firstName;
        $nom = $faker->lastName;
        $admin->setNom($nom);
        $admin->setPrenom($prenom);
        $admin->setEmail('admin@eni.fr');
        $admin->setPseudo('admin-'.strtolower($prenom));
        $password = $this->passwordHasher->hashPassword($admin, '123456');
        $admin->setPassword($password);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCampus($faker->randomElement($allCampuses));
        $manager->persist($admin);

        //création d'utilisateur avec le rôle USER
        for ($i = 1; $i <= self::USER_QUANTITY; $i++) {
            $user = new User();
            $prenom = $faker->firstName;
            $nom = $faker->lastName;
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail(strtolower($prenom).'.'.strtolower($nom).'@eni.fr');
            $user->setPseudo(strtolower($prenom).' - '.strtolower($nom));
            $password = $this->passwordHasher->hashPassword($user, '123456');
            $user->setPassword($password);
            $user->setCampus($faker->randomElement($allCampuses));
            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }

}
