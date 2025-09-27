<?php

namespace App\Security;

use App\Entity\File;
use App\Entity\User;
use App\Service\PermissionCheckerService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class FileVoter
 *
 * Ce Voter Symfony détermine si un utilisateur a la permission d'effectuer des actions
 * spécifiques sur une entité File, comme la voir ou la télécharger.
 */
class FileVoter extends Voter
{
    // Permissions que ce voter peut vérifier
    const VIEW = 'view';
    const DOWNLOAD = 'download';

    /**
     * @var PermissionCheckerService
     */
    private PermissionCheckerService $permissionChecker;

    /**
     * FileVoter constructor.
     *
     * @param PermissionCheckerService $permissionChecker Le service pour vérifier les permissions.
     */
    public function __construct(PermissionCheckerService $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * Détermine si ce voter supporte l'attribut et le sujet donnés.
     *
     * @param string $attribute La permission à vérifier.
     * @param mixed $subject Le sujet (doit être une entité File).
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::DOWNLOAD])) {
            return false;
        }

        if (!$subject instanceof File) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie la permission pour l'attribut et le sujet donnés.
     *
     * @param string $attribute La permission à vérifier.
     * @param mixed $subject L'entité File.
     * @param TokenInterface $token Le token de sécurité.
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var File $file */
        $file = $subject;
        $folder = $file->getFolder();

        return match($attribute) {
            self::VIEW => $this->permissionChecker->canViewFiles($user, $folder),
            self::DOWNLOAD => $this->permissionChecker->canDownloadFile($user, $file),
            default => false,
        };
    }
}