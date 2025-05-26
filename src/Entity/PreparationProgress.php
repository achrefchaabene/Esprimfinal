<?php

namespace App\Entity;

use App\Repository\PreparationProgressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreparationProgressRepository::class)]
class PreparationProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'preparationProgress')]
#[ORM\JoinColumn(nullable: false)]
private ?User $user = null;

    #[ORM\Column]
    private ?int $commonQuestionsCompleted = 0;

    #[ORM\Column]
    private ?int $behavioralQuestionsCompleted = 0;

    #[ORM\Column]
    private ?int $technicalChallengesCompleted = 0;

    #[ORM\Column]
    private ?bool $mockInterviewCompleted = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCommonQuestionsCompleted(): ?int
    {
        return $this->commonQuestionsCompleted;
    }

    public function setCommonQuestionsCompleted(int $commonQuestionsCompleted): self
    {
        $this->commonQuestionsCompleted = $commonQuestionsCompleted;

        return $this;
    }

    public function getBehavioralQuestionsCompleted(): ?int
    {
        return $this->behavioralQuestionsCompleted;
    }

    public function setBehavioralQuestionsCompleted(int $behavioralQuestionsCompleted): self
    {
        $this->behavioralQuestionsCompleted = $behavioralQuestionsCompleted;

        return $this;
    }

    public function getTechnicalChallengesCompleted(): ?int
    {
        return $this->technicalChallengesCompleted;
    }

    public function setTechnicalChallengesCompleted(int $technicalChallengesCompleted): self
    {
        $this->technicalChallengesCompleted = $technicalChallengesCompleted;

        return $this;
    }

    public function isMockInterviewCompleted(): ?bool
    {
        return $this->mockInterviewCompleted;
    }

    public function setMockInterviewCompleted(bool $mockInterviewCompleted): self
    {
        $this->mockInterviewCompleted = $mockInterviewCompleted;

        return $this;
    }

    public function getTotalProgress(): int
    {
        $total = 0;
        
        // Supposons que chaque section vaut 25% du total
        $total += min(25, ($this->commonQuestionsCompleted / 5) * 25);
        $total += min(25, ($this->behavioralQuestionsCompleted / 6) * 25);
        $total += min(25, ($this->technicalChallengesCompleted / 4) * 25);
        $total += $this->mockInterviewCompleted ? 25 : 0;
        
        return $total;
    }

    public function getCommonQuestionsProgress(): int
    {
        return min(100, ($this->commonQuestionsCompleted / 5) * 100);
    }

    public function getBehavioralQuestionsProgress(): int
    {
        return min(100, ($this->behavioralQuestionsCompleted / 6) * 100);
    }

    public function getTechnicalChallengesProgress(): int
    {
        return min(100, ($this->technicalChallengesCompleted / 4) * 100);
    }

    public function getCompanyResearchProgress(): int
    {
        // À implémenter selon votre logique
        return 0;
    }

    public function getSalaryNegotiationProgress(): int
    {
        // À implémenter selon votre logique
        return 0;
    }
}

