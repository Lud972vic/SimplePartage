<?php

namespace App\Security;

use App\Entity\Folder;
use App\Entity\User;
use App\Service\PermissionCheckerService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class FolderVoter
 *
 * Cette classe est un Voter Symfony qui détermine si un utilisateur a la permission d'effectuer
 * des actions spécifiques sur une entité Folder. Elle centralise la logique de permission pour
 * la visualisation des dossiers, la visualisation des fichiers dans les dossiers et le téléchargement des fichiers.
 */
class FolderVoter extends Voter
{
    // Définit les permissions que ce voter peut vérifier
    const VIEW = 'view';
    const VIEW_FILES = 'view_files';
    const DOWNLOAD_FILES = 'download_files';

    /**
     * @var PermissionCheckerService
     */
    private PermissionCheckerService $permissionChecker;

    /**
     * FolderVoter constructor.
     *
     * @param PermissionCheckerService $permissionChecker Le service utilisé pour vérifier les permissions.
     */
    public function __construct(PermissionCheckerService $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * Détermine si ce voter prend en charge l'attribut et le sujet donnés.
     *
     * @param string $attribute La permission à vérifier (par exemple, 'view', 'view_files').
     * @param mixed $subject Le sujet sur lequel vérifier les permissions (doit être une entité Folder).
     * @return bool True si ce voter prend en charge l'attribut et le sujet, sinon false.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::VIEW_FILES, self::DOWNLOAD_FILES])) {
            return false;
        }

        if (!$subject instanceof Folder) {
            return false;
        }

        return true;
    }

    /**
     * Effectue la vérification de la permission.
     *
     * @param string $attribute La permission à vérifier.
     * @param mixed $subject L'entité Folder sur laquelle vérifier les permissions.
     * @param TokenInterface $token Le jeton de sécurité contenant l'utilisateur.
     * @return bool True si l'utilisateur a la permission, sinon false.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Folder $folder */
        $folder = $subject;

        return match($attribute) {
            self::VIEW => $this->permissionChecker->canViewFolder($user, $folder),
            self::VIEW_FILES => $this->permissionChecker->canViewFiles($user, $folder),
            self::DOWNLOAD_FILES => $this->permissionChecker->canDownloadFiles($user, $folder),
            default => false,
        };
    }
}