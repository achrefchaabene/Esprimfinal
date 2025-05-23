<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class JobSeekerProfileCustomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control'],
                'data' => $options['user_data']['firstName']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control'],
                'data' => $options['user_data']['lastName']
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre professionnel',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['user_data']['title']
            ])
            ->add('about', TextareaType::class, [
                'label' => 'À propos de moi',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5],
                'data' => $options['user_data']['about']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'data' => $options['user_data']['email']
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['user_data']['phone']
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['user_data']['address']
            ])
            ->add('experience', TextType::class, [
                'label' => 'Niveau d\'expérience',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['user_data']['experience']
            ])
            ->add('education', TextType::class, [
                'label' => 'Niveau d\'éducation',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['user_data']['education']
            ])
            ->add('skills', TextType::class, [
                'label' => 'Compétences',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Séparez les compétences par des virgules',
                'data' => $options['user_data']['skills']
            ])
            ->add('profileImageFile', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG ou GIF)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user_data' => [],
        ]);
    }
}