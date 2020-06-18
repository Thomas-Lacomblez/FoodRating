<?php

namespace App\Repository;

use App\Entity\DemandeAmi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DemandeAmi|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeAmi|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeAmi[]    findAll()
 * @method DemandeAmi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeAmiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeAmi::class);
    }

    // /**
    //  * @return DemandeAmi[] Returns an array of DemandeAmi objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DemandeAmi
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
