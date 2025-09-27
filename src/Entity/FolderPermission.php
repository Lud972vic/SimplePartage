<?php

namespace App\Entity;

use App\Entity\Group;
use App\Repository\FolderPermissionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente les permissions d'accès à un dossier pour un utilisateur ou un groupe.
 * Définit les droits spécifiques comme la visualisation du dossier, la visualisation des fichiers,
 * et le téléchargement des fichiers.
 */
#[ORM\Entity(repositoryClass: FolderPermissionRepository::class)]
class FolderPermission
{
    // Constantes pour le type de cible de la permission
    public const TARGET_USER = 'user';
    public const TARGET_GROUP = 'group';

    /**
     * @var int|null L'identifiant unique de la permission.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Folder|null Le dossier auquel cette permission s'applique.
     */
    #[ORM\ManyToOne(inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Folder $folder = null;

    /**
     * @var string|null Le type de cible (utilisateur ou groupe).
     */
    #[ORM\Column(length: 10)]
    private ?string $targetType = null;

    /**
     * @var int|null L'ID de la cible (ID de l'utilisateur ou du groupe).
     */
    #[ORM\Column]
    private ?int $targetId = null;
    
    /**
     * @var Group|null Le groupe cible, si la permission s'applique à un groupe.
     */
    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Group $group = null;

    /**
     * @var bool|null Autorise ou non la visualisation du dossier.
     */
    #[ORM\Column]
    private ?bool $canViewFolder = false;

    /**
     * @var bool|null Autorise ou non la visualisation des fichiers dans le dossier.
     */
    #[ORM\Column]
    private ?bool $canViewFiles = false;

    /**
     * @var bool|null Autorise ou non le téléchargement des fichiers du dossier.
     */
    #[ORM\Column]
    private ?bool $canDownloadFiles = false;

    /**
     * Obtient l'ID de la permission.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Obtient le dossier associé.
     *
     * @return Folder|null
     */
    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    /**
     * Définit le dossier associé.
     *
     * @param Folder|null $folder
     * @return static
     */
    public function setFolder(?Folder $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Obtient le type de cible.
     *
     * @return string|null
     */
    public function getTargetType(): ?string
    {
        return $this->targetType;
    }

    /**
     * Définit le type de cible.
     *
     * @param string $targetType
     * @return static
     * @throws \InvalidArgumentException Si le type de cible est invalide.
     */
    public function setTargetType(string $targetType): static
    {
        if (!in_array($targetType, [self::TARGET_USER, self::TARGET_GROUP])) {
            throw new \InvalidArgumentException('Invalid target type');
        }
        
        $this->targetType = $targetType;

        return $this;
    }

    /**
     * Obtient l'ID de la cible.
     *
     * @return int|null
     */
    public function getTargetId(): ?int
    {
        return $this->targetId;
    }

    /**
     * Définit l'ID de la cible.
     *
     * @param int $targetId
     * @return static
     */
    public function setTargetId(int $targetId): static
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * Vérifie si la visualisation du dossier est autorisée.
     *
     * @return bool|null
     */
    public function isCanViewFolder(): ?bool
    {
        return $this->canViewFolder;
    }

    /**
     * Définit la permission de visualiser le dossier.
     *
     * @param bool $canViewFolder
     * @return static
     */
    public function setCanViewFolder(bool $canViewFolder): static
    {
        $this->canViewFolder = $canViewFolder;

        return $this;
    }

    /**
     * Vérifie si la visualisation des fichiers est autorisée.
     *
     * @return bool|null
     */
    public function isCanViewFiles(): ?bool
    {
        return $this->canViewFiles;
    }

    /**
     * Définit la permission de visualiser les fichiers.
     *
     * @param bool $canViewFiles
     * @return static
     */
    public function setCanViewFiles(bool $canViewFiles): static
    {
        $this->canViewFiles = $canViewFiles;

        return $this;
    }

    /**
     * Vérifie si le téléchargement des fichiers est autorisé.
     *
     * @return bool|null
     */
    public function isCanDownloadFiles(): ?bool
    {
        return $this->canDownloadFiles;
    }

    /**
     * Définit la permission de télécharger les fichiers.
     *
     * @param bool $canDownloadFiles
     * @return static
     */
    public function setCanDownloadFiles(bool $canDownloadFiles): static
    {
        $this->canDownloadFiles = $canDownloadFiles;

        return $this;
    }
    
    /**
     * Obtient le groupe cible.
     *
     * @return Group|null
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }
    
    /**
     * Définit le groupe cible.
     * Si un groupe est défini, le type de cible et l'ID de la cible sont automatiquement mis à jour.
     *
     * @param Group|null $group
     * @return static
     */
    public function setGroup(?Group $group): static
    {
        $this->group = $group;
        
        if ($group !== null) {
            $this->setTargetType(self::TARGET_GROUP);
            $this->setTargetId($group->getId());
        }
        
        return $this;
    }
}