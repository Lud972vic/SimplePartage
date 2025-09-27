<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Folder;
use App\Form\FileUploadType;
use App\Repository\FileRepository;
use App\Service\PermissionCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Ce contrôleur gère les actions liées aux fichiers, telles que le téléversement,
 * le téléchargement et la suppression.
 *
 * Il est préfixé par la route '/file' et nécessite une authentification pour la plupart des actions.
 */
#[Route('/file')]
class FileController extends AbstractController
{
    /**
     * @var EntityManagerInterface Le gestionnaire d'entités pour interagir avec la base de données.
     */
    private $entityManager;

    /**
     * @var FileRepository Le repository pour accéder aux données des fichiers.
     */
    private $fileRepository;

    /**
     * @var PermissionCheckerService Le service pour vérifier les permissions des utilisateurs.
     */
    private $permissionChecker;

    /**
     * @var SluggerInterface L'utilitaire pour générer des slugs à partir de chaînes de caractères.
     */
    private $slugger;

    /**
     * @var string Le répertoire racine du projet.
     */
    private $projectDir;

    /**
     * Constructeur du contrôleur.
     *
     * @param PermissionCheckerService $permissionChecker Le service de vérification des permissions.
     * @param EntityManagerInterface   $entityManager   Le gestionnaire d'entités.
     * @param FileRepository           $fileRepository    Le repository des fichiers.
     * @param SluggerInterface         $slugger           L'utilitaire de slug.
     * @param string                   $projectDir        Le répertoire du projet.
     */
    public function __construct(
        PermissionCheckerService $permissionChecker,
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository,
        SluggerInterface $slugger,
        string $projectDir
    ) {
        $this->permissionChecker = $permissionChecker;
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->slugger = $slugger;
        $this->projectDir = $projectDir;
    }

    /**
     * Gère le téléversement d'un fichier dans un dossier spécifique.
     *
     * @param Request $request La requête HTTP contenant le fichier téléversé.
     * @param Folder  $folder  Le dossier de destination.
     * @return Response La réponse HTTP, soit une redirection vers le dossier, soit le formulaire de téléversement.
     */
    #[Route('/upload/{id}', name: 'app_file_upload')]
    public function upload(Request $request, Folder $folder): Response
    {
        // Crée le formulaire de téléversement.
        $form = $this->createForm(FileUploadType::class);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide.
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('file')->getData();

            if ($uploadedFile) {
                // Récupère les informations du fichier original.
                $originalClientName = $uploadedFile->getClientOriginalName();
                $fileSize = $uploadedFile->getSize();
                $fileMimeType = $uploadedFile->getMimeType();
                $originalFilename = pathinfo($originalClientName, PATHINFO_FILENAME);

                // Génère un nom de fichier sécurisé et unique.
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                // Récupère le chemin complet du dossier de destination.
                $folderPath = $this->getFolderPath($folder);

                try {
                    // Crée le dossier s'il n'existe pas.
                    if (!is_dir($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }

                    // Déplace le fichier téléversé vers le dossier de destination.
                    $uploadedFile->move($folderPath, $newFilename);

                    // Crée une nouvelle entité File pour stocker les informations en base de données.
                    $file = new File();
                    $file->setFolder($folder);
                    $file->setUploadedBy($this->getUser());
                    $file->setFilename($originalClientName);
                    $file->setPath(str_replace($this->projectDir . '/public', '', $folderPath) . '/' . $newFilename);
                    $file->setMimeType($fileMimeType);
                    $file->setSize($fileSize);
                    $file->setUploadedAt(new \DateTimeImmutable());

                    // Persiste l'entité en base de données.
                    $this->entityManager->persist($file);
                    $this->entityManager->flush();

                    // Ajoute un message de succès.
                    $this->addFlash('success', 'Le fichier a été téléversé avec succès.');

                    // Redirige vers la page du dossier.
                    return $this->redirectToRoute('app_folder_show', ['id' => $folder->getId()]);
                } catch (FileException $e) {
                    // En cas d'erreur, ajoute un message d'erreur.
                    $this->addFlash('danger', 'Une erreur est survenue lors du téléversement du fichier: ' . $e->getMessage());
                }
            }
        }

        // Affiche le formulaire de téléversement.
        return $this->render('file/upload.html.twig', [
            'form' => $form->createView(),
            'folder' => $folder,
        ]);
    }

    /**
     * Gère le téléchargement d'un fichier.
     *
     * @param File $file Le fichier à télécharger.
     * @return Response La réponse HTTP contenant le fichier à télécharger ou une page d'erreur.
     */
    #[Route('/download/{id}', name: 'app_file_download')]
    public function download(File $file): Response
    {
        // Vérifie si l'utilisateur a la permission de télécharger le fichier.
        if (!$this->permissionChecker->canDownloadFile($this->getUser(), $file)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour télécharger ce fichier.');
        }
        
        // Construit le chemin complet du fichier.
        $filePath = $this->projectDir . '/public' . $file->getPath();

        // Si le fichier n'existe pas, affiche une page d'erreur.
        if (!file_exists($filePath)) {
            return $this->render('error/file_not_found.html.twig', [
                'filename' => $file->getFilename(),
            ]);
        }

        // Retourne le fichier pour le téléchargement.
        return $this->file($filePath, $file->getFilename());
    }

    /**
     * Gère la suppression d'un fichier.
     *
     * @param Request $request La requête HTTP.
     * @param File    $file    Le fichier à supprimer.
     * @return Response Une redirection vers la page du dossier parent.
     */
    #[Route('/delete/{id}', name: 'app_file_delete', methods: ['POST'])]
    public function delete(Request $request, File $file): Response
    {
        // Récupère l'ID du dossier parent pour la redirection.
        $folderId = $file->getFolder()->getId();
        
        // Vérifie la validité du jeton CSRF pour des raisons de sécurité.
        if ($this->isCsrfTokenValid('delete'.$file->getId(), $request->request->get('_token'))) {
            // Construit le chemin complet du fichier.
            $filePath = $this->projectDir . '/public' . $file->getPath();
            
            // Supprime le fichier physique s'il existe.
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Supprime l'entité de la base de données.
            $this->entityManager->remove($file);
            $this->entityManager->flush();

            // Ajoute un message de succès.
            $this->addFlash('success', 'Le fichier a été supprimé avec succès.');
        }

        // Redirige vers la page du dossier parent.
        return $this->redirectToRoute('app_folder_show', ['id' => $folderId]);
    }

    /**
     * Construit le chemin de dossier complet à partir d'une entité Folder.
     *
     * @param Folder $folder Le dossier pour lequel générer le chemin.
     * @return string Le chemin complet du dossier.
     */
    private function getFolderPath(Folder $folder): string
    {
        $pathParts = [];
        $currentFolder = $folder;

        // Remonte la hiérarchie des dossiers pour construire le chemin.
        while ($currentFolder !== null) {
            array_unshift($pathParts, $this->slugger->slug($currentFolder->getName())->lower());
            $currentFolder = $currentFolder->getParent();
        }

        // Retourne le chemin complet dans le répertoire d'uploads.
        return $this->projectDir . '/public/uploads/' . implode('/', $pathParts);
    }
}