<?php

namespace App\Repository;

use App\Entity\DiscussionPrivee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiscussionPrivee|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscussionPrivee|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscussionPrivee[]    findAll()
 * @method DiscussionPrivee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscussionPriveeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscussionPrivee::class);
    }

    // /**
    //  * @return DiscussionPrivee[] Returns an array of DiscussionPrivee objects
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
    public function findOneBySomeField($value): ?DiscussionPrivee
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
