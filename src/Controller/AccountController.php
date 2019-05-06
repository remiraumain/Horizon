<?php

namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/profil/{id}", name="profile_show")
     */
    public function show(User $user)
    {
        return $this->render('@FOSUser/Profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/api/account", name="api_account")
     */
    public function accountApi()
    {
        $user = $this->getUser();

        return $this->json($user, 200, [], [
            'groups' => ['main'],
        ]);
    }
}
