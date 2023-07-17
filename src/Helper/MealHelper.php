<?php

namespace App\Helper;

use App\Repository\LanguageRepository;
use App\Repository\TranslationRepository;
use App\Repository\TagRepository;
use App\Repository\CategoryRepository;
use App\Repository\IngredientRepository;
use App\Repository\MealRepository;

class MealHelper
{
    private $translationRepository;
    private $languageRepository;
    private $tagRepository;
    private $categoryRepository;
    private $ingredientRepository;
    private $mealRepository;

    public function __construct(
        TranslationRepository $translationRepository,
        LanguageRepository $languageRepository,
        TagRepository $tagRepository,
        CategoryRepository $categoryRepository,
        IngredientRepository $ingredientRepository,
        MealRepository $mealRepository
    ) {
        $this->translationRepository = $translationRepository;
        $this->languageRepository = $languageRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->ingredientRepository = $ingredientRepository;
        $this->mealRepository = $mealRepository;
    }

    public function countTotalItems($queryBuilder): int
    {
        $countQuery = $queryBuilder->getQuery();

        $countQuery->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true);
        $countQuery->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($countQuery);
        $totalItems = count($paginator);

        return $totalItems;
    }

    public function generateUrl($request, $page): string
    {
        $queryParameters = $request->query->all();
        $queryParameters['page'] = $page;
        $queryString = http_build_query($queryParameters);

        return $request->getSchemeAndHttpHost() . $request->getPathInfo() . '?' . $queryString;
    }

    public function translate(string $keyword, string $lang): ?string
    {
        $translation = $this->translationRepository->findOneBy(['keyword' => $keyword, 'language' => $lang]);

        return $translation ? $translation->getValue() : null;
    }

    public function getLanguageId(string $name): ?int
    {
        $language = $this->languageRepository->findOneByName($name);
        return $language ? $language->getId() : null;
    }

    private function loadTagsData(int $mealId, string $lang): array
    {
        $tags = $this->tagRepository->findTagsByMealId($mealId);
        $translatedTags = [];

        foreach ($tags as $tag) {
            $translatedTag = [
                'id' => $tag->getId(),
                'title' => $this->translate($tag->getTitle(), $lang),
                'slug' => $tag->getSlug(),
            ];
            $translatedTags[] = $translatedTag;
        }

        return $translatedTags;
    }

    private function loadCategoryData(?int $mealId, string $lang): ?array
    {
        $meal = $this->mealRepository->find($mealId);
        $categoryId = $meal ? $meal->getCategory() : null;
        if ($categoryId) {
            $category = $this->categoryRepository->find($categoryId);

            if ($category) {
                return [
                    'id' => $category->getId(),
                    'title' => $this->translate($category->getTitle(), $lang),
                    'slug' => $category->getSlug(),
                ];
            }
        }

        return null;
    }

    private function loadIngredientsData(int $mealId, string $lang): array
    {
        $meal = $this->mealRepository->find($mealId);
        $ingredients = $meal ? $meal->getIngredients()->toArray() : [];

        $translatedIngredients = [];

        foreach ($ingredients as $ingredient) {
            $translatedIngredient = [
                'id' => $ingredient->getId(),
                'title' => $this->translate($ingredient->getTitle(), $lang),
                'slug' => $ingredient->getSlug(),
            ];
            $translatedIngredients[] = $translatedIngredient;
        }

        return $translatedIngredients;
    }

    public function buildResponse(int $page, int $totalItems, int $perPage, int $totalPages, array $translatedResults, $request): array
    {
        $response = [
            'meta' => [
                'currentPage' => $page,
                'totalItems' => $totalItems,
                'itemsPerPage' => $perPage,
                'totalPages' => $totalPages,
            ],
            'data' => $translatedResults,
            'links' => [
                'prev' => ($page > 1) ? $this->generateUrl($request, $page - 1) : null,
                'next' => ($page < $totalPages) ? $this->generateUrl($request, $page + 1) : null,
                'self' => $this->generateUrl($request, $page),
            ],
        ];

        return $response;
    }

    public function processResults(array $results, string $lang, ?string $with): array
    {
        $translatedResults = [];

        foreach ($results as $result) {
            $translatedResult = $result;
            $translatedResult['title'] = $this->translate($result['title'], $lang);
            $translatedResult['description'] = $this->translate($result['description'], $lang);
            unset($translatedResult['created_at']);
            unset($translatedResult['deleted_at']);
            unset($translatedResult['updated_at']);


            if ($with) {
                $withArray = explode(',', $with);

                foreach ($withArray as $item) {
                    if ($item === 'ingredients') {
                        $translatedResult['ingredients'] = $this->loadIngredientsData($result['id'], $lang);
                    } elseif ($item === 'category') {
                        $translatedResult['category'] = $this->loadCategoryData($result['id'], $lang);
                    } elseif ($item === 'tags') {
                        $translatedResult['tags'] = $this->loadTagsData($result['id'], $lang);
                    }
                }
            }
            $translatedResults[] = $translatedResult;
        }

        return $translatedResults;
    }
}
