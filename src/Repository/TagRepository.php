<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findTagsByMealId(int $mealId): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.meals', 'm')
            ->andWhere('m.id = :mealId')
            ->setParameter('mealId', $mealId)
            ->getQuery()
            ->getResult();
    }

}
