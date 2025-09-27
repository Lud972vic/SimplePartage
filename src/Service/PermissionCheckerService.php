<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\Folder;
use App\Entity\FolderPermission;
use App\Entity\User;
use App\Repository\FolderPermissionRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PermissionCheckerService
 *
 * Ce service centralise la logique de vérification des permissions pour les dossiers et les fichiers.
 * Il détermine si un utilisateur peut voir un dossier, voir les fichiers qu'il contient, ou télécharger des fichiers,
 * en tenant compte des permissions directes, des permissions de groupe et de l'héritage.
 */
class PermissionCheckerService
{
    private EntityManagerInterface $entityManager;
    private FolderPermissionRepository $permissionRepository;

    /**
     * PermissionCheckerService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param FolderPermissionRepository $permissionRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FolderPermissionRepository $permissionRepository
    ) {
        $this->entityManager = $entityManager;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Vérifie si un utilisateur peut voir un dossier.
     *
     * @param User $user L'utilisateur à vérifier.
     * @param Folder $folder Le dossier à vérifier.
     * @return bool
     */
    public function canViewFolder(User $user, Folder $folder): bool
    {
        // Admin can do anything
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Check direct user permissions first (highest priority)
        $userPermission = $this->permissionRepository->findUserPermissions($folder, $user);
        if ($userPermission && $userPermission->isCanViewFolder()) {
            return true;
        }

        // Check group permissions
        $groupIds = $user->getGroups()->map(fn($group) => $group->getId())->toArray();
        $groupPermissions = $this->permissionRepository->findGroupPermissions($folder, $groupIds);
        
        foreach ($groupPermissions as $permission) {
            if ($permission->isCanViewFolder()) {
                return true;
            }
        }

        // If the folder has specific permissions, don't inherit
        if ($this->permissionRepository->countByFolder($folder) > 0) {
            return false;
        }

        // Check parent folder permissions if inheritance is enabled
        if ($folder->isInheritPermissions() && $folder->getParent()) {
            return $this->canViewFolder($user, $folder->getParent());
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur peut voir les fichiers dans un dossier.
     *
     * @param User $user L'utilisateur à vérifier.
     * @param Folder $folder Le dossier à vérifier.
     * @return bool
     */
    public function canViewFiles(User $user, Folder $folder): bool
    {
        // Admin can do anything
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Check direct user permissions first (highest priority)
        $userPermission = $this->permissionRepository->findUserPermissions($folder, $user);
        if ($userPermission && $userPermission->isCanViewFiles()) {
            return true;
        }

        // Check group permissions
        $groupIds = $user->getGroups()->map(fn($group) => $group->getId())->toArray();
        $groupPermissions = $this->permissionRepository->findGroupPermissions($folder, $groupIds);
        
        foreach ($groupPermissions as $permission) {
            if ($permission->isCanViewFiles()) {
                return true;
            }
        }

        // If the folder has specific permissions, don't inherit
        if ($this->permissionRepository->countByFolder($folder) > 0) {
            return false;
        }

        // Check parent folder permissions if inheritance is enabled
        if ($folder->isInheritPermissions() && $folder->getParent()) {
            return $this->canViewFiles($user, $folder->getParent());
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur peut télécharger des fichiers depuis un dossier.
     *
     * @param User $user L'utilisateur à vérifier.
     * @param Folder $folder Le dossier à vérifier.
     * @return bool
     */
    public function canDownloadFiles(User $user, Folder $folder): bool
    {
        // Admin can do anything
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Check direct user permissions first (highest priority)
        $userPermission = $this->permissionRepository->findUserPermissions($folder, $user);
        if ($userPermission && $userPermission->isCanDownloadFiles()) {
            return true;
        }

        // Check group permissions
        $groupIds = $user->getGroups()->map(fn($group) => $group->getId())->toArray();
        $groupPermissions = $this->permissionRepository->findGroupPermissions($folder, $groupIds);
        
        foreach ($groupPermissions as $permission) {
            if ($permission->isCanDownloadFiles()) {
                return true;
            }
        }

        // If the folder has specific permissions, don't inherit
        if ($this->permissionRepository->countByFolder($folder) > 0) {
            return false;
        }

        // Check parent folder permissions if inheritance is enabled
        if ($folder->isInheritPermissions() && $folder->getParent()) {
            return $this->canDownloadFiles($user, $folder->getParent());
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur peut télécharger un fichier spécifique.
     *
     * @param User $user L'utilisateur à vérifier.
     * @param File $file Le fichier à vérifier.
     * @return bool
     */
    public function canDownloadFile(User $user, File $file): bool
    {
        return $this->canDownloadFiles($user, $file->getFolder());
    }

    /**
     * Récupère tous les dossiers qu'un utilisateur peut voir.
     *
     * @param User $user L'utilisateur.
     * @return array La liste des dossiers accessibles.
     */
    public function getAccessibleFolders(User $user): array
    {
        // For admin, get all folders
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->entityManager->getRepository(Folder::class)->findAll();
        }

        $allFolders = $this->entityManager->getRepository(Folder::class)->findAll();
        $accessibleFolders = [];

        foreach ($allFolders as $folder) {
            if ($this->canViewFolder($user, $folder)) {
                $accessibleFolders[] = $folder;
            }
        }

        return $accessibleFolders;
    }

    /**
     * Récupère tous les fichiers qu'un utilisateur peut voir dans un dossier.
     *
     * @param User $user L'utilisateur.
     * @param Folder $folder Le dossier.
     * @return array La liste des fichiers accessibles.
     */
    public function getAccessibleFiles(User $user, Folder $folder): array
    {
        // Check if user can view files in this folder
        if (!$this->canViewFiles($user, $folder)) {
            return [];
        }

        return $this->entityManager->getRepository(File::class)->findByFolder($folder);
    }

    /**
     * Récupère tous les sous-dossiers qu'un utilisateur peut voir dans un dossier.
     *
     * @param User $user L'utilisateur.
     * @param Folder $folder Le dossier parent.
     * @return array La liste des sous-dossiers accessibles.
     */
    public function getAccessibleSubfolders(User $user, Folder $folder): array
    {
        $subfolders = $folder->getChildren()->toArray();
        $accessibleSubfolders = [];

        foreach ($subfolders as $subfolder) {
            if ($this->canViewFolder($user, $subfolder)) {
                $accessibleSubfolders[] = $subfolder;
            }
        }

        return $accessibleSubfolders;
    }
}