<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller responsible for handling home page redirection based on user roles.
 */
class HomeController extends AbstractController
{
    // Role constants for better maintainability
    private const ROLE_JOB_SEEKER = 'ROLE_JOB_SEEKER';
    private const ROLE_COMPANY = 'ROLE_COMPANY';
    private const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * Home page route that redirects users to their appropriate dashboard
     * based on their assigned role.
     *
     * @return Response Redirect response to the appropriate route
     */
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        // Redirect to login page if user is not authenticated
        if (!$user) {
            return $this->redirectToRoute('app_first_page');
        }

        $userRoles = $user->getRoles();
        
        // Determine redirect route based on user's highest priority role
        $redirectRoute = $this->determineRedirectRoute($userRoles);
        
        return $this->redirectToRoute($redirectRoute);
    }

    /**
     * Determines the appropriate redirect route based on user roles.
     * Priority order: ADMIN > COMPANY > JOB_SEEKER
     *
     * @param array $userRoles Array of user roles
     * @return string Route name to redirect to
     */
    private function determineRedirectRoute(array $userRoles): string
    {
        // Check roles in priority order (admin has highest priority)
        if (in_array(self::ROLE_ADMIN, $userRoles)) {
            return 'admin_dashboard';
        }
        
        if (in_array(self::ROLE_COMPANY, $userRoles)) {
            return 'app_entreprise_home';
        }
        
        if (in_array(self::ROLE_JOB_SEEKER, $userRoles)) {
            return 'job_seeker_home';
        }
        
        // Default fallback for users without specific roles
        return 'app_first_page';
    }
}
