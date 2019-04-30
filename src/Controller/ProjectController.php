<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectFormType;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage()
    {
        return $this->render('project/index.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

    /**
     * @Route("/project/new", name="project_new")
     */
    public function new(EntityManagerInterface $entityManager, Request $request, UploaderHelper $uploaderHelper)
    {
        $form = $this->createForm(ProjectFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            /** @var Project $project */
            $project = $form->getData();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();

            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadProjectImage($uploadedFile);
                $project->setImageFilename($newFilename);
            }

            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', 'Project created ✅');

            return $this->redirectToRoute('project_show', [
                'slug' => $project->getSlug(),
            ]);
        }

        return $this->render('project/new.html.twig', [
            'controller_name' => 'ProjectController',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/project/{slug}", name="project_show")
     */
    public function show(Project $project)
    {
        return $this->render('project/show.html.twig', [
            'controller_name' => 'ProjectController',
            'project' => $project,
        ]);
    }
}
