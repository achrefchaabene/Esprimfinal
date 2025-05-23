#!/usr/bin/env php
<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$em = $container->get('doctrine.orm.entity_manager');
$userRepo = $em->getRepository(\App\Entity\User::class);

// Récupérer un utilisateur existant
$user = $userRepo->findOneBy([]);

if (!$user) {
    echo "Aucun utilisateur trouvé dans la base de données.\n";
    exit(1);
}

echo "Test de l'entité User:\n";
echo "ID: " . $user->getId() . "\n";
echo "Username: " . $user->getUsername() . "\n";
echo "Profile Image: " . ($user->getProfileImage() ?: "null") . "\n\n";

// Tester setProfileImage
$testImagePath = 'uploads/profile_images/test-' . uniqid() . '.jpg';
echo "Définition de l'image à: " . $testImagePath . "\n";
$user->setProfileImage($testImagePath);
echo "Nouvelle valeur: " . ($user->getProfileImage() ?: "null") . "\n\n";

// Tester la persistance
echo "Persistance des modifications...\n";
$em->persist($user);
$em->flush();
echo "Modifications persistées.\n\n";

// Vérifier que les modifications ont été enregistrées
$refreshedUser = $userRepo->find($user->getId());
echo "Valeur après persistance: " . ($refreshedUser->getProfileImage() ?: "null") . "\n";

// Restaurer l'ancienne valeur
echo "Restauration de l'ancienne valeur...\n";
$user->setProfileImage(null);
$em->persist($user);
$em->flush();
echo "Valeur restaurée.\n";