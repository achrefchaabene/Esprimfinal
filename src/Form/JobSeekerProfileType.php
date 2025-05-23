<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormInterface;

class JobSeekerProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre professionnel',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('about', TextareaType::class, [
                'label' => 'À propos de moi',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('experience', TextType::class, [
                'label' => 'Niveau d\'expérience',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('education', TextType::class, [
                'label' => 'Niveau d\'éducation',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('skills', TextType::class, [
                'label' => 'Compétences',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Séparez les compétences par des virgules'
            ])
            ->add('profileImageFile', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false, // Très important : ne pas mapper ce champ à l'entité
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
            'data_class' => User::class,
            // Ignorer le champ profileImage lors de la validation et de la soumission
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                return $groups;
            },
        ]);
    }
}
