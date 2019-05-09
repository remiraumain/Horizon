<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectFormType;
use App\Repository\ProjectRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class ProjectController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(ProjectRepository $repository)
    {
        $porjects = $repository->findAllPublishedOrderedByNewest();
        return $this->render('project/homepage.html.twig', [
            'controller_name' => 'ProjectController',
            'projects' => $porjects,
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
            $project->setPublishedAt(new \DateTime('now'));
            $project->setAuthor($this->getUser());

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
     * @Route("/project/{slug}/edit", name="project_edit")
     * @IsGranted("MANAGE", subject="project")
     */
    public function edit(Project $project, Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper)
    {
        $form = $this->createForm(ProjectFormType::class, $project);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();

            $this->addFlash('success', 'Project updated 👍');

            return $this->redirectToRoute('project_show', [
                'slug' => $project->getSlug(),
            ]);
        }

        return $this->render('project/edit.html.twig', [
            'form' => $form->createView(),
            'project' => $project
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
