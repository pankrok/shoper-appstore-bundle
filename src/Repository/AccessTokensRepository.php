<?php

namespace PanKrok\ShoperAppstoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PanKrok\ShoperAppstoreBundle\Entity\AccessTokens;

/**
 * @method AccessTokens|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessTokens|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessTokens[]    findAll()
 * @method AccessTokens[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessTokensRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessTokens::class);
    }

    // /**
    //  * @return AccessTokens[] Returns an array of AccessTokens objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccessTokens
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
