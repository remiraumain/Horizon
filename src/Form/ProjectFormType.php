<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class ProjectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Project|null $project */
        $project = $builder->getData() ?? null;
        $isEdit = $project && $project->getId();

        $builder
            ->add('name')
            ->add('category', EntityType::class, [
                // looks for choices from this entity
                'class' => Category::class,

                // uses the User.username property as the visible option string
                'choice_label' => 'name',

                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,)
                'placeholder' => 'Choose a category',
            ])
        ;

        if ($isEdit) {
            $builder->add('description')
                ->add('save', SubmitType::class, ['label' => 'Save'])
                ->add('publish', SubmitType::class, ['label' => 'Publish'])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
