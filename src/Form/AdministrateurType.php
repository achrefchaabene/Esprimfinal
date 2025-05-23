<?php
// src/Form/AdministrateurType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdministrateurType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('username', TextType::class, [
'label' => 'Nom d\'utilisateur',
'required' => true,
])
->add('email', EmailType::class, [
'label' => 'Email',
'required' => true,
])
->add('motdepasse', PasswordType::class, [
'label' => 'Mot de passe',
'required' => true,
])
->add('prenom', TextType::class, [
'label' => 'Prénom',
'required' => true,
])
->add('nomComplet', TextType::class, [
'label' => 'Nom complet',
'required' => true,
]);
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([
'data_class' => \App\Entity\Administrateur::class,
]);
}
}