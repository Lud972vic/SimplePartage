<?php

namespace App\Form;

use App\Entity\Folder;
use App\Repository\FolderRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FolderType
 *
 * This class defines the form for creating and editing a Folder entity.
 * It includes fields for the folder's name and its parent folder.
 */
class FolderType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options The options for building the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du dossier',
                'attr' => ['placeholder' => 'Saisir le nom du dossier']
            ])
            ->add('parent', EntityType::class, [
                'class' => Folder::class,
                'choice_label' => 'name',
                'label' => 'Dossier parent',
                'required' => false,
                'placeholder' => 'Aucun (dossier racine)',
                'query_builder' => function (FolderRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.name', 'ASC');
                },
            ])
        ;
    }

    /**
     * Configures the options for this form.
     *
     * @param OptionsResolver $resolver The resolver for the form options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Folder::class,
        ]);
    }
}