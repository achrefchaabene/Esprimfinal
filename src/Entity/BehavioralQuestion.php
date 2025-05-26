<?php

namespace App\Entity;

use App\Repository\BehavioralQuestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BehavioralQuestionRepository::class)]
class BehavioralQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    #[ORM\Column(length: 255)]
    private ?string $subCategory = null;

    #[ORM\Column(length: 50)]
    private ?string $difficulty = null;

    #[ORM\Column(type: 'text')]
    private ?string $tips = null;

    #[ORM\Column(type: 'text')]
    private ?string $exampleAnswer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getSubCategory(): ?string
    {
        return $this->subCategory;
    }

    public function setSubCategory(string $subCategory): self
    {
        $this->subCategory = $subCategory;

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

    public function getTips(): ?string
    {
        return $this->tips;
    }

    public function setTips(string $tips): self
    {
        $this->tips = $tips;

        return $this;
    }

    public function getExampleAnswer(): ?string
    {
        return $this->exampleAnswer;
    }

    public function setExampleAnswer(string $exampleAnswer): self
    {
        $this->exampleAnswer = $exampleAnswer;

        return $this;
    }
}