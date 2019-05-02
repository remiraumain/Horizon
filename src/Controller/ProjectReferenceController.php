<?php

namespace App\Controller;

use App\Api\ProjectReferenceUploadApiModel;
use App\Entity\Project;
use App\Entity\ProjectReference;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectReferenceController extends AbstractController
{
    /**
     * @Route("/project/{slug}/references", name="project_add_reference", methods={"POST"})
     */
    public function uploadProjectReference(Project $project, Request $request, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        if ($request->headers->get('Content-Type') === 'application/json') {
            /** @var ProjectReferenceUploadApiModel $uploadApiModel */
            $uploadApiModel = $serializer->deserialize(
                $request->getContent(),
                ProjectReferenceUploadApiModel::class,
                'json'
            );

            $violations = $validator->validate($uploadApiModel);
            if ($violations->count() > 0) {
                return $this->json($violations, 400);
            }


            $tmpPath = sys_get_temp_dir().'/sf_upload'.uniqid();
            file_put_contents($tmpPath, $uploadApiModel->getDecodedData());
            $uploadedFile = new FileObject($tmpPath);
            $originalFilename = $uploadApiModel->filename;
        } else {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->files->get('reference');
            $originalFilename = $uploadedFile->getClientOriginalName();
        }

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
        $projectReference->setOriginalFilename($originalFilename ?? $filename);
        $projectReference->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream');

        if (is_file($uploadedFile->getPathname())) {
            unlink($uploadedFile->getPathname());
        }

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
     * @Route("/project/{slug}/references", name="project_list_references", methods={"GET"})
     */
    public function getProjectReference(Project $project)
    {
        return $this->json(
            $project->getProjectReferences(),
            200,
            [],
            [
                'groups' => ['main']
            ]
        );
    }

    /**
     * @Route("/project/{slug}/references/reorder", methods="POST", name="project_reorder_references")
     */
    public function reorderArticleReferences(Project $project, Request $request, EntityManagerInterface $entityManager)
    {
        $orderedIds = json_decode($request->getContent(), true);

        if ($orderedIds === null) {
            return $this->json(['detail' => 'Invalid body'], 400);
        }

        // from (position)=>(id) to (id) => (position)
        $orderedIds = array_flip($orderedIds);
        foreach ($project->getProjectReferences() as $reference) {
            $reference->setPosition($orderedIds[$reference->getId()]);
        }

        $entityManager->flush();

        return $this->json(
            $project->getProjectReferences(),
            200,
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

    /**
     * @Route("/project/references/{id}", name="project_delete_reference", methods={"DELETE"})
     */
    public function deleteProjectReference(ProjectReference $reference, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager)
    {
//        $project = $reference->getProject();
//        $this->denyAccessUnlessGranted('MANAGE', $project);

        $entityManager->remove($reference);
        $entityManager->flush();

        $uploaderHelper->deleteFile($reference->getFilePath(), false);

        return new Response(null, 204);
    }

    /**
     * @Route("/project/references/{id}", name="project_update_reference", methods={"PUT"})
     */
    public function updateProjectReference(ProjectReference $reference, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, ValidatorInterface $validator)
    {
//        $project = $reference->getProject();
//        $this->denyAccessUnlessGranted('MANAGE', $project);

        $serializer->deserialize(
            $request->getContent(),
            ProjectReference::class,
            'json',
            [
                'object_to_populate' => $reference,
                'groups' => ['input']
            ]
        );

        $violations = $validator->validate($reference);
        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        $entityManager->persist($reference);
        $entityManager->flush();

        return $this->json(
            $reference,
            200,
            [],
            [
                'groups' => ['main']
            ]
        );
    }
}
