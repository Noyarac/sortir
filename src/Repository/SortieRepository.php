<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\DTO\FiltreSortie;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects
     */
    public function findByFilter(FiltreSortie $filtreSortie): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.campus = :campus')
            ->setParameter('campus', $filtreSortie->getCampus())
            ->andWhere('s.etat != :etatHistorisee')
            ->setParameter('etatHistorisee', Etat::HISTORISEE->value);
        if ($filtreSortie->getContient()) {
            $qb->andWhere($qb->expr()->like('s.nom', ':contient'))
                ->setParameter("contient", "%" . $filtreSortie->getContient() . "%");
        }
        if ($filtreSortie->getDebut()) {
            $qb->andWhere("s.dateHeureDebut >= :debut")
                ->setParameter("debut", $filtreSortie->getDebut());
        }
        if ($filtreSortie->getFin()) {
            $qb->andWhere("s.dateHeureDebut <= :fin")
                ->setParameter("fin", $filtreSortie->getFin());
        }
        if ($filtreSortie->getOrganisateur()) {
            $qb->andWhere("s.organisateur = :organisateur")
                ->setParameter("organisateur", $filtreSortie->getUser());
        }
        if ($filtreSortie->getParticipant() || $filtreSortie->getNonParticipant()) {
            $qb->leftJoin("s.participants", "p");
        }
        if ($filtreSortie->getParticipant() && !$filtreSortie->getNonParticipant()) {
            $qb->andWhere("p.id = :participantId")
                ->setParameter("participantId", $filtreSortie->getUser()->getId());
        }
        if ($filtreSortie->getNonParticipant() && !$filtreSortie->getParticipant()) {
            $qb->andWhere('s.id NOT IN (
                SELECT sortie2.id FROM App\Entity\Sortie sortie2
                JOIN sortie2.participants participant2
                WHERE participant2.id = :nonParticipantId
            )')
                ->setParameter('nonParticipantId', $filtreSortie->getUser()->getId());
        }
        if ($filtreSortie->getTerminees()) {
            $qb->andWhere("s.etat = '" . Etat::TERMINEE->value ."'");
        }


        return $qb->orderBy('s.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSortiesTermineesDepuisPlusDeNbMois(int $nbMois) : array
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->andWhere('s.dateHeureDebut <= :limite')
            ->setParameter('limite', (new \DateTime())->modify('-'.$nbMois.'months'))
            ->andWhere('s.etat IN (:etats)')
            ->setParameter('etats', [Etat::TERMINEE->value, Etat::ANNULEE->value]);

        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }

    public function findAllToCloture(): array {
        return $this->createQueryBuilder('s')
            ->andWhere("s.etat = '"  . Etat::OUVERTE->value . "'")
            ->andWhere("s.dateLimiteInscription < :date")
            ->setParameter('date', new DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
