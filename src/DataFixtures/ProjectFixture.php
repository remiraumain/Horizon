<?php

namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Common\Persistence\ObjectManager;

class ProjectFixture extends AppFixtures
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(Project::class, 10, function (Project $project, $count)
        {
            $project->setName("test")
                ->setDescription("test");

        });

        $manager->flush();
    }
}
