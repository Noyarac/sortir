<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Etat;
use App\Entity\Sortie;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SortieTest extends TestCase
{
    public function testCorrectionDeLEtatOuverteEnClotureeSiLaDateLimiteEstDepassee(): void
    {
        $sortie = new Sortie();
        $sortie->setEtat(Etat::OUVERTE->value);
        $sortie->setDateLimiteInscription(new DateTimeImmutable("yesterday"));

        $this->assertEquals(Etat::CLOTUREE->value, $sortie->getEtat());
    }
}
