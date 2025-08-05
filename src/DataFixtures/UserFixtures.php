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
        $admin->setPseudo('admin'.ucfirst($prenom));
        $password = $this->passwordHasher->hashPassword($admin, 'Mdp*123456');
        $admin->setPassword($password);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCampus($faker->randomElement($allCampuses));
        $manager->persist($admin);

        //création d'utilisateur avec le rôle USER
        for ($i = 1; $i <= self::USER_QUANTITY; $i++) {
            $user = new User();
            $prenom = $faker->firstName;
            $nom = $faker->lastName;
            //Suppression des accents et mise en minuscules pour le main
            $prenomClean = strtolower((string)iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $prenom));
            $nomClean = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nom));
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($prenomClean.'.'.$nomClean.'@eni.fr');
            $user->setPseudo(strtolower($prenom).ucfirst($nom));
            $numeroTelephone = $faker->optional(80)->numerify('06########');
            if($numeroTelephone){
                $user->setTelephone($numeroTelephone);
            }
            $password = $this->passwordHasher->hashPassword($user, 'Mdp*123456');
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
