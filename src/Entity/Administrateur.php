<?php

namespace App\Entity;

use App\Repository\AdministrateurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdministrateurRepository::class)]
#[ORM\Table(name: 'administrateurs')]
class Administrateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'administrateur', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    private ?string $nomComplet = null;

    // Getters and Setters
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
        if ($user !== null && $user->getAdministrateur() !== $this) {
            $user->setAdministrateur($this);
        }
        return $this;
    }
    
    public function getPrenom(): ?string 
    { 
        return $this->prenom; 
    }
    
    public function setPrenom(string $prenom): self 
    { 
        $this->prenom = $prenom; 
        return $this; 
    }
    
    public function getNomComplet(): ?string 
    { 
        return $this->nomComplet; 
    }
    
    public function setNomComplet(string $nomComplet): self 
    { 
        $this->nomComplet = $nomComplet; 
        return $this; 
    }
}

