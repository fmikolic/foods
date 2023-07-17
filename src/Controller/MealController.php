<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Helper\MealHelper;
use App\Validator\ParamsValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api', name: 'api_')]
class MealController extends AbstractController
{

    private $mealHelper;
    private $paramsValidator;
    public function __construct(MealHelper $mealHelper, ParamsValidator $paramsValidator)
    {
        $this->mealHelper = $mealHelper;
        $this->paramsValidator = $paramsValidator;
    }

    #[Route('/meals', name: 'meals_get', methods: ['get'])]
    public function index(Request $request): JsonResponse
    {
        $errors = $this->paramsValidator->validateParameters($request);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors], 400);
        }

        $perPage = $request->query->getInt('per_page', 10);
        $page = $request->query->getInt('page', 1);
        $category = $request->query->get('category');
        $tags = $request->query->get('tags');
        $with = $request->query->get('with');
        $lang = $this->mealHelper->getLanguageId($request->query->get('lang'));
        $diffTime = $request->query->getInt('diff_time', 0);

        $repository = $this->getDoctrine()->getRepository(Meal::class);
        $queryBuilder = $repository->createQueryBuilder('m')
            ->select('DISTINCT m')
            ->leftJoin('m.category', 'c')
            ->leftJoin('m.tags', 't')
            ->leftJoin('m.ingredients', 'i');

        if ($diffTime > 0) {
            $deletedAtTime = (new \DateTime())->setTimestamp($diffTime);
            $updatedAtTime = (new \DateTime())->setTimestamp($diffTime);
            $createdAtTime = (new \DateTime())->setTimestamp($diffTime);
        
            $queryBuilder
                ->andWhere('m.deleted_at >= :deletedAtTime OR m.updated_at >= :updatedAtTime OR m.created_at >= :createdAtTime')
                ->setParameter('deletedAtTime', $deletedAtTime, \Doctrine\DBAL\Types\DateTimeType::DATETIME)
                ->setParameter('updatedAtTime', $updatedAtTime, \Doctrine\DBAL\Types\DateTimeType::DATETIME)
                ->setParameter('createdAtTime', $createdAtTime, \Doctrine\DBAL\Types\DateTimeType::DATETIME);
        }else{
            $queryBuilder
            ->andWhere('m.deleted_at IS NULL');
        }

        if ($category !== null) {
            if ($category === 'NULL') {
                $queryBuilder->andWhere('m.category IS NULL');
            } else if ($category === '!NULL') {
                $queryBuilder->andWhere('m.category IS NOT NULL');
            } else {
                $queryBuilder->andWhere('c.id = :category')
                    ->setParameter('category', $category);
            }
        }

        if ($tags) {
            $tagIds = explode(',', $tags);
            $numTags = count($tagIds);

            foreach ($tagIds as $index => $tagId) {
                $alias = 't_' . $index;
                $queryBuilder
                    ->innerJoin('m.tags', $alias)
                    ->andWhere($alias . '.id = :tagId' . $index)
                    ->setParameter('tagId' . $index, $tagId);
            }

            $queryBuilder
                ->groupBy('m.id')
                ->having($queryBuilder->expr()->eq('COUNT(t)', ':numTags'))
                ->setParameter('numTags', $numTags);
        }

        $queryBuilder->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage);

        $results = $queryBuilder->getQuery()->getArrayResult();

        $totalItems = $this->mealHelper->countTotalItems($queryBuilder);

        $totalPages = ceil($totalItems / $perPage);

        $translatedResults = $this->mealHelper->processResults($results, $lang, $with);

        $response = $this->mealHelper->buildResponse($page, $totalItems, $perPage, $totalPages, $translatedResults, $request);

        return new JsonResponse($response);
    }
}
