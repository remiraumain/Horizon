<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class ProjectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Project|null $project */
        $project = $options['date'] ?? null;
        $isEdit = $project && $project->getId();

        $builder
            ->add('name')
            ->add('description')
        ;

        $imageContraints = [
            new Image([
                'maxSize' => '5M'
            ]),
        ];

        if (!$isEdit || !$project->getImageFilename())
        {
            $imageContraints[] = new NotNull([
                'message' => 'Please upload an image'
            ]);
        }
        $builder
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => $imageContraints
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
