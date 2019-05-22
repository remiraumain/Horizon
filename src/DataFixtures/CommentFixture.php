<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CommentFixture extends AppFixtures implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(100, 'main_comments', function ($count) use ($manager) {

            $user = $this->getRandomReference('main_users');
            /** @var Project $project */
            $project = $this->getRandomReference('main_projects');

            while (!$project->isPublished()) {
                $project = $this->getRandomReference('main_projects');
            }

            $date = $this->faker->dateTimeBetween('-100 days', '-1 days');

            $comment = new Comment();
            $comment->setContent($this->faker->paragraphs(1, true))
                ->setAuthor($user)
                ->setProject($project)
                ->setCreatedAt($date)
                ->setUpdatedAt($date)
            ;

            return $comment;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixture::class,
            ProjectFixture::class,
        ];
    }
}
