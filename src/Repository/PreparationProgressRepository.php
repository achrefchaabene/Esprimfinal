<?php

namespace App\Repository;

use App\Entity\PreparationProgress;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PreparationProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreparationProgress::class);
    }

    public function findOrCreateForUser(User $user): PreparationProgress
    {
        $progress = $this->findOneBy(['user' => $user]);
        
        if (!$progress) {
            $progress = new PreparationProgress();
            $progress->setUser($user);
            // Initialiser les valeurs par défaut si nécessaire
            $progress->setCommonQuestionsCompleted(0);
            $progress->setBehavioralQuestionsCompleted(0);
            $progress->setTechnicalChallengesCompleted(0);
            $progress->setMockInterviewCompleted(false);
        }
        
        return $progress;
    }
}
