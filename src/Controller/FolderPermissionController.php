<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Entity\FolderPermission;
use App\Entity\Group;
use App\Repository\FolderPermissionRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Ce contrôleur gère les permissions d'accès aux dossiers pour les groupes d'utilisateurs.
 * Il est accessible uniquement par les administrateurs.
 */
#[Route('/ajouter/permission')]
class FolderPermissionController extends AbstractController
{
    /**
     * Affiche la liste des permissions pour un dossier spécifique.
     *
     * @param Folder                     $folder               Le dossier concerné.
     * @param FolderPermissionRepository $permissionRepository Le repository pour accéder aux permissions.
     * @return Response La réponse HTTP avec la vue des permissions.
     */
    #[Route('/dossier-numero/{id}', name: 'app_folder_permission_index')]
    public function index(Folder $folder, FolderPermissionRepository $permissionRepository): Response
    {
        // Récupère toutes les permissions associées à ce dossier.
        $permissions = $permissionRepository->findBy(['folder' => $folder]);
        
        // Affiche la vue avec la liste des permissions.
        return $this->render('folder_permission/index.html.twig', [
            'folder' => $folder,
            'permissions' => $permissions,
        ]);
    }
    
    /**
     * Gère la création d'une nouvelle permission pour un dossier.
     *
     * @param Request                $request         La requête HTTP.
     * @param Folder                 $folder          Le dossier pour lequel ajouter une permission.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @param GroupRepository        $groupRepository Le repository pour accéder aux groupes.
     * @return Response La réponse HTTP.
     */
    #[Route('/dossier/{id}/choix', name: 'app_folder_permission_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Folder $folder, EntityManagerInterface $entityManager, GroupRepository $groupRepository): Response
    {
        // Crée une nouvelle instance de l'entité FolderPermission.
        $permission = new FolderPermission();
        $permission->setFolder($folder);
        
        // Crée le formulaire pour ajouter une nouvelle permission.
        $form = $this->createFormBuilder($permission)
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'label' => 'Groupe',
                'attr' => ['class' => 'form-control'],
                'by_reference' => false
            ])
            ->add('canViewFolder', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Peut voir le dossier',
                'expanded' => true,
            ])
            ->add('canViewFiles', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Peut voir les fichiers',
                'expanded' => true,
            ])
            ->add('canDownloadFiles', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Peut télécharger les fichiers',
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
            ->getForm();
        
        $form->handleRequest($request);
        
        // Si le formulaire est soumis et valide.
        if ($form->isSubmitted() && $form->isValid()) {
            // Persiste la nouvelle permission en base de données.
            $entityManager->persist($permission);
            $entityManager->flush();
            
            // Ajoute un message de succès.
            $this->addFlash('success', 'La permission a été ajoutée avec succès.');
            // Redirige vers la liste des permissions du dossier.
            return $this->redirectToRoute('app_folder_permission_index', ['id' => $folder->getId()]);
        }
        
        // Affiche le formulaire de création de permission.
        return $this->render('folder_permission/new.html.twig', [
            'form' => $form->createView(),
            'folder' => $folder,
        ]);
    }
    
    /**
     * Gère la modification d'une permission existante.
     *
     * @param Request                $request       La requête HTTP.
     * @param FolderPermission       $permission    La permission à modifier.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP.
     */
    #[Route('/{id}/modifier', name: 'app_folder_permission_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FolderPermission $permission, EntityManagerInterface $entityManager): Response
    {
        // Crée le formulaire pour modifier la permission.
        $form = $this->createFormBuilder($permission)
            ->add('canViewFolder', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Peut voir le dossier',
                'expanded' => true,
            ])
            ->add('canViewFiles', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Peut voir les fichiers',
                'expanded' => true,
            ])
            ->add('canDownloadFiles', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label' => 'Peut télécharger les fichiers',
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
            ->getForm();
        
        $form->handleRequest($request);
        
        // Si le formulaire est soumis et valide.
        if ($form->isSubmitted() && $form->isValid()) {
            // Met à jour la permission en base de données.
            $entityManager->flush();
            
            // Ajoute un message de succès.
            $this->addFlash('success', 'La permission a été mise à jour avec succès.');
            // Redirige vers la liste des permissions du dossier.
            return $this->redirectToRoute('app_folder_permission_index', ['id' => $permission->getFolder()->getId()]);
        }
        
        // Affiche le formulaire de modification de permission.
        return $this->render('folder_permission/edit.html.twig', [
            'form' => $form->createView(),
            'permission' => $permission,
        ]);
    }
    
    /**
     * Gère la suppression d'une permission.
     *
     * @param Request                $request       La requête HTTP.
     * @param FolderPermission       $permission    La permission à supprimer.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP.
     */
    #[Route('/{id}', name: 'app_folder_permission_delete', methods: ['POST'])]
    public function delete(Request $request, FolderPermission $permission, EntityManagerInterface $entityManager): Response
    {
        // Récupère l'ID du dossier pour la redirection.
        $folderId = $permission->getFolder()->getId();
        
        // Vérifie la validité du jeton CSRF pour des raisons de sécurité.
        if ($this->isCsrfTokenValid('delete'.$permission->getId(), $request->request->get('_token'))) {
            // Supprime la permission de la base de données.
            $entityManager->remove($permission);
            $entityManager->flush();
            
            // Ajoute un message de succès.
            $this->addFlash('success', 'La permission a été supprimée avec succès.');
        }
        
        // Redirige vers la liste des permissions du dossier.
        return $this->redirectToRoute('app_folder_permission_index', ['id' => $folderId]);
    }
}