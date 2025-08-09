<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function test_login_WithEmail() : void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        //simuler la connexion d'un utilisateur
        $user = $entityManager->getRepository(User::class)->findOneBy([]);

        $this->client->request('GET', '/');
        $this->client->followRedirect();
        $this->client->submitForm('Se connecter', [
            '_username' => $user->getEmail(),
            '_password' => 'Mdp*123456',
            ]);

        $this->assertResponseRedirects('/');
    }

    public function test_login_WithPseudo() : void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        //simuler la connexion d'un utilisateur
        $user = $entityManager->getRepository(User::class)->findOneBy([]);

        $this->client->request('GET', '/');
        $this->client->followRedirect();
        $this->client->submitForm('Se connecter', [
            '_username' => $user->getPseudo(),
            '_password' => 'Mdp*123456',
        ]);

        $this->assertResponseRedirects('/');
    }

    public function test_login_compteInactif_ConnexionRefusée() : void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        //simuler la connexion d'un utilisateur
        $user = $entityManager->getRepository(User::class)->findOneBy([]);
        $user->setActif(false);
        $entityManager->flush();

        $this->client->request('GET', '/');
        $this->client->followRedirect();
        $this->client->submitForm('Se connecter', [
            '_username' => $user->getPseudo(),
            '_password' => 'Mdp*123456',
        ]);
        // Après soumission, Symfony redirige vers /login (échec)
        $this->assertResponseStatusCodeSame(302);
        // Suivre la redirection (vers /login) pour voir la page d'erreur
        $crawler = $this->client->followRedirect();

        // Maintenant on est sur la page de login (200 OK)
        $this->assertResponseIsSuccessful();

        // On vérifie la présence du message d’erreur
        $this->assertSelectorTextContains('.alert-danger', "Votre compte a été désactivé, veuillez contacter l'administrateur");

        // Vérifier qu'il n'y a pas de lien logout (pas connecté)
        $this->assertSelectorNotExists('a.nav-link[href="/logout"]');
    }

}
