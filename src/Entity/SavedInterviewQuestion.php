<?php

namespace App\Entity;

use App\Repository\SavedInterviewQuestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavedInterviewQuestionRepository::class)]
class SavedInterviewQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'savedInterviewQuestions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?InterviewQuestion $question = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $savedAt = null;

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

    public function getQuestion(): ?InterviewQuestion
    {
        return $this->question;
    }

    public function setQuestion(?InterviewQuestion $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getSavedAt(): ?\DateTimeImmutable
    {
        return $this->savedAt;
    }

    public function setSavedAt(\DateTimeImmutable $savedAt): self
    {
        $this->savedAt = $savedAt;

        return $this;
    }
}