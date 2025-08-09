<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function test_home_UserConnectePeutAccederAlaPage(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        //simuler la connexion d'un utilisateur
        $user = $entityManager->getRepository(User::class)->findOneBy([]);
        $this->client->loginUser($user);
        //accéder à la page de création GET
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        // Vérifie que la route appelée est bien la route nommée 'main_home'
        $this->assertSame('main_home', $this->client->getRequest()->attributes->get('_route'));

        // Vérifie la présence d’un élément dans la page (ex : un h1 avec texte "Bienvenue")
        $this->assertSelectorExists('div#filtres');
    }

    public function test_home_UserNonConnecteRedirigerVersLogin(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();
        $this->assertRouteSame('app_login');
        $this->assertSelectorTextContains('h1', 'Se connecter');
    }
}
