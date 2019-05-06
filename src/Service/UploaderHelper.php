<?php


namespace App\Service;


use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    const PROFILE_IMAGE = 'profile_image';
    const PROJECT_IMAGE = 'project_image';
    const PROJECT_REFERENCE = 'project_reference';

    private $filesystem;
    private $privateFilesystem;
    private $requestStackContext;
    private $logger;
    private $publicAssetBaseUrl;

    public function __construct(FilesystemInterface $publicUploadsFilesystem, FilesystemInterface $privateUploadsFilesystem, RequestStackContext $requestStackContext, LoggerInterface $logger, string $uploadedAssetsBaseUrl)
    {
        $this->filesystem = $publicUploadsFilesystem;
        $this->privateFilesystem = $privateUploadsFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->logger = $logger;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }

    public function uploadImage(File $file, ?string $existingFilename, string $directory): string
    {
        $newFilename = $this->uploadFile($file, $directory, true);

        if ($existingFilename) {
            try {
                $result = $this->filesystem->delete($directory.'/'.$existingFilename);

                if ($result === false) {
                    throw new \Exception(sprintf('Cold not delete old uploaded file "%s"', $existingFilename));
                }
            } catch (FileNotFoundException $e) {
                $this->logger->alert(sprintf('Old uploaded file "%s" was missing when trying to delete', $existingFilename));
            }
        }

        return $newFilename;
    }

    public function uploadProfileImage(File $file, ?string $existingFilename): string
    {
        return $this->uploadImage($file, $existingFilename, self::PROFILE_IMAGE);
    }

    public function uploadProjectImage(File $file, ?string $existingFilename): string
    {
        return $this->uploadImage($file, $existingFilename, self::PROJECT_IMAGE);
    }

    public function uploadProjectReference(File $file): string
    {
        return $this->uploadFile($file, self::PROJECT_REFERENCE, false);
    }

    public function getPublicPath(string $path): string
    {
        $fullPath = $this->publicAssetBaseUrl.'/'.$path;
        // if it's already absolute, just return
        if (strpos($fullPath, '://') !== false) {
            return $fullPath;
        }
        // needed if you deploy under a subdirectory
        return $this->requestStackContext
                ->getBasePath().$fullPath;
    }

    /**
     * @return resource
     */
    public function readStream(string $path, bool $isPublic)
    {
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;

        $resource = $filesystem->readStream($path);

        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }

        return $resource;
    }

    public function deleteFile(string $path, bool $isPublic)
    {
        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;
        $result = $filesystem->delete($path);
        if ($result === false) {
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        }
    }

    private function uploadFile(File $file, string $directory, bool $isPublic): string
    {
        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilename = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $file->guessExtension();

        $filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;

        $result = $stream = fopen($file->getPathname(), 'r');
        $filesystem->writeStream(
            $directory . '/' . $newFilename,
            $stream
        );

        if ($result === false) {
            throw new \Exception(sprintf('Cold not write uploaded file "%s"', $newFilename));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $newFilename;
    }
}