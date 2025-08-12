<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Form\DTO\FiltreCampus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Campus>
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }

    public function findByFilter(FiltreCampus $filtreCampus): array
    {
        $qb = $this->createQueryBuilder('s');
        if ($filtreCampus->getContient()) {
            $qb->andWhere($qb->expr()->like('s.nom', ':contient'))
                ->setParameter("contient", "%" . $filtreCampus->getContient() . "%");
        }

        return $qb->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    //    /**
    //     * @return Campus[] Returns an array of Campus objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Campus
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
