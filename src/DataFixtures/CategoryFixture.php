<?php


namespace App\DataFixtures;


use App\Entity\Category;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixture extends AppFixtures
{
    private static $categories = [
        'Web',
        'Arduino',
    ];

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(2, 'main_categories', function ($count) use ($manager) {
            $randomCategory = self::$categories[$count];

            $category = new Category();
            $category->setName($randomCategory);

            return $category;
        });
        $manager->flush();
    }
}