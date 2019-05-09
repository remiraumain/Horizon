<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Service\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ProjectImageFixture extends AppFixtures implements DependentFixtureInterface
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

    public function loadData(ObjectManager $manager)
    {
        $projects = $this->getAllReferencesByType(ProjectFixture::PROJECT_REFERENCE);

        foreach ($projects as $project)
        {
            /** @var Project $project */
            $project = $this->getReference($project);

            for ($i = 0; $i < 3; $i++) {
                $projectImage = new ProjectImage($project);
                $filename = $this->fakeUploadImage();
                $projectImage->setFilename($filename);
                $projectImage->setOriginalFilename($filename);
                $projectImage->setMimeType('image/*');

                $manager->persist($projectImage);
            }
        }

        $manager->flush();
    }

    private function fakeUploadImage(): string
    {
        $randomImage = $this->faker->randomElement(self::$projectImages);
        $fs = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$randomImage;
        $fs->copy(__DIR__.'/images/'.$randomImage, $targetPath, true);
        return $this->uploaderHelper
            ->uploadProjectImage(new File($targetPath));
    }

    public function getDependencies()
    {
        return [
            ProjectFixture::class,
        ];
    }
}
