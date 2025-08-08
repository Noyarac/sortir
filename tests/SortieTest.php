<?php

namespace App\Tests;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SortieTest extends TestCase
{
    public function test_mettreAJourSortiesHistorisees_Succes() : void
    {
        $sortie1 = $this->createMock(Sortie::class);
        $sortie1->expects($this->once())->method('setEtat')->with(Etat::HISTORISEE->value);

        $sortie2 = $this->createMock(Sortie::class);
        $sortie2->expects($this->once())->method('setEtat')->with(Etat::HISTORISEE->value);

        $repository = $this->createMock(SortieRepository::class);
        $repository->expects($this->once())
            ->method('findSortiesTermineesDepuisPlusDUnMois')
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
