<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Service\UploaderHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends AppFixtures
{
    private $passwordEncoder;
    private $uploaderHelper;
    private static $userImages = [
        'pp1.jpg',
        'pp2.jpg',
        'pp3.jpg',
    ];

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, UploaderHelper $uploaderHelper)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->uploaderHelper = $uploaderHelper;
    }

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'main_users', function($i) use ($manager) {
            $user = new User();
            $user->setEmail(sprintf('user%d@example.com', $i));
            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $user->setEnabled(true);

            $imageFilename = $this->fakeUploadImage();

            $user->setImageFilename($imageFilename);

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'test'
            ));

            $apiToken1 = new ApiToken($user);
            $apiToken2 = new ApiToken($user);
            $manager->persist($apiToken1);
            $manager->persist($apiToken2);

            return $user;
        });

        $this->createMany(3, 'admin_users', function($i) {
            $user = new User();
            $user->setEmail(sprintf('admin%d@test.com', $i));
            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setEnabled(true);

            $imageFilename = $this->fakeUploadImage();

            $user->setImageFilename($imageFilename);

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'admin'
            ));

            return $user;
        });

        $manager->flush();
    }

    private function fakeUploadImage(): string
    {
        $randomImage = $this->faker->randomElement(self::$userImages);
        $fs = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$randomImage;
        $fs->copy(__DIR__.'/images/'.$randomImage, $targetPath, true);
        return $this->uploaderHelper
            ->uploadProfileImage(new File($targetPath), null);
    }
}
