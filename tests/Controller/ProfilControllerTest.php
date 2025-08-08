<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilControllerTest extends WebTestCase
{
    public function test_modifierProfil_FormulaireValide_Succes(): void
    {
        $client = static::createClient();
        $faker = \Faker\Factory::create('fr_FR');
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        //simuler la connexion d'un utilisateur
        $user = $entityManager->getRepository(User::class)->find(3);
        $client->loginUser($user);
        $client->request('GET', '/profil/modifier');
        $this->assertResponseIsSuccessful();
        $password = 'Mdp*123456789';
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $prenom= $faker->firstName;
        $pseudo= $prenom.' unique';
        $nom = $faker->lastName;
        $telephone = $faker->numerify('06########');
        $email = 'unique-'.$faker->email;
        $client->submitForm('Enregistrer', [
            'user[pseudo]' => $pseudo,
            'user[prenom]' => $prenom,
            'user[nom]' => $nom,
            'user[telephone]' => $telephone,
            'user[email]' => $email,
            'user[plainPassword][first]' => $password,
            'user[plainPassword][second]' => $password,
        ]);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertRouteSame('main_home');
        $entityManager->clear();
        $user = $entityManager->getRepository(User::class)->find(3);
        $this->assertSelectorTextContains('.alert-success',
            "Profil mis à jour avec succès");
        $this->assertSame($pseudo, $user->getPseudo());
        $this->assertSame($prenom, $user->getPrenom());
        $this->assertSame($nom, $user->getNom());
        $this->assertSame($telephone, $user->getTelephone());
        $this->assertSame($email, $user->getEmail());
        $this->assertTrue($passwordHasher->isPasswordValid($user, $password));

    }
}
