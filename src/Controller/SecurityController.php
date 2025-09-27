<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Ce contrôleur gère l'authentification des utilisateurs, y compris la connexion et la déconnexion.
 */
class SecurityController extends AbstractController
{
    /**
     * Gère l'affichage du formulaire de connexion et les erreurs d'authentification.
     *
     * @param AuthenticationUtils $authenticationUtils L'utilitaire pour obtenir les informations d'authentification.
     * @return Response La réponse HTTP avec le formulaire de connexion.
     */
    #[Route('/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur de connexion s'il y en a une.
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Récupère le dernier nom d'utilisateur saisi par l'utilisateur.
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affiche le formulaire de connexion avec les éventuelles erreurs et le dernier nom d'utilisateur.
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     * Cette méthode peut être vide car elle est interceptée par le pare-feu de sécurité de Symfony.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide.
        // Elle est interceptée par la configuration de sécurité de Symfony (logout key).
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}