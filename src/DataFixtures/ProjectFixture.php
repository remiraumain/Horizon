<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Service\UploaderHelper;
use Doctrine\Common\Persistence\ObjectManager;

class ProjectFixture extends AppFixtures
{
    private $uploaderHelper;

    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

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
