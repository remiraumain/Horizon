<?php


namespace App\Service;


use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    const PROJECT_IMAGE = 'project_image';

    private $uploadsPath;
    private $requestStackContext;

    public function __construct(string $uploadsPath, RequestStackContext $requestStackContext)
    {
        $this->uploadsPath = $uploadsPath;
        $this->requestStackContext = $requestStackContext;
    }

    public function uploadProjectImage(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath.'/'.self::PROJECT_IMAGE;

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename) . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

        $uploadedFile->move(
            $destination,
            $newFilename
        );

        return $newFilename;
    }

    public function getPublicPath(string $path): string
    {
        return $this->requestStackContext
            ->getBasePath().'/uploads/'.$path;
    }

}