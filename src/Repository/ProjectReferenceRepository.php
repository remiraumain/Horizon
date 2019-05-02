<?php

namespace App\Repository;

use App\Entity\ProjectReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ProjectReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectReference[]    findAll()
 * @method ProjectReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectReferenceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProjectReference::class);
    }

    // /**
    //  * @return ProjectReference[] Returns an array of ProjectReference objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProjectReference
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
