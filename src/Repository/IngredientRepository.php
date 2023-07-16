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

    public function findIngredientsByMealIds(array $mealIds): array
    {
        return $this->createQueryBuilder('i')
            ->select('i, mhi, m')
            ->join('i.mealHasIngredients', 'mhi')
            ->join('mhi.meal', 'm')
            ->where('m.id IN (:mealIds)')
            ->setParameter('mealIds', $mealIds)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Book[] Returns an array of Book objects
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
    public function findOneBySomeField($value): ?Book
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
