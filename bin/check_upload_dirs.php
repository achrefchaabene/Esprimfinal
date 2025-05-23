#!/usr/bin/env php
<?php

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new \App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$directories = [
    'profile_images_directory',
    'company_logos_directory',
    'resumes_directory',
    'voice_messages_directory'
];

foreach ($directories as $dirParam) {
    $dir = $container->getParameter($dirParam);
    echo "Checking $dirParam: $dir\n";
    
    if (!file_exists($dir)) {
        echo "  Directory does not exist. Creating...\n";
        mkdir($dir, 0777, true);
        echo "  Created directory with permissions 0777\n";
    } else {
        echo "  Directory exists\n";
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "  Permissions: $perms\n";
        
        if (!is_writable($dir)) {
            echo "  WARNING: Directory is not writable!\n";
        } else {
            echo "  Directory is writable\n";
        }
    }
    
    echo "\n";
}

echo "Done checking upload directories.\n";


