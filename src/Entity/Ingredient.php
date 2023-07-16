<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ingredient")
 */
class Ingredient
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="MealHasIngredient", mappedBy="ingredient")
     */
    private $mealHasIngredients;

    public function __construct()
    {
        $this->mealHasIngredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getMealHasIngredients(): Collection
    {
        return $this->mealHasIngredients;
    }

    public function addMealHasIngredient(MealHasIngredient $mealHasIngredient): void
    {
        if (!$this->mealHasIngredients->contains($mealHasIngredient)) {
            $this->mealHasIngredients[] = $mealHasIngredient;
            $mealHasIngredient->setIngredient($this);
        }
    }

    public function removeMealHasIngredient(MealHasIngredient $mealHasIngredient): void
    {
        $this->mealHasIngredients->removeElement($mealHasIngredient);
        $mealHasIngredient->setIngredient(null);
    }
}
