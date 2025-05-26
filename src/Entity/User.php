<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null; // 'job_seeker' ou 'company'

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $industry = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profileImage = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $about = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $experience = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $education = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $skills = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\OneToMany(targetEntity: Job::class, mappedBy: 'company')]
    private $jobs;

    #[ORM\OneToMany(targetEntity: SavedJob::class, mappedBy: 'user')]
    private $savedJobs;

    #[ORM\OneToMany(targetEntity: JobApplication::class, mappedBy: 'user')]
    private $jobApplications;

    #[ORM\ManyToMany(targetEntity: Conversation::class, mappedBy: "participants")]
    private $conversations;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender')]
    private $messages;

    /**
     * @ORM\OneToOne(targetEntity=BusinessCardHtml::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $businessCardHtml;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isApproved = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Administrateur::class, cascade: ['persist', 'remove'])]
    private ?Administrateur $administrateur = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: PreparationProgress::class, cascade: ['persist', 'remove'])]
    private ?PreparationProgress $preparationProgress = null;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
        $this->savedJobs = new ArrayCollection();
        $this->jobApplications = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        
        if ($this->type === 'job_seeker') {
            $roles[] = 'ROLE_JOB_SEEKER';
        } elseif ($this->type === 'company') {
            $roles[] = 'ROLE_COMPANY';
        }

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear any temporary sensitive data
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Retourne le type d'utilisateur ('job_seeker' ou 'company')
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Définit le type d'utilisateur
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getIndustry(): ?string
    {
        return $this->industry;
    }

    public function setIndustry(?string $industry): self
    {
        $this->industry = $industry;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): self
    {
        $this->experience = $experience;
        return $this;
    }

    public function getEducation(): ?string
    {
        return $this->education;
    }

    public function setEducation(?string $education): self
    {
        $this->education = $education;
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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set profile image
     * 
     * @param string|null $profileImage
     * @return $this
     */
    public function setProfileImage(?string $profileImage): self
    {
        $this->profileImage = $profileImage;
        return $this;
    }

    /**
     * Get profile image
     * 
     * @return string|null
     */
    public function getProfileImage(): ?string
    {
        // Retourner simplement la valeur stockée, sans valeur par défaut
        return $this->profileImage;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return Collection|Job[]
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): self
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs[] = $job;
            $job->setCompany($this);
        }
        return $this;
    }

    public function removeJob(Job $job): self
    {
        if ($this->jobs->removeElement($job)) {
            if ($job->getCompany() === $this) {
                $job->setCompany(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|SavedJob[]
     */
    public function getSavedJobs(): Collection
    {
        return $this->savedJobs;
    }

    public function addSavedJob(SavedJob $savedJob): self
    {
        if (!$this->savedJobs->contains($savedJob)) {
            $this->savedJobs[] = $savedJob;
            $savedJob->setUser($this);
        }
        return $this;
    }

    public function removeSavedJob(SavedJob $savedJob): self
    {
        if ($this->savedJobs->removeElement($savedJob)) {
            if ($savedJob->getUser() === $this) {
                $savedJob->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|JobApplication[]
     */
    public function getJobApplications(): Collection
    {
        return $this->jobApplications;
    }

    public function addJobApplication(JobApplication $jobApplication): self
    {
        if (!$this->jobApplications->contains($jobApplication)) {
            $this->jobApplications[] = $jobApplication;
            $jobApplication->setUser($this);
        }
        return $this;
    }

    public function removeJobApplication(JobApplication $jobApplication): self
    {
        if ($this->jobApplications->removeElement($jobApplication)) {
            if ($jobApplication->getUser() === $this) {
                $jobApplication->setUser(null);
            }
        }
        return $this;
    }

    /**
     * Helper method to get full name
     */
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     * Helper method to get profile image path with default fallback
     * This should only be used for display, not for form handling
     */
    public function getProfileImagePath(): string
    {
        return $this->profileImage ? $this->profileImage : 'img/fxchat.png';
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations[] = $conversation;
            $conversation->addParticipant($this);
        }
        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->removeElement($conversation)) {
            $conversation->removeParticipant($this);
        }
        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setSender($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }
        return $this;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setDefaultProfileImage()
    {
        // Commentez complètement cette méthode pour l'empêcher de s'exécuter
        // Ne définir l'image par défaut que lors de la création initiale
        // if ($this->getId() === null && !$this->profileImage) {
        //     $this->profileImage = 'img/fxchat.png';
        // }
    }

    // Supprimer ou commenter la méthode magique __call si elle existe
    // public function __call($method, $arguments)
    // {
    //     // ...
    // } 

    /**
     * Vérifie si l'utilisateur est approuvé
     */
    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    /**
     * Définit si l'utilisateur est approuvé
     */
    public function setIsApproved(bool $isApproved): self
    {
        $this->isApproved = $isApproved;
        return $this;
    }

    /**
     * Récupère la date de création de l'utilisateur
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création de l'utilisateur
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Récupère l'administrateur associé à cet utilisateur
     */
    public function getAdministrateur(): ?Administrateur
    {
        return $this->administrateur;
    }

    /**
     * Définit l'administrateur associé à cet utilisateur
     */
    public function setAdministrateur(?Administrateur $administrateur): self
    {
        // Définir le nouvel administrateur
        $this->administrateur = $administrateur;

        // Définir la relation inverse si nécessaire
        if ($administrateur !== null && $administrateur->getUser() !== $this) {
            $administrateur->setUser($this);
        }

        return $this;
    }

    public function getPreparationProgress(): ?PreparationProgress
    {
        return $this->preparationProgress;
    }

    public function setPreparationProgress(?PreparationProgress $preparationProgress): self
    {
        $this->preparationProgress = $preparationProgress;

        // set the owning side of the relation if necessary
        if ($preparationProgress !== null && $preparationProgress->getUser() !== $this) {
            $preparationProgress->setUser($this);
        }

        return $this;
    }
}
