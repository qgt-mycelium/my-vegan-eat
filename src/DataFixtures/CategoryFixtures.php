<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoryFixtures extends Fixture
{
    // Create some tags with faker (between 10 and 20) with random data and add them to the database
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        // Categories
        $postsCategories = ['Recipes', 'Lifestyle', 'Budget', 'Travel', 'DIY', 'Fashion', 'Beauty', 'Fitness', 'Health'];
        $recipesCategories = ['Breakfasts', 'Lunches', 'Dinners', 'Desserts', 'Snacks', 'Drinks'];

        foreach ($postsCategories as $i => $categoryName) {
            $category = (new Category());
            $category->setName($categoryName);
            $category->setSlug(strtolower($categoryName));
            $category->setDescription($faker->sentence(4));
            $manager->persist($category);
            $this->addReference('category_'.$i, $category);

            if ('Recipes' === $categoryName) {
                foreach ($recipesCategories as $j => $recipeCategoryName) {
                    $recipeCategory = (new Category());
                    $recipeCategory->setName($recipeCategoryName);
                    $recipeCategory->setSlug($category->getSlug().'/'.strtolower($recipeCategoryName));
                    $recipeCategory->setDescription($faker->sentence(4));
                    $recipeCategory->setParent($category);
                    $manager->persist($recipeCategory);
                    $this->addReference('category_'.$i.'_'.$j, $recipeCategory);
                }
            }
        }

        $manager->flush();
    }
}
