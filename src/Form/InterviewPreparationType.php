<?php

namespace App\Form;

use App\Entity\JobApplication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class InterviewPreparationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('preparationNotes', TextareaType::class, [
                'label' => 'Preparation Notes',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Write down key points you want to mention, questions to ask, etc.'
                ],
                'required' => false
            ])
            ->add('strengths', TextareaType::class, [
                'label' => 'Your Strengths for This Role',
                'attr' => ['rows' => 3],
                'required' => false
            ])
            ->add('weaknesses', TextareaType::class, [
                'label' => 'Areas to Improve',
                'attr' => ['rows' => 3],
                'required' => false
            ])
            ->add('questionsToAsk', CollectionType::class, [
                'label' => 'Questions to Ask',
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => ['placeholder' => 'Add a question to ask the interviewer'],
                    'label' => false
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobApplication::class,
        ]);
    }
}