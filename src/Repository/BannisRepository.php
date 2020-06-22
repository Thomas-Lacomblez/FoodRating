<?php

namespace App\Repository;

use App\Entity\Bannis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Bannis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bannis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bannis[]    findAll()
 * @method Bannis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bannis::class);
    }

    // /**
    //  * @return Bannis[] Returns an array of Bannis objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Bannis
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
