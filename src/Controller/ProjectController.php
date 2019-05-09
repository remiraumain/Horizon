<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectFormType;
use App\Repository\ProjectRepository;
use App\Service\UploaderHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @Route("admin/project/new", name="project_new")
     */
    public function new(EntityManagerInterface $entityManager, Request $request, UploaderHelper $uploaderHelper)
    {
        $form = $this->createForm(ProjectFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Project $project */
            $project = $form->getData();
            $project->setAuthor($this->getUser());

            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_edit', [
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
    public function edit(Project $project, Request $request, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $form = $this->createForm(ProjectFormType::class, $project);

        $form->handleRequest($request);

        $desc = $form->get('description')->getData();
        $violations = $validator->validate(
            $desc,
            [
                new NotNull([
                    'message' => 'Don\'t forget the description of your project'
                ]),
            ]
        );

        if ($form->isSubmitted() && $form->isValid()) {
            $nextAction = $form->get('save')->isClicked()
                ? 'task_new'
                : 'task_publish';
            if ($nextAction === 'task_publish') {
                if ($violations->count() === 0) {
                    if ($project->getProjectImages()->count() > 0) {
                        $project->setPublishedAt(new DateTime('now'));
                        $this->addFlash('success', 'Project published ✅');
                        $em->persist($project);
                        $em->flush();

                        return $this->redirectToRoute('project_show', [
                            'slug' => $project->getSlug(),
                        ]);
                    } else {
                        $this->addFlash('error', 'Please upload some images 🤨');

                        return $this->render('project/edit.html.twig', [
                            'form' => $form->createView(),
                            'project' => $project,
                        ]);
                    }
                } else {
                    $this->addFlash('error', current($violations[0]));

                    return $this->render('project/edit.html.twig', [
                        'form' => $form->createView(),
                        'project' => $project,
                    ]);
                }
            }
            $this->addFlash('success', 'Project saved 🥰');
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('project_show', [
                'slug' => $project->getSlug(),
            ]);
        }

        return $this->render('project/edit.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
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
