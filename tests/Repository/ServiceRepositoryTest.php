<?php

namespace App\Tests\Repository;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServiceRepositoryTest extends KernelTestCase
{
    public function test_findSortiesTermineesDepuisPlusDeNbMois(): void
    {
        $kernel = self::bootKernel();
        $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $campus = $entityManager->getRepository(Campus::class)->find(1);
        $lieu = $entityManager->getRepository(Lieu::class)->find(1);
        $user = $entityManager->getRepository(User::class)->find(1);

        $sortieHistorisable = new Sortie();
        $sortieHistorisable->setNom('Sortie Historisable');
        $sortieHistorisable->setDateHeureDebut((new \DateTimeImmutable())->modify('-2 days')->modify('-1 month'));
        $sortieHistorisable->setDateLimiteInscription((new \DateTimeImmutable())->modify('-4 days')->modify('-1 month'));
        $sortieHistorisable->setNbInscriptionMax(15);
        $sortieHistorisable->setDuree(60);
        $sortieHistorisable->setInfosSortie('Cette sortie est historisable');
        $sortieHistorisable->setCampus($campus);
        $sortieHistorisable->setLieu($lieu);
        $sortieHistorisable->setOrganisateur($user);
        $sortieHistorisable->setEtat(Etat::TERMINEE->value);
        $entityManager->persist($sortieHistorisable);

        $sortieNonHistorisable = new Sortie();
        $sortieNonHistorisable->setNom('Sortie Non Historisable');
        $sortieNonHistorisable->setDateHeureDebut((new \DateTimeImmutable())->modify('-2 days'));
        $sortieNonHistorisable->setDateLimiteInscription((new \DateTimeImmutable())->modify('-4 days'));
        $sortieNonHistorisable->setNbInscriptionMax(15);
        $sortieNonHistorisable->setDuree(60);
        $sortieNonHistorisable->setInfosSortie('Cette sortie est non historisable');
        $sortieNonHistorisable->setCampus($campus);
        $sortieNonHistorisable->setLieu($lieu);
        $sortieNonHistorisable->setOrganisateur($user);
        $sortieNonHistorisable->setEtat(Etat::TERMINEE->value);
        $entityManager->persist($sortieNonHistorisable);

        $entityManager->flush();

        $results = $sortieRepository->findSortiesTermineesDepuisPlusDeNbMois(1);

        $this->assertCount(1, $results);
        $this->assertSame($sortieHistorisable->getId(), $results[0]->getId());

        $entityManager->remove($sortieNonHistorisable);
        $entityManager->remove($sortieHistorisable);
        $entityManager->flush();

    }
}
