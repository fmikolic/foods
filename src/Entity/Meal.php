<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity
 * @ORM\Table(name="meal")
 * @Gedmo\SoftDeleteable(fieldName="deleted_at", timeAware=false)
 */
class Meal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deleted_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="meals")
     * @ORM\JoinTable(name="meal_has_tag",
     *     joinColumns={@ORM\JoinColumn(name="meal_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    private $tags;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Ingredient", inversedBy="meals")
     * @ORM\JoinTable(name="meal_has_ingredient",
     *     joinColumns={@ORM\JoinColumn(name="meal_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="ingredient_id", referencedColumnName="id")}
     * )
     */
    private $ingredients;

    /**
     * @ORM\OneToMany(targetEntity="MealHasIngredient", mappedBy="meal")
     */
    private $mealHasIngredients;


    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->created_at = $createdAt;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): void
    {
        $this->deleted_at = $deletedAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updated_at = $updatedAt;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addMeal($this);
        }
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
        $tag->removeMeal($this);
    }

    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): void
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients[] = $ingredient;
            $ingredient->addMeal($this);
        }
    }
    /**
     * @return Collection|MealHasIngredient[]
     */
    public function getMealHasIngredients(): Collection
    {
        return $this->mealHasIngredients;
    }

    public function addMealHasIngredient(MealHasIngredient $mealHasIngredient): void
    {
        if (!$this->mealHasIngredients->contains($mealHasIngredient)) {
            $this->mealHasIngredients[] = $mealHasIngredient;
            $mealHasIngredient->setMeal($this);
        }
    }

    public function removeMealHasIngredient(MealHasIngredient $mealHasIngredient): void
    {
        $this->mealHasIngredients->removeElement($mealHasIngredient);
        $mealHasIngredient->setMeal(null);
    }

    public function removeIngredient(Ingredient $ingredient): void
    {
        $this->ingredients->removeElement($ingredient);
        $ingredient->removeMeal($this);
    }
}
