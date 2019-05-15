<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Entity\ProjectReference;
use App\Repository\ProjectRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectAdminController extends AbstractController
{
    /**
     * @Route("/admin/project/{slug}/images", name="admin_project_add_image", methods={"POST"})
     * @IsGranted("MANAGE", subject="project")
     */
    public function uploadProjectImage(Project $project, Request $request, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('image-upload');

        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank([
                    'message' => 'Please select an image to upload'
                ]),
                new Image([
                    'maxSize' => '5M',
                ]),
            ]
        );

        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        $filename = $uploaderHelper->uploadProjectImage($uploadedFile);

        $projectImage = new ProjectImage($project);
        $projectImage->setFilename($filename);
        $projectImage->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);
        $projectImage->setMimeType($uploadedFile->getMimeType() ?? 'image/*');

        $entityManager->persist($projectImage);
        $entityManager->flush();

        return $this->json(
            $projectImage,
            201,
            [],
            [
                'groups' => ['main']
            ]
        );
    }

    /**
     * @Route("/project/{slug}/images", name="project_list_images", methods={"GET"})
     */
    public function getProjectImage(Project $project)
    {
        return $this->json(
            $project->getProjectImages(),
            200,
            [],
            [
                'groups' => ['main']
            ]
        );
    }

    /**
     * @Route("/project/images/{id}", name="project_delete_image", methods={"DELETE"})
     */
    public function deleteProjectReference(ProjectImage $image, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $project = $image->getProject();
        $this->denyAccessUnlessGranted('MANAGE', $project);


        $violations = $validator->validate(
            $project->getProjectImages()->getValues(),
            [
                new Count([
                    'min' => 2,
                    'minMessage' => 'Well.. you need at least one image of your project so I am not deleting it !',
                ])
            ]
        );

        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        $entityManager->remove($image);
        $entityManager->flush();

        $uploaderHelper->deleteFile($image->getImagePath(), true);

        return new Response(null, 204);
    }

    /**
     * @Route("/admin/project/{id}/delete", name="project_delete")
     * @IsGranted("MANAGE", subject="project")
     */
    public function delete(Project $project, EntityManagerInterface $em, UploaderHelper $uploaderHelper)
    {
        foreach ($project->getProjectImages() as $image)
        {
            $uploaderHelper->deleteFile($image->getImagePath(), true);
        }
        $em->remove($project);
        $em->flush();
        $this->addFlash('success', 'Project Deleted! That\'s a good thing because it was garbage 💩');
        return $this->redirectToRoute('project_list');
    }

    /**
     * @Route("/admin/project", name="admin_project_list")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function listAll(ProjectRepository $projectRepo)
    {
        $projects = $projectRepo->findAll();

        return $this->render('project/list.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * @Route("/list/project", name="project_list")
     * @IsGranted("ROLE_USER")
     */
    public function listManage(ProjectRepository $projectRepo)
    {
        $projects = $projectRepo->findBy(
            ['author' => $this->getUser()]
        );

        return $this->render('project/list.html.twig', [
            'projects' => $projects,
        ]);
    }
}
