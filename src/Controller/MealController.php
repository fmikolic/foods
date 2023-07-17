<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Entity\MealHasTag;
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
            ->select('DISTINCT m.id, m.title, m.description, m.status')
            ->leftJoin('m.category', 'c')
            ->leftJoin('m.tags', 't')
            ->leftJoin('m.ingredients', 'i');

        if ($diffTime > 0) {
            $queryBuilder->orWhere('m.created_at >= :diffTime')
                ->orWhere('m.updated_at >= :diffTime')
                ->orWhere('m.deleted_at >= :diffTime')
                ->setParameter('diffTime', $diffTime);
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
                ->having('COUNT(DISTINCT t.id) = :numTags')
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
