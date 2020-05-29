<?php

namespace App\Repository;

use App\Entity\MoyenneProduits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MoyenneProduits|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoyenneProduits|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoyenneProduits[]    findAll()
 * @method MoyenneProduits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoyenneProduitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoyenneProduits::class);
    }

    // /**
    //  * @return MoyenneProduits[] Returns an array of MoyenneProduits objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MoyenneProduits
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
