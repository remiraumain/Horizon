<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use http\QueryString;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @return Project[]
     */
    public function findAllPublished()
    {
        return $this->addIsPublishedQueryBuilder()
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Project[]
     */
    public function findAllPublishedOrderedByNewest()
    {
        return $this->addIsPublishedQueryBuilder()
            //->leftJoin('a.tags', 't')
            //->addSelect('t')
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getAllPublishedByLikesQueryBuilder():QueryBuilder
    {
        return $this->addIsPublishedQueryBuilder()
            ->orderBy('a.likes', 'DESC');
    }

    /**
     * @return Project[]
     */
    public function findAllPublishedByCategory(array $filter)
    {
        if (!isset($filter['id'])) {
            return $this->findAllPublishedByLikes();
        }
        return $this->addIsPublishedQueryBuilder()
            ->leftJoin('a.category', 'c')
            ->addSelect('c')
            ->andWhere('c.name = :name')
            ->setParameter('name', $filter['name'])
            ->getQuery()
            ->getResult()
            ;
    }

    public function getAllPublishedLikedByQueryBuilder(int $id): QueryBuilder
    {
        return $this->addIsPublishedQueryBuilder()
            ->leftJoin('a.likeUsers', 'u')
            ->addSelect('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ;
    }

    private function addIsPublishedQueryBuilder(QueryBuilder $qb = null)
    {
        return $this->getOrCreateQueryBuilder($qb)
            ->andWhere('a.publishedAt IS NOT NULL');
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('a');
    }

    // /**
    //  * @return Project[] Returns an array of Project objects
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
    public function findOneBySomeField($value): ?Project
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
