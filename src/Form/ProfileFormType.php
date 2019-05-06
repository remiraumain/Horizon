<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;
use Symfony\Component\Validator\Constraints\Image;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $imageContraints = [
            new Image([
                'maxSize' => '5M'
            ]),
        ];

        $builder
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => $imageContraints
            ])
            ->add('firstName')
            ->add('lastName')
            ->remove('username')
        ;
    }

    public function getParent()
    {
        return BaseProfileFormType::class;
    }


}