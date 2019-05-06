<?php


namespace App\EventListener;


use App\Entity\User;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class AddImageAfterValidationEditProfile implements EventSubscriberInterface
{
    private $router;
    private $uploaderHelper;

    public function __construct(RouterInterface $router, UploaderHelper $uploaderHelper)
    {
        $this->router = $router;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function onProfileSuccess(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getForm()->getData();

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $event->getForm()['imageFile']->getData();

        if ($uploadedFile) {
            $newFilename = $this->uploaderHelper->uploadProfileImage($uploadedFile, $user->getImageFilename());
            $user->setImageFilename($newFilename);
        }
    }
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onProfileSuccess'
        ];
    }
}