<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Ce contrôleur est responsable de la gestion des erreurs liées aux fichiers.
 * Il fournit des routes de test pour simuler différents scénarios d'erreur,
 * tels que les fichiers non trouvés, les fichiers supprimés ou les accès non autorisés.
 *
 * Chaque méthode de ce contrôleur rend un template d'erreur spécifique
 * avec un message et un code d'erreur appropriés.
 */
class ErrorController extends AbstractController
{
    /**
     * Simule le cas où un fichier n'est pas trouvé à un chemin spécifié.
     *
     * Cette méthode de test est accessible via l'URL '/test/file-not-found'.
     * Elle vérifie l'existence d'un fichier à un chemin fictif et,
     * si le fichier n'existe pas, elle affiche une page d'erreur 404.
     *
     * @return Response La réponse HTTP avec la page d'erreur.
     */
    #[Route('/test/file-not-found', name: 'app_test_file_not_found')]
    public function testFileNotFound(): Response
    {
        // Définit un chemin de fichier fictif pour simuler un fichier non trouvé.
        $filePath = '/chemin/vers/fichier/inexistant.pdf';
        
        // Vérifie si le fichier n'existe pas.
        if (!file_exists($filePath)) {
            // Ajoute un message flash pour informer l'utilisateur de l'erreur.
            $this->addFlash('error', 'Le fichier demandé n\'existe pas.');
            
            // Rend le template d'erreur pour les fichiers non trouvés.
            return $this->render('error/file_not_found.html.twig', [
                'file_path' => $filePath,
                'error_code' => 404,
                'error_message' => 'Fichier non trouvé'
            ]);
        }
        
        // Cette ligne ne devrait jamais être atteinte dans ce scénario de test.
        return new Response('Ce code ne devrait jamais être atteint');
    }
    
    /**
     * Simule le cas où un fichier a été supprimé de la base de données.
     *
     * Cette méthode de test est accessible via l'URL '/test/file-deleted'.
     * Elle simule une recherche en base de données pour un fichier qui n'existe plus
     * et affiche une page d'erreur 410 (Gone).
     *
     * @return Response La réponse HTTP avec la page d'erreur.
     */
    #[Route('/test/file-deleted', name: 'app_test_file_deleted')]
    public function testFileDeleted(): Response
    {
        // Simule un identifiant de fichier qui n'existe plus.
        $fileId = 999;
        
        // Simule le résultat d'une recherche en base de données (fichier non trouvé).
        $fileExists = false;
        
        // Si le fichier n'existe pas, affiche une erreur.
        if (!$fileExists) {
            // Ajoute un message flash pour avertir l'utilisateur.
            $this->addFlash('warning', 'Le fichier a été supprimé ou déplacé.');
            
            // Rend le template d'erreur avec un code 410.
            return $this->render('error/file_not_found.html.twig', [
                'file_id' => $fileId,
                'error_code' => 410,
                'error_message' => 'Fichier supprimé'
            ]);
        }
        
        // Cette ligne ne devrait jamais être atteinte.
        return new Response('Ce code ne devrait jamais être atteint');
    }
    
    /**
     * Simule le cas où un utilisateur n'a pas les permissions pour accéder à un fichier.
     *
     * Cette méthode de test est accessible via l'URL '/test/file-no-permission'.
     * Elle simule une vérification de permissions qui échoue et affiche une
     * page d'erreur 403 (Forbidden).
     *
     * @return Response La réponse HTTP avec la page d'erreur.
     */
    #[Route('/test/file-no-permission', name: 'app_test_file_no_permission')]
    public function testFileNoPermission(): Response
    {
        // Simule l'identifiant d'un fichier protégé.
        $fileId = 123;
        
        // Simule un échec de la vérification des permissions.
        $hasPermission = false;
        
        // Si l'utilisateur n'a pas la permission, affiche une erreur.
        if (!$hasPermission) {
            // Ajoute un message flash pour indiquer un accès refusé.
            $this->addFlash('danger', 'Vous n\'avez pas les permissions nécessaires pour accéder à ce fichier.');
            
            // Rend le template d'erreur avec un code 403.
            return $this->render('error/file_not_found.html.twig', [
                'file_id' => $fileId,
                'error_code' => 403,
                'error_message' => 'Accès refusé'
            ]);
        }
        
        // Cette ligne ne devrait jamais être atteinte.
        return new Response('Ce code ne devrait jamais être atteint');
    }
}