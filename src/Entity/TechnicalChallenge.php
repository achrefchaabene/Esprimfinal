<?php

namespace App\Entity;

use App\Repository\TechnicalChallengeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TechnicalChallengeRepository::class)]
class TechnicalChallenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $difficulty = null;

    #[ORM\Column(length: 50)]
    private ?string $category = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $sampleSolution = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $hints = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getSampleSolution(): ?string
    {
        return $this->sampleSolution;
    }

    public function setSampleSolution(?string $sampleSolution): self
    {
        $this->sampleSolution = $sampleSolution;

        return $this;
    }

    public function getHints(): ?string
    {
        return $this->hints;
    }

    public function setHints(?string $hints): self
    {
        $this->hints = $hints;

        return $this;
    }
}