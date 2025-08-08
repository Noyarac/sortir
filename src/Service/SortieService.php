<?php

namespace App\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SortieService
{

    public function __construct(private readonly EntityManagerInterface $entityManager){}
    public function gererEtatSortie(Sortie $sortie, ?string $etat = null) : void
    {
        if ($etat !== null) {
            if (!in_array($etat, array_column(Etat::cases(), 'value'), true)) {
                throw new \InvalidArgumentException("État non autorisé : $etat");
            }
            $sortie->setEtat($etat);
        }

        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    public function inscription(Sortie $sortie, User $user): bool {
        try {
            $sortie->addParticipant($user);
            if (sizeof($sortie->getParticipants()) >= $sortie->getNbInscriptionMax()) {
                $sortie->setEtat(Etat::CLOTUREE->value);
            }
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }

    public function desistement(Sortie $sortie, User $user): bool {
        try {
            $sortie->removeParticipant($user);
            if (
                sizeof($sortie->getParticipants()) < $sortie->getNbInscriptionMax()
                && $sortie->getDateLimiteInscription() > new DateTimeImmutable()
                && $sortie->getEtat() == Etat::CLOTUREE->value
            ) {
                $sortie->setEtat(Etat::OUVERTE->value);
            }
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }

    public function mettreAjourSortiesHistorisees(): int
    {
        $sortieRepository = $this->entityManager->getRepository(Sortie::class);
        $sortiesAHistoriser = $sortieRepository->findSortiesTermineesDepuisPlusDUnMois(1);
        foreach ($sortiesAHistoriser as $sortie) {
            $sortie->setEtat(Etat::HISTORISEE->value);
        }
        $this->entityManager->flush();
        return count($sortiesAHistoriser);
    }

}
