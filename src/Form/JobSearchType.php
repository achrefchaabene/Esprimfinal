<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keywords', TextType::class, [
                'label' => 'Mots-clés',
                'required' => false,
                'attr' => ['placeholder' => 'Rechercher par titre, contenu...']
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['placeholder' => 'Ville, région...']
            ])
            ->add('industry', ChoiceType::class, [
                'label' => 'Secteur d\'activité',
                'required' => false,
                'choices' => [
                    'Tous les secteurs' => '',
                    'Informatique' => 'informatique',
                    'Finance' => 'finance',
                    'Santé' => 'sante',
                    'Éducation' => 'education',
                    'Marketing' => 'marketing',
                    // Ajoutez d'autres secteurs selon vos besoins
                ],
                'placeholder' => 'Sélectionnez un secteur'
            ])
            ->add('dateFrom', DateType::class, [
                'label' => 'Date de début',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true
            ])
            ->add('dateTo', DateType::class, [
                'label' => 'Date de fin',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}


