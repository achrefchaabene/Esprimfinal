<?php
// src/Controller/FirstPageController.php

// src/Controller/FirstPageController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstPageController extends AbstractController
{
    #[Route('/', name: 'app_first_page')]
    public function index(): Response
    {
        return $this->render('first_page/index.html.twig', [
            'welcome_message' => 'WELCOME TO ESPRIM CAREER',
            'subtitle' => 'Build your career. Make your mark. Right here in Tunisia',
        ]);
    }

    #[Route('/features', name: 'app_features')]
    public function features(): Response
    {
        return $this->render('first_page/features.html.twig', [
            'page_title' => 'Our Features',
            'features' => [
                [
                    'title' => 'Job Listings',
                    'description' => 'Access thousands of job offers from top companies in Tunisia',
                    'icon' => 'fas fa-briefcase'
                ],
                [
                    'title' => 'Internship Opportunities',
                    'description' => 'Find the perfect internship to kickstart your career',
                    'icon' => 'fas fa-user-graduate'
                ],
                [
                    'title' => 'Company Profiles',
                    'description' => 'Explore company profiles and culture before applying',
                    'icon' => 'fas fa-building'
                ],
                [
                    'title' => 'Application Tracking',
                    'description' => 'Track all your applications in one place',
                    'icon' => 'fas fa-tasks'
                ],
                [
                    'title' => 'Career Advice',
                    'description' => 'Get expert advice to improve your job search',
                    'icon' => 'fas fa-lightbulb'
                ],
                [
                    'title' => 'Custom Alerts',
                    'description' => 'Receive notifications for new jobs matching your profile',
                    'icon' => 'fas fa-bell'
                ]
            ]
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('first_page/about.html.twig', [
            'page_title' => 'About Esprim Career',
            'about_sections' => [
                [
                    'title' => 'Our Mission',
                    'content' => 'To connect talented professionals with the best career opportunities in Tunisia and help companies find the perfect candidates for their teams.'
                ],
                [
                    'title' => 'Our Vision',
                    'content' => 'To become the leading career platform in Tunisia, transforming the way people find jobs and companies hire talent.'
                ],
                [
                    'title' => 'Our Story',
                    'content' => 'Founded in 2023, Esprim Career started as a small project to help students find internships. Today, we serve thousands of job seekers and hundreds of companies across various industries.'
                ]
            ],
            'team' => [
                [
                    'name' => 'John Doe',
                    'position' => 'CEO & Founder',
                    'bio' => '10+ years in HR and recruitment',
                    'photo' => 'team1.jpg'
                ],
                [
                    'name' => 'Jane Smith',
                    'position' => 'CTO',
                    'bio' => 'Tech enthusiast and problem solver',
                    'photo' => 'team2.jpg'
                ]
            ]
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('first_page/contact.html.twig', [
            'page_title' => 'Contact Us',
            'contact_info' => [
                'address' => '123 Career Street, Tunis, Tunisia',
                'phone' => '+216 12 345 678',
                'email' => 'contact@esprimcareer.tn',
                'working_hours' => 'Monday to Friday, 9:00 AM to 5:00 PM'
            ]
        ]);
    }
}