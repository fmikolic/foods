<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\MealHasIngredient;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


class IngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    public function findIngredientsByMealId(int $mealId): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.meals', 'm')
            ->andWhere('m.id = :mealId')
            ->setParameter('mealId', $mealId)
            ->getQuery()
            ->getResult();
    }
}
