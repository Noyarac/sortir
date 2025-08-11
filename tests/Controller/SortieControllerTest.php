<?php

namespace App\Tests\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SortieControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function test_creationSortie_FormulaireValideBouton():void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        //simuler la connexion d'un utilisateur
        $user = $entityManager->getRepository(User::class)->findOneBy([]);
        $lieu = $entityManager->getRepository(Lieu::class)->findOneBy([]);
        $this->client->loginUser($user);
        //accéder à la page de création GET
        $this->client->request('GET', '/sortie/creation');
        $this->client->submitForm('Enregistrer', [
            'sortie[nom]' => 'Balade dans le parc',
            'sortie[dateHeureDebut]' => (new \DateTimeImmutable('now'))->modify('+1 month')->format('Y-m-d\TH:i'),
            'sortie[dateLimiteInscription]' => (new \DateTimeImmutable('now'))->modify('+1 month')->modify('-2 days')->format('Y-m-d\TH:i'),
            'sortie[nbInscriptionMax]' => '15',
            'sortie[duree]' => '60',
            'sortie[lieu]' => $lieu->getId(),
            'sortie[infosSortie]' => "Balade dans le parc",
        ]);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertRouteSame('main_home');
    }

    public function test_affichage_details(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        //simuler la connexion d'un utilisateur
        $user = $entityManager->getRepository(User::class)->findOneBy([]);
        $sortie = $entityManager->getRepository(Sortie::class)->findOneBy(["etat" => Etat::OUVERTE->value]);
        $this->client->loginUser($user);
        $this->client->request('GET', '/sortie/' . $sortie->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Afficher une sortie');
        $this->assertSelectorTextContains('dd', $sortie->getNom());
    }

}
