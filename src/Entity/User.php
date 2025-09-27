<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Représente un utilisateur de l'application.
 * Implémente UserInterface et PasswordAuthenticatedUserInterface pour l'intégration avec la sécurité de Symfony.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int|null L'identifiant unique de l'utilisateur.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null L'adresse email de l'utilisateur, utilisée comme identifiant.
     */
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * @var array Les rôles de l'utilisateur (par exemple, ROLE_USER, ROLE_ADMIN).
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null Le mot de passe hashé de l'utilisateur.
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Group> Les groupes auxquels l'utilisateur appartient.
     */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users')]
    private Collection $groups;

    /**
     * @var string|null Le prénom de l'utilisateur.
     */
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    /**
     * @var string|null Le nom de famille de l'utilisateur.
     */
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    /**
     * Constructeur de l'entité User.
     * Initialise la collection de groupes.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * Obtient l'ID de l'utilisateur.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Obtient l'email de l'utilisateur.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'email de l'utilisateur.
     *
     * @param string $email
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Un identifiant visuel qui représente cet utilisateur.
     *
     * @see UserInterface
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Retourne les rôles de l'utilisateur.
     * Garantit que chaque utilisateur a au moins le rôle ROLE_USER.
     *
     * @see UserInterface
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Définit les rôles de l'utilisateur.
     *
     * @param array $roles
     * @return static
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Retourne le mot de passe hashé.
     *
     * @see PasswordAuthenticatedUserInterface
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe hashé.
     *
     * @param string $password
     * @return static
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Efface les informations sensibles temporaires de l'utilisateur.
     *
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Obtient la collection des groupes de l'utilisateur.
     *
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * Ajoute un groupe à l'utilisateur.
     *
     * @param Group $group
     * @return static
     */
    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * Supprime un groupe de l'utilisateur.
     *
     * @param Group $group
     * @return static
     */
    public function removeGroup(Group $group): static
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * Obtient le prénom de l'utilisateur.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Définit le prénom de l'utilisateur.
     *
     * @param string $firstName
     * @return static
     */
    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Obtient le nom de famille de l'utilisateur.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Définit le nom de famille de l'utilisateur.
     *
     * @param string $lastName
     * @return static
     */
    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Obtient le nom complet de l'utilisateur.
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}