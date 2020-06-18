<?php

namespace App\Repository;

use App\Entity\ReponsePrivee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReponsePrivee|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReponsePrivee|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReponsePrivee[]    findAll()
 * @method ReponsePrivee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReponsePriveeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReponsePrivee::class);
    }

    // /**
    //  * @return ReponsePrivee[] Returns an array of ReponsePrivee objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReponsePrivee
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
