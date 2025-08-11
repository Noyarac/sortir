<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilControllerTest extends WebTestCase
{
    private $users;
    private $user1;
    private $user2;
    private $entityManager;
    protected function setUp() : void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        //simuler la connexion d'un utilisateur qui peut accéder à la page de modification de son profil
        $this->users = $this->entityManager->getRepository(User::class)->findBy([], null, 2);
        $this->user1 = $this->users[0];
        $this->user2 = $this->users[1];
        $this->client->loginUser($this->user1);
    }
    public function test_modifierProfil_FormulaireValide_Succes(): void
    {
        $this->client->request('GET', '/profil/modifier');
        //On vérifie que l'utilisateur connecté peut accéder à la page de modification de profil
        $this->assertResponseIsSuccessful();

        //Nouvelles données qui vont être insérées dans le formulaire
        $password = 'Mdp*123456789';
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $userId = $this->user1->getId();
        $prenom= 'Alexandre';
        $pseudo= 'TestUser'.uniqid();
        $nom = 'Dumard';
        $telephone = '0601020304';
        $email = 'unique123.dumard@eni.fr';
        //l'utilisateur soumet le formulaire avec des données valides
        $this->client->submitForm('Enregistrer', [
            'user[pseudo]' => $pseudo,
            'user[prenom]' => $prenom,
            'user[nom]' => $nom,
            'user[telephone]' => $telephone,
            'user[email]' => $email,
            'user[plainPassword][first]' => $password,
            'user[plainPassword][second]' => $password,
        ]);
        //Formulaire valide - redirection vers 'main_home'
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertRouteSame('main_home');

        //Vérification des données enregistrées en BDD qui doivent correspondre aux données ci-dessus
        $this->entityManager->clear();
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        $this->assertSelectorTextContains('.alert-success',
            "Profil mis à jour avec succès");
        $this->assertSame($pseudo, $user->getPseudo());
        $this->assertSame($prenom, $user->getPrenom());
        $this->assertSame($nom, $user->getNom());
        $this->assertSame($telephone, $user->getTelephone());
        $this->assertSame($email, $user->getEmail());
        $this->assertTrue($passwordHasher->isPasswordValid($user, $password));

    }

    public function test_modifierProfil_MotsDePasseDifferent_Erreur() : void
    {
        //L'utilisateur connecté accède à la page de modification de profil
        $this->client->request('GET', '/profil/modifier');

        //l'utilisateur soumet le formulaire avec un mot de passe et sa confirmation différente
        $this->client->submitForm('Enregistrer', [
            'user[pseudo]' => $this->user1->getPseudo(),
            'user[prenom]' => $this->user1->getPrenom(),
            'user[nom]' => $this->user1->getNom(),
            'user[telephone]' => $this->user1->getTelephone(),
            'user[email]' => $this->user1->getEmail(),
            'user[plainPassword][first]' => 'Mdp*1234567',
            'user[plainPassword][second]' => 'Mdp*123456789',
        ]);

        //Formulaire non valide - erreur 422 et on reste
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.invalid-feedback', "Les mots de passe doivent correspondre.");
    }

    public function test_modifierProfil_PseudoNonUnique_Erreur() : void
    {
        //L'utilisateur connecté accède à la page de modification de profil
        $this->client->request('GET', '/profil/modifier');


        //l'utilisateur soumet le formulaire avec un pseudo non unique (celui de $user2)
        $this->client->submitForm('Enregistrer', [
            'user[pseudo]' => $this->user2->getPseudo(),
            'user[prenom]' => $this->user1->getPrenom(),
            'user[nom]' => $this->user1->getNom(),
            'user[telephone]' => $this->user1->getTelephone(),
            'user[email]' => $this->user1->getEmail(),
        ]);

        //Formulaire non valide - erreur 422 et on reste
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.invalid-feedback', "Ce pseudo est déjà utilisé");
    }

    public function test_modifierProfil_EmailNonUnique_Erreur() : void
    {
        //L'utilisateur connecté accède à la page de modification de profil
        $this->client->request('GET', '/profil/modifier');


        //l'utilisateur soumet le formulaire avec un email non unique (celui de $user2)
        $this->client->submitForm('Enregistrer', [
            'user[pseudo]' => $this->user1->getPseudo(),
            'user[prenom]' => $this->user1->getPrenom(),
            'user[nom]' => $this->user1->getNom(),
            'user[telephone]' => $this->user1->getTelephone(),
            'user[email]' => $this->user2->getEmail(),
        ]);

        //Formulaire non valide - erreur 422 et on reste
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.invalid-feedback', "Cette adresse mail est déjà utilisée");
    }
}
