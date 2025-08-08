<?php

namespace App\Tests\Unit\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use App\Service\SortieService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SortieServiceTest extends KernelTestCase
{

    public function testInscrireUnParticipant(): void
    {
        self::bootKernel();
        $service = static::getContainer()->get(SortieService::class);

        $emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        $service = new SortieService($emMock);


        $sortie = new Sortie();
        $sortie->setEtat(Etat::OUVERTE->value);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie->setNom("Sortie");
        $sortie->setNbInscriptionMax(2);

        $user1 = new User();
        $user1->setNom("User1");

        // First inscription
        $result1 = $service->inscription($sortie, $user1);
        $this->assertTrue($result1);
        $this->assertContains($user1, $sortie->getParticipants());
    }

    public function testInscriptionAddsParticipantAndClosesWhenFull(): void
    {
        self::bootKernel();
        $service = static::getContainer()->get(SortieService::class);

        $sortie = new Sortie();
        $sortie->setEtat(Etat::OUVERTE->value);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie->setNom("Sortie");
        $sortie->setNbInscriptionMax(2);

        $user1 = new User();
        $user1->setNom("User1");
        $user2 = new User();
        $user2->setNom("User2");

        // First inscription
        $result1 = $service->inscription($sortie, $user1);
        $this->assertTrue($result1);
        $this->assertContains($user1, $sortie->getParticipants());

        // Second inscription, should reach max
        $result2 = $service->inscription($sortie, $user2);
        $this->assertTrue($result2);
        $this->assertContains($user2, $sortie->getParticipants());
        $this->assertEquals(2, count($sortie->getParticipants()));
        $this->assertEquals(\App\Entity\Etat::CLOTUREE->value, $sortie->getEtat());
    }
}
