<?php


namespace App\Controller;


use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/{id}/delete", name="comment_delete")
     */
    public function delete(Comment $comment, EntityManagerInterface $entityManager)
    {
        $project = $comment->getProject();

        if ($project->getAuthor() !== $this->getUser() && $comment->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Comment deleted 👌');

        return $this->redirectToRoute('project_show', [
            'slug' => $project->getSlug(),
        ]);
    }
}