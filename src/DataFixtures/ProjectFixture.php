<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Service\UploaderHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ProjectFixture extends AppFixtures
{
    private $uploaderHelper;
    private static $projectImages = [
        'image1.jpg',
        'image2.jpg',
        'image3.jpg',
    ];

    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'main_projects', function ($count) use ($manager) {
            $project = new Project();

            $imageFilename = $this->fakeUploadImage();

            // publish most articles
            if ($this->faker->boolean(70)) {
                $project->setPublishedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            }

            $project->setName("test")
                ->setDescription("test")
                ->setImageFilename($imageFilename)
            ;

            return $project;
        });

        $manager->flush();
    }

    private function fakeUploadImage(): string
    {
        $randomImage = $this->faker->randomElement(self::$projectImages);
        $fs = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$randomImage;
        $fs->copy(__DIR__.'/images/'.$randomImage, $targetPath, true);
        return $this->uploaderHelper
            ->uploadProjectImage(new File($targetPath), null);
    }
}
