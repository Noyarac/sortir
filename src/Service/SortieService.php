<?php

namespace App\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class SortieService
{

    public function __construct(private readonly EntityManagerInterface $entityManager){}

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
            // Si l'utilisateur ne fait pas partie des participants
            if (!in_array($user, $sortie->getParticipants()->toArray())) return false;

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

    public function mettreAJourSortiesHistorisees(): int
    {
        $sortieRepository = $this->entityManager->getRepository(Sortie::class);
        $sortiesAHistoriser = $sortieRepository->findSortiesTermineesDepuisPlusDeNbMois(1);
        foreach ($sortiesAHistoriser as $sortie) {
            $sortie->setEtat(Etat::HISTORISEE->value);
        }
        $this->entityManager->flush();
        return count($sortiesAHistoriser);
    }

}
