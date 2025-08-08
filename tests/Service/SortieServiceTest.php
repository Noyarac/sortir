<?php

namespace App\Tests\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SortieServiceTest extends KernelTestCase
{

    public function testInscrireUnParticipant(): void
    {
        $emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $service = new SortieService($emMock);

        $sortie = new Sortie();
        $sortie->setEtat(Etat::OUVERTE->value);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie->setNom("Sortie");
        $sortie->setNbInscriptionMax(2);

        $user1 = new User();
        $user1->setNom("User1");

        $result1 = $service->inscription($sortie, $user1);
        $this->assertTrue($result1);
        $this->assertContains($user1, $sortie->getParticipants());
    }

    public function test_inscrit_dernier_participant_et_cloture(): void
    {
        $emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $service = new SortieService($emMock);

        $sortie = new Sortie();
        $sortie->setEtat(Etat::OUVERTE->value);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie->setNom("Sortie");
        $sortie->setNbInscriptionMax(2);

        $user1 = new User();
        $user1->setNom("User1");
        $user2 = new User();
        $user2->setNom("User2");

        $service->inscription($sortie, $user1);
        $result = $service->inscription($sortie, $user2);
        $this->assertTrue($result);
        $this->assertContains($user2, $sortie->getParticipants());
        $this->assertEquals(2, count($sortie->getParticipants()));
        $this->assertEquals(\App\Entity\Etat::CLOTUREE->value, $sortie->getEtat());
    }

        public function testUnParticipantSeDesiste(): void
    {
        $emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $service = new SortieService($emMock);

        $sortie = new Sortie();
        $sortie->setEtat(Etat::OUVERTE->value);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie->setNom("Sortie");
        $sortie->setNbInscriptionMax(2);
        $user1 = new User();
        $user1->setNom("User1");
        $sortie->addParticipant($user1);

        $result = $service->desistement($sortie, $user1);
        $this->assertTrue($result);
        $this->assertNotContains($user1, $sortie->getParticipants());
    }

        public function testUnParticipantSeDesisteEtReouvreLaSortie(): void
    {
        $emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $service = new SortieService($emMock);

        $sortie = new Sortie();
        $sortie->setEtat(Etat::CLOTUREE->value);
        $sortie->setNbInscriptionMax(3);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie->setNom("SortieTest");
        $user1 = new User();
        $user1->setNom("User1");
        $sortie->addParticipant($user1);
        $user2 = new User();
        $user2->setNom("User2");
        $sortie->addParticipant($user2);
        $user3 = new User();
        $user3->setNom("User3");
        $sortie->addParticipant($user3);

        $result = $service->desistement($sortie, $user3);
        $this->assertTrue($result);
        $this->assertNotContains($user3, $sortie->getParticipants());
        $this->assertEquals(Etat::OUVERTE->value, $sortie->getEtat());
    }

        public function testUnParticipantSeDesisteEtNeReouvrePasLaSortie(): void
    {
        $emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $service = new SortieService($emMock);

        $sortie = new Sortie();
        $sortie->setEtat(Etat::CLOTUREE->value);
        $sortie->setNbInscriptionMax(3);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("-1 week"));
        $sortie->setNom("Sortie");
        $sortie->setNbInscriptionMax(2);
        $user1 = new User();
        $user1->setNom("User1");
        $sortie->addParticipant($user1);
        $user2 = new User();
        $user2->setNom("User2");
        $sortie->addParticipant($user2);
        $user3 = new User();
        $user3->setNom("User3");
        $sortie->addParticipant($user3);

        $result = $service->desistement($sortie, $user3);
        $this->assertTrue($result);
        $this->assertNotContains($user3, $sortie->getParticipants());
        $this->assertEquals(Etat::CLOTUREE->value, $sortie->getEtat());
    }

        public function test_On_ne_peut_pas_desister_un_utilisateur_qui_ne_s_est_pas_inscrit(): void
    {
        $emMock = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $service = new SortieService($emMock);

        $sortie = new Sortie();
        $sortie->setEtat(Etat::OUVERTE->value);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie->setNom("Sortie");
        $sortie->setNbInscriptionMax(2);
        $user1 = new User();
        $user1->setNom("User1");

        $result = $service->desistement($sortie, $user1);
        $this->assertFalse($result);
        $this->assertNotContains($user1, $sortie->getParticipants());
    }

    public function test_mettreAJourSortiesHistorisees_Succes() : void
    {
        $sortie1 = $this->createMock(Sortie::class);
        $sortie1->expects($this->once())->method('setEtat')->with(Etat::HISTORISEE->value);

        $sortie2 = $this->createMock(Sortie::class);
        $sortie2->expects($this->once())->method('setEtat')->with(Etat::HISTORISEE->value);

        $repository = $this->createMock(SortieRepository::class);
        $repository->expects($this->once())
            ->method('findSortiesTermineesDepuisPlusDeNbMois')
            ->with(1)
            ->willReturn([$sortie1, $sortie2]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('getRepository')->with(Sortie::class)->willReturn($repository);
        $entityManager->expects($this->once())->method('flush');

        $service = new SortieService($entityManager);

        $result = $service->mettreAJourSortiesHistorisees();

        $this->assertSame(2, $result);
    }

}
