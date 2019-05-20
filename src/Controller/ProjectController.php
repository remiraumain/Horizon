<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Project;
use App\Form\ProjectFormType;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Service\UploaderHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ProjectController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(ProjectRepository $projectRepository, Request $request, PaginatorInterface $paginator)
    {
        if (!$this->getUser()) {
            return $this->render('project/landing.html.twig', [
                'controller_name' => 'ProjectController',
            ]);
        }

        $filter = $request->query->get('filter');

        if (!is_null($filter) && $filter === 'likes') {
            $queryBuilder = $projectRepository->getAllPublishedLikedByQueryBuilder($this->getUser()->getId());

        } else {
            $queryBuilder = $projectRepository->getAllPublishedByLikesQueryBuilder();
        }

        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            11/*limit per page*/
        );

        return $this->render('project/homepage.html.twig', [
            'controller_name' => 'ProjectController',
            'pagination' => $pagination,
            'filter' => $filter,
        ]);
    }

    /**
     * @Route("/launch", name="app_landing")
     */
    public function landing(ProjectRepository $projectRepository, Request $request)
    {
        return $this->render('project/landing.html.twig', [
        ]);
    }

    /**
     * @Route("admin/project/new", name="project_new")
     * @IsGranted("ROLE_USER")
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
                new NotBlank([
                    'message' => 'Don\'t forget the description of your project'
                ]),
            ]
        );

        if ($form->isSubmitted() && $form->isValid()) {
            if ($violations->count() > 0) {
                $this->addFlash('error', $violations[0]->getMessage());

                return $this->render('project/edit.html.twig', [
                    'form' => $form->createView(),
                    'project' => $project,
                ]);
            }
            if (!$project->getProjectImages()->count() > 0) {
                $this->addFlash('error', 'Please upload some images 🤨');

                return $this->render('project/edit.html.twig', [
                    'form' => $form->createView(),
                    'project' => $project,
                ]);
            }

            $nextAction = $form->get('save')->isClicked()
                ? 'task_new'
                : 'task_publish';

            if ($nextAction === 'task_publish') {
                $project->setPublishedAt(new DateTime('now'));
                $this->addFlash('success', 'Project published ✅');
            } else {
                $project->setPublishedAt(null);
                $this->addFlash('success', 'Project saved in the drafts 🥰');
            }

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
     * @IsGranted("ROLE_USER")
     */
    public function show(Project $project, Request $request, EntityManagerInterface $entityManager)
    {
        $like = $project->getLikeUsers()->contains($this->getUser());

        $comment = new Comment();

        $form = $this->createFormBuilder($comment)
            ->add('content')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser());
            $comment->setProject($project);

            if (!$comment->getCreatedAt()) {
                $comment->setCreatedAt(new \DateTime());
            }

            $comment->setUpdatedAt(new \DateTime());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success','Comment posted !');
            return $this->redirect($request->getUri());
        }

        return $this->render('project/show.html.twig', [
            'controller_name' => 'ProjectController',
            'project' => $project,
            'like' => $like,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/project/{slug}/like", name="project_like", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function like(Project $project, ProjectRepository $projectRepository, EntityManagerInterface $entityManager)
    {
        if ($project->getLikeUsers()->contains($this->getUser())) {
            $project->removeLikeUser($this->getUser());
        } else {
            $project->addLikeUser($this->getUser());
        }

        $entityManager->persist($project);
        $entityManager->flush();

        return new JsonResponse(['hearts' => $project->getLikeUsers()->count()]);
    }

    /**
     * @Route("/project", name="project_filter")
     * @IsGranted("ROLE_USER")
     */
    public function filter(ProjectRepository $projectRepository, CategoryRepository $categoryRepository, Request $request)
    {
        $categories = $categoryRepository->findAll();
        $filter = $request->query->get('filter');

        if ($filter) {
            $projects = $projectRepository->findAllPublishedByCategory($filter);
        } else {
            $projects = $projectRepository->findAllPublishedOrderedByNewest();
        }

        return $this->render('project/explore.html.twig', [
            'controller_name' => 'ProjectController',
            'projects' => $projects,
            'categories' => $categories,
            'filter' => $filter
        ]);
    }
}
