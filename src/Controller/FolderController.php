<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Form\FolderType;
use App\Repository\FolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PermissionCheckerService;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ce contrôleur gère toutes les actions liées aux dossiers : affichage, création, suppression.
 * Il est préfixé par la route '/folders' et nécessite une authentification pour la plupart des actions.
 */
#[Route('/les-dossiers')]
class FolderController extends AbstractController
{
    /**
     * @var FolderRepository Le repository pour accéder aux données des dossiers.
     */
    private FolderRepository $folderRepository;

    /**
     * @var PermissionCheckerService Le service pour vérifier les permissions des utilisateurs.
     */
    private PermissionCheckerService $permissionChecker;

    /**
     * Constructeur du contrôleur.
     *
     * @param FolderRepository       $folderRepository  Le repository des dossiers.
     * @param PermissionCheckerService $permissionChecker Le service de vérification des permissions.
     */
    public function __construct(
        FolderRepository $folderRepository,
        PermissionCheckerService $permissionChecker
    ) {
        $this->folderRepository = $folderRepository;
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * Affiche la liste des dossiers accessibles par l'utilisateur connecté.
     *
     * @return Response La réponse HTTP avec la vue des dossiers.
     */
    #[Route('/', name: 'app_folder_index')]
    public function index(): Response
    {
        // Récupère l'utilisateur actuellement connecté.
        $user = $this->getUser();
        // Récupère la liste des dossiers auxquels l'utilisateur a accès.
        $accessibleFolders = $this->permissionChecker->getAccessibleFolders($user);

        // Calcule le nombre total de dossiers et de fichiers accessibles.
        $totalFolderCount = count($accessibleFolders);
        $totalFileCount = 0;
        foreach ($accessibleFolders as $folder) {
            $totalFileCount += count($this->permissionChecker->getAccessibleFiles($user, $folder));
        }

        // Filtre les dossiers pour n'afficher que les dossiers racine (ceux qui n'ont pas de parent).
        $rootFolders = array_filter($accessibleFolders, function(Folder $folder) {
            return $folder->getParent() === null;
        });

        // Affiche la vue avec la liste des dossiers racine et les totaux.
        return $this->render('folder/index.html.twig', [
            'folders' => $rootFolders,
            'totalFolderCount' => $totalFolderCount,
            'totalFileCount' => $totalFileCount,
        ]);
    }

    /**
     * Gère la création d'un nouveau dossier.
     * Seuls les administrateurs peuvent créer des dossiers.
     *
     * @param Request                $request         La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP.
     */
    #[Route('/nouveau/dossier', name: 'app_folder_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée une nouvelle instance de l'entité Folder.
        $folder = new Folder();
        // Crée le formulaire de création de dossier.
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide.
        if ($form->isSubmitted() && $form->isValid()) {
            // Définit la date de création du dossier.
            $folder->setCreatedAt(new \DateTime());

            // Récupère l'ID du dossier parent depuis la requête.
            $parentFolderId = $request->request->get('parent_folder_id');
            if ($parentFolderId) {
                // Recherche le dossier parent en base de données.
                $parentFolder = $entityManager->getRepository(Folder::class)->find($parentFolderId);
                if ($parentFolder) {
                    // Associe le dossier parent au nouveau dossier.
                    $folder->setParent($parentFolder);
                }
            }

            // Persiste le nouveau dossier en base de données.
            $entityManager->persist($folder);
            $entityManager->flush();

            // Ajoute un message de succès.
            $this->addFlash('success', 'Dossier créé avec succès.');

            // Redirige vers la liste des dossiers.
            return $this->redirectToRoute('app_folder_index');
        }

        // Affiche le formulaire de création de dossier.
        return $this->render('folder/new.html.twig', [
            'folder' => $folder,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche le contenu d'un dossier spécifique.
     * L'utilisateur doit avoir le rôle ROLE_USER et les permissions nécessaires pour voir le dossier.
     *
     * @param Folder                   $folder            Le dossier à afficher.
     * @param PermissionCheckerService $permissionChecker Le service de vérification des permissions.
     * @return Response La réponse HTTP.
     */
    #[Route('/{id}', name: 'app_folder_show', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function show(Folder $folder, PermissionCheckerService $permissionChecker): Response
    {
        // Vérifie si l'utilisateur a le droit de voir le dossier (défini dans FolderVoter).
        $this->denyAccessUnlessGranted('view', $folder);

        // Récupère l'utilisateur connecté.
        $user = $this->getUser();
        
        // Vérification manuelle supplémentaire des permissions de vue.
        if (!$this->permissionChecker->canViewFolder($user, $folder)) {
            $this->addFlash('warning', 'Vous n\'avez pas accès à ce dossier.');
            return $this->redirectToRoute('app_folder_index');
        }
        
        // Vérifie si l'utilisateur peut voir et télécharger les fichiers du dossier.
        $canViewFiles = $this->permissionChecker->canViewFiles($user, $folder);
        $canDownloadFiles = $this->permissionChecker->canDownloadFiles($user, $folder);
        
        // Récupère les sous-dossiers accessibles par l'utilisateur.
        $subfolders = $this->permissionChecker->getAccessibleSubfolders($user, $folder);
        
        // Récupère les fichiers si l'utilisateur a la permission de les voir.
        $files = $canViewFiles ? $this->permissionChecker->getAccessibleFiles($user, $folder) : [];

        // Affiche la vue du dossier avec ses sous-dossiers et fichiers.
        return $this->render('folder/show.html.twig', [
            'folder' => $folder,
            'subfolders' => $subfolders,
            'files' => $files,
            'canViewFiles' => $canViewFiles,
            'canDownloadFiles' => $canDownloadFiles,
        ]);
    }

    /**
     * Gère la suppression d'un dossier.
     * Seuls les administrateurs peuvent supprimer des dossiers.
     *
     * @param Request                $request         La requête HTTP.
     * @param Folder                 $folder            Le dossier à supprimer.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités.
     * @return Response La réponse HTTP.
     */
    #[Route('/{id}/supprimer', name: 'app_folder_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Folder $folder, EntityManagerInterface $entityManager): Response
    {
        // Vérifie la validité du jeton CSRF pour des raisons de sécurité.
        if ($this->isCsrfTokenValid('delete'.$folder->getId(), $request->request->get('_token'))) {
            $entityManager->remove($folder);
            $entityManager->flush();
            $this->addFlash('success', 'Le dossier et son contenu ont été supprimés avec succès.');
        }

        // Redirige vers la liste des dossiers.
        return $this->redirectToRoute('app_folder_index');
    }
}