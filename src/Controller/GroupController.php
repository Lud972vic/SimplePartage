<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ce contrôleur gère les opérations CRUD (Créer, Lire, Mettre à jour, Supprimer) pour les groupes d'utilisateurs.
 * Il est accessible via la route préfixée '/admin/group' et est destiné aux administrateurs.
 */
#[Route('/admin/ajouter/groupe')]
class GroupController extends AbstractController
{
    /**
     * Affiche la liste de tous les groupes.
     *
     * @param GroupRepository $groupRepository Le repository pour accéder aux données des groupes.
     * @return Response La réponse HTTP avec la vue de la liste des groupes.
     */
    #[Route('/', name: 'app_group_index', methods: ['GET'])]
    public function index(GroupRepository $groupRepository): Response
    {
        // Affiche la vue avec tous les groupes récupérés depuis la base de données.
        return $this->render('group/index.html.twig', [
            'groups' => $groupRepository->findAll(),
        ]);
    }

    /**
     * Gère la création d'un nouveau groupe.
     *
     * @param Request                $request         La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP.
     */
    #[Route('/nouveau', name: 'app_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée une nouvelle instance de l'entité Group.
        $group = new Group();
        // Crée le formulaire de création de groupe.
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide.
        if ($form->isSubmitted() && $form->isValid()) {
            // Persiste le nouveau groupe en base de données.
            $entityManager->persist($group);
            $entityManager->flush();

            // Ajoute un message de succès.
            $this->addFlash('success', 'Le groupe a été créé avec succès.');
            // Redirige vers la liste des groupes.
            return $this->redirectToRoute('app_group_index');
        }

        // Affiche le formulaire de création de groupe.
        return $this->render('group/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche les détails d'un groupe spécifique.
     *
     * @param Group $group Le groupe à afficher.
     * @return Response La réponse HTTP avec la vue des détails du groupe.
     */
    #[Route('/{id}', name: 'app_group_show', methods: ['GET'])]
    public function show(Group $group): Response
    {
        // Affiche la vue avec les informations du groupe.
        return $this->render('group/show.html.twig', [
            'group' => $group,
        ]);
    }

    /**
     * Gère la modification d'un groupe existant.
     *
     * @param Request                $request         La requête HTTP.
     * @param Group                  $group           Le groupe à modifier.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP.
     */
    #[Route('/{id}/modifier', name: 'app_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        // Crée le formulaire de modification de groupe.
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide.
        if ($form->isSubmitted() && $form->isValid()) {
            // Met à jour le groupe en base de données.
            $entityManager->flush();

            // Ajoute un message de succès.
            $this->addFlash('success', 'Le groupe a été mis à jour avec succès.');
            // Redirige vers la liste des groupes.
            return $this->redirectToRoute('app_group_index');
        }

        // Affiche le formulaire de modification de groupe.
        return $this->render('group/edit.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }

    /**
     * Gère la suppression d'un groupe.
     *
     * @param Request                $request         La requête HTTP.
     * @param Group                  $group           Le groupe à supprimer.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP.
     */
    #[Route('/{id}', name: 'app_group_delete', methods: ['POST'])]
    public function delete(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        // Vérifie la validité du jeton CSRF pour des raisons de sécurité.
        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {
            // Supprime le groupe de la base de données.
            $entityManager->remove($group);
            $entityManager->flush();
            
            // Ajoute un message de succès.
            $this->addFlash('success', 'Le groupe a été supprimé avec succès.');
        }

        // Redirige vers la liste des groupes.
        return $this->redirectToRoute('app_group_index');
    }
}