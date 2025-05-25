<?php
namespace App\Entity;
use App\Repository\PublicationRepository;use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication{
    #[ORM\Id]    #[ORM\GeneratedValue]
    #[ORM\Column]    private ?int $id = null;
    #[ORM\Column(length: 255)]
    private ?string $title = null;
    #[ORM\Column(type: 'text')]    private ?string $content = null;
    #[ORM\Column(length: 100)]
    private ?string $category = null;
    #[ORM\Column]    private ?bool $isPublished = false;
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    #[ORM\Column(nullable: true)]    private ?\DateTimeImmutable $updatedAt = null;
    #[ORM\ManyToOne(inversedBy: 'publications')]
    #[ORM\JoinColumn(nullable: false)]    private ?User $user = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $skills = null;
    public function getId(): ?int
    {        return $this->id;
    }
    public function getTitle(): ?string    {
        return $this->title;    }
    public function setTitle(string $title): static
    {        $this->title = $title;
        return $this;    }
    public function getContent(): ?string
    {        return $this->content;
    }
    public function setContent(string $content): static    {
        $this->content = $content;        return $this;
    }
    public function getCategory(): ?string    {
        return $this->category;    }
    public function setCategory(string $category): static
    {        $this->category = $category;
        return $this;    }
    public function isIsPublished(): ?bool
    {        return $this->isPublished;
    }
    public function setIsPublished(bool $isPublished): static    {
        $this->isPublished = $isPublished;        return $this;
    }
    public function getCreatedAt(): ?\DateTimeImmutable    {
        return $this->createdAt;    }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {        $this->createdAt = $createdAt;
        return $this;    }
    public function getUpdatedAt(): ?\DateTimeImmutable
    {        return $this->updatedAt;
    }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static    {
        $this->updatedAt = $updatedAt;        return $this;
    }
    public function getUser(): ?User    {
        return $this->user;    }
    public function setUser(?User $user): static
    {        $this->user = $user;
        return $this;
    }
    public function getSkills(): ?string
    {
        return $this->skills;
    }
    public function setSkills(?string $skills): self
    {
        $this->skills = $skills;
        return $this;
    }
}



























































