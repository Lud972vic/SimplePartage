<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un groupe d'utilisateurs.
 * Un groupe est une collection d'utilisateurs, utilisée pour gérer les permissions en masse.
 */
#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    /**
     * @var int|null L'identifiant unique du groupe.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null Le nom du groupe.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, User> La collection des utilisateurs membres de ce groupe.
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    private Collection $users;

    /**
     * Constructeur de l'entité Group.
     * Initialise la collection d'utilisateurs.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * Obtient l'ID du groupe.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Obtient le nom du groupe.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom du groupe.
     *
     * @param string $name
     * @return static
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Obtient la collection des utilisateurs du groupe.
     *
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Ajoute un utilisateur au groupe.
     *
     * @param User $user
     * @return static
     */
    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addGroup($this);
        }

        return $this;
    }

    /**
     * Supprime un utilisateur du groupe.
     *
     * @param User $user
     * @return static
     */
    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeGroup($this);
        }

        return $this;
    }
}