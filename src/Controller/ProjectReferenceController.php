<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectReference;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectReferenceController extends AbstractController
{
    /**
     * @Route("/project/{slug}/references", name="project_add_reference", methods={"POST"})
     */
    public function uploadProjectReference(Project $project, Request $request, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('reference');

        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank([
                    'message' => 'Please select a file to upload'
                ]),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'image/*',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain'
                    ],
                ])
            ]
        );

        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        $filename = $uploaderHelper->uploadProjectReference($uploadedFile);

        $projectReference = new ProjectReference($project);
        $projectReference->setFilename($filename);
        $projectReference->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);
        $projectReference->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream');

        $entityManager->persist($projectReference);
        $entityManager->flush();

        return $this->json(
            $projectReference,
            201,
            [],
            [
                'groups' => ['main']
            ]
        );
    }

    /**
     * @Route("/project/references/{id}/download", name="project_download_reference", methods={"GET"})
     */
    public function downloadProjectReference(ProjectReference $reference, UploaderHelper $uploaderHelper)
    {
//        $project = $reference->getProject();
//        $this->denyAccessUnlessGranted('MANAGE', $project);

        $response = new StreamedResponse(function () use ($reference, $uploaderHelper) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $uploaderHelper->readStream($reference->getFilePath(), false);

            stream_copy_to_stream($fileStream, $outputStream);
        });
        $response->headers->set('Content-Type', $reference->getMimeType());
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $reference->getOriginalFilename()
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
