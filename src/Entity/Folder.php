<?php

namespace App\Entity;

use App\Repository\FolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un dossier dans le système de fichiers virtuel.
 * Un dossier peut contenir des sous-dossiers et des fichiers, et peut avoir des permissions associées.
 */
#[ORM\Entity(repositoryClass: FolderRepository::class)]
class Folder
{
    /**
     * @var int|null L'identifiant unique du dossier.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null Le nom du dossier.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Folder|null Le dossier parent. La suppression d'un dossier parent entraîne la suppression de ses enfants.
     */
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    /**
     * @var Collection<int, Folder> Les sous-dossiers. La persistance et la suppression sont en cascade.
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['persist', 'remove'])]
    private Collection $children;

    /**
     * @var \DateTimeInterface|null La date de création du dossier.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, File> Les fichiers contenus dans ce dossier. La persistance et la suppression sont en cascade.
     */
    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: File::class, cascade: ['persist', 'remove'])]
    private Collection $files;

    /**
     * @var Collection<int, FolderPermission> Les permissions associées à ce dossier. La persistance et la suppression sont en cascade.
     */
    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: FolderPermission::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $permissions;

    /**
     * @var bool|null Indique si le dossier hérite des permissions de son parent.
     */
    #[ORM\Column]
    private ?bool $inheritPermissions = true;

    /**
     * Constructeur de l'entité Folder.
     * Initialise les collections et la date de création.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * Obtient l'ID du dossier.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Obtient le nom du dossier.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom du dossier.
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
     * Obtient le dossier parent.
     *
     * @return Folder|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * Définit le dossier parent.
     *
     * @param Folder|null $parent
     * @return static
     */
    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Obtient la collection des sous-dossiers.
     *
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Ajoute un sous-dossier.
     *
     * @param Folder $child
     * @return static
     */
    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * Supprime un sous-dossier.
     *
     * @param Folder $child
     * @return static
     */
    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            // met le côté propriétaire à null (sauf s'il a déjà été modifié)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * Obtient la date de création.
     *
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création.
     *
     * @param \DateTimeInterface $createdAt
     * @return static
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Obtient la collection des fichiers.
     *
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * Ajoute un fichier.
     *
     * @param File $file
     * @return static
     */
    public function addFile(File $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setFolder($this);
        }

        return $this;
    }

    /**
     * Supprime un fichier.
     *
     * @param File $file
     * @return static
     */
    public function removeFile(File $file): static
    {
        if ($this->files->removeElement($file)) {
            // met le côté propriétaire à null (sauf s'il a déjà été modifié)
            if ($file->getFolder() === $this) {
                $file->setFolder(null);
            }
        }

        return $this;
    }

    /**
     * Obtient la collection des permissions.
     *
     * @return Collection<int, FolderPermission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * Ajoute une permission.
     *
     * @param FolderPermission $permission
     * @return static
     */
    public function addPermission(FolderPermission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
            $permission->setFolder($this);
        }

        return $this;
    }

    /**
     * Supprime une permission.
     *
     * @param FolderPermission $permission
     * @return static
     */
    public function removePermission(FolderPermission $permission): static
    {
        if ($this->permissions->removeElement($permission)) {
            // met le côté propriétaire à null (sauf s'il a déjà été modifié)
            if ($permission->getFolder() === $this) {
                $permission->setFolder(null);
            }
        }

        return $this;
    }

    /**
     * Vérifie si le dossier hérite des permissions.
     *
     * @return bool|null
     */
    public function isInheritPermissions(): ?bool
    {
        return $this->inheritPermissions;
    }

    /**
     * Définit si le dossier hérite des permissions.
     *
     * @param bool $inheritPermissions
     * @return static
     */
    public function setInheritPermissions(bool $inheritPermissions): static
    {
        $this->inheritPermissions = $inheritPermissions;

        return $this;
    }

    /**
     * Calcule et retourne le chemin complet du dossier.
     *
     * @return string
     */
    public function getPath(): string
    {
        if ($this->getParent()) {
            return $this->getParent()->getPath() . '/' . $this->getName();
        }

        return $this->getName();
    }
}