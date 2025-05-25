<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class EntrepriseProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom d\'utilisateur'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom d\'utilisateur est obligatoire']),
                    new Length([
                        'min' => 3,
                        'max' => 30,
                        'minMessage' => 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Email'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Email(['message' => 'Veuillez entrer un email valide'])
                ]
            ])
            ->add('companyName', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de l\'entreprise'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l\'entreprise est obligatoire']),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Le nom de l\'entreprise ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('industry', ChoiceType::class, [
                'label' => 'Secteur d\'activité',
                'choices' => [
                    'Informatique / IT' => 'Informatique / IT',
                    'Marketing / Communication' => 'Marketing / Communication',
                    'Finance / Banque' => 'Finance / Banque',
                    'Santé / Médical' => 'Santé / Médical',
                    'Éducation / Formation' => 'Éducation / Formation',
                    'Ingénierie / Technique' => 'Ingénierie / Technique',
                    'Commerce / Vente' => 'Commerce / Vente',
                    'Mécanique / Automobile' => 'Mécanique / Automobile',
                    'Bâtiment / Construction' => 'Bâtiment / Construction',
                    'Hôtellerie / Restauration' => 'Hôtellerie / Restauration',
                    'Transport / Logistique' => 'Transport / Logistique',
                    'Juridique / Droit' => 'Juridique / Droit',
                    'Ressources Humaines' => 'Ressources Humaines',
                    'Autre' => 'Autre'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Secteur d\'activité'
                ],
                'required' => false
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse complète'
                ],
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('website', UrlType::class, [
                'label' => 'Site web',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://www.example.com'
                ],
                'required' => false,
                'constraints' => [
                    new Url(['message' => 'Veuillez entrer une URL valide'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description de votre entreprise',
                    'rows' => 5
                ],
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('logoFile', FileType::class, [
                'label' => 'Logo de l\'entreprise',
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
            'data_class' => User::class,
        ]);
    }
}