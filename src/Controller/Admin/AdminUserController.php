<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdministrateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/users', name: 'admin_users_')]
class AdminUserController extends AbstractController
{
    #[Route('/create', name: 'create')]
    public function create(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = new User();
        $form = $this->createForm(AdministrateurType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_ADMIN']);
            $user->setType('admin');
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('motdepasse')->getData()
                )
            );
            
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Administrateur créé avec succès');
            return $this->redirectToRoute('admin_dashboard');
        }
        
        return $this->render('admin/demandes/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}