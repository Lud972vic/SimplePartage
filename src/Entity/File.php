<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un fichier dans le système.
 * Chaque fichier est associé à un dossier, un utilisateur qui l'a téléversé,
 * et contient des métadonnées comme le nom, le chemin, la date de téléversement, etc.
 */
#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    /**
     * @var int|null L'identifiant unique du fichier.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null Le nom original du fichier.
     */
    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    /**
     * @var string|null Le chemin de stockage du fichier sur le serveur.
     */
    #[ORM\Column(length: 255)]
    private ?string $path = null;

    /**
     * @var Folder|null Le dossier parent de ce fichier.
     */
    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Folder $folder = null;

    /**
     * @var \DateTimeInterface|null La date et l'heure du téléversement.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploadedAt = null;

    /**
     * @var string|null Le type MIME du fichier.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mimeType = null;

    /**
     * @var int|null La taille du fichier en octets.
     */
    #[ORM\Column]
    private ?int $size = null;

    /**
     * @var User|null L'utilisateur qui a téléversé le fichier.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $uploadedBy = null;

    /**
     * Constructeur de l'entité File.
     * Initialise la date de téléversement à la date et heure actuelles.
     */
    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    /**
     * Obtient l'ID du fichier.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Obtient le nom du fichier.
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Définit le nom du fichier.
     *
     * @param string $filename
     * @return static
     */
    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Obtient le chemin du fichier.
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Définit le chemin du fichier.
     *
     * @param string $path
     * @return static
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Obtient le dossier parent.
     *
     * @return Folder|null
     */
    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    /**
     * Définit le dossier parent.
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
     * Obtient la date de téléversement.
     *
     * @return \DateTimeInterface|null
     */
    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    /**
     * Définit la date de téléversement.
     *
     * @param \DateTimeInterface $uploadedAt
     * @return static
     */
    public function setUploadedAt(\DateTimeInterface $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    /**
     * Obtient le type MIME.
     *
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * Définit le type MIME.
     *
     * @param string|null $mimeType
     * @return static
     */
    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Obtient la taille du fichier.
     *
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Définit la taille du fichier.
     *
     * @param int $size
     * @return static
     */
    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Obtient l'utilisateur qui a téléversé le fichier.
     *
     * @return User|null
     */
    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    /**
     * Définit l'utilisateur qui a téléversé le fichier.
     *
     * @param User|null $uploadedBy
     * @return static
     */
    public function setUploadedBy(?User $uploadedBy): static
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }

    /**
     * Obtient le chemin complet du fichier, incluant le chemin du dossier parent.
     *
     * @return string
     */
    public function getFullPath(): string
    {
        return $this->folder->getPath() . '/' . $this->filename;
    }

    /**
     * Obtient la taille du fichier formatée pour l'affichage (par exemple, en Ko, Mo, Go).
     *
     * @return string
     */
    public function getFormattedSize(): string
    {
        $size = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }
}