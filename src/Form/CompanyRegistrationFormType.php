<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'input-field', 'placeholder' => 'Email']
            ])
            ->add('username', TextType::class, [
                'attr' => ['class' => 'input-field', 'placeholder' => 'Username']
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false, // Ce champ n'est pas mappé directement à l'entité
                'attr' => ['class' => 'input-field', 'placeholder' => 'Password']
            ])
            ->add('companyName', TextType::class, [
                'attr' => ['class' => 'input-field', 'placeholder' => 'Company Name']
            ])
            ->add('industry', ChoiceType::class, [
                'choices' => [
                    'Sélectionnez un secteur' => '',
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
                'placeholder' => 'Sélectionnez un secteur d\'activité',
                'required' => true
            ])
            // Assurez-vous que ce champ est bien défini dans votre entité User
            ->add('address', TextType::class, [
                'attr' => ['class' => 'input-field', 'placeholder' => 'Address'],
                'required' => false, // Rendez-le optionnel pour éviter les erreurs de validation
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Company' => 'company',
                    'Job Seeker' => 'job_seeker'
                ],
                'data' => 'company', // Valeur par défaut
                'mapped' => false // Ou mappez-le si vous voulez le gérer dans le contrôleur
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
