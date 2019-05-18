<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Service\UploaderHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProjectFixture extends AppFixtures implements DependentFixtureInterface
{
    public const PROJECT_REFERENCE = 'main_projects';

    protected function loadData(ObjectManager $manager)
    {
        $user = $this->getRandomReference('main_users');
        $this->createMany(100, 'main_projects', function ($count) use ($manager) {
            $project = new Project();
            $project->setAuthor($this->getRandomReference('main_users'))
                ->setCategory($this->getRandomReference('main_categories'))
            ;

            foreach ($this->getRandomReferences('main_users', 3) as $user)
            {
                $project->addLikeUser($user);
            }

            // publish most articles
            if ($this->faker->boolean(70)) {
                $project->setPublishedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            }

            $project->setName($this->faker->realText($maxNbChars = 100))
                ->setDescription($this->faker->paragraphs(3, true))
            ;

            return $project;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixture::class,
            CategoryFixture::class,
        ];
    }
}
