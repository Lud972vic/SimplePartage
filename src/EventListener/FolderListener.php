<?php

namespace App\EventListener;

use App\Entity\Folder;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class FolderListener
 *
 * This class listens for Doctrine lifecycle events related to the Folder entity.
 * It is responsible for creating and deleting the physical folders on the file system
 * that correspond to the Folder entities in the database.
 */
class FolderListener
{
    /**
     * @var SluggerInterface
     */
    private $slugger;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * FolderListener constructor.
     *
     * @param SluggerInterface $slugger The slugger service for creating URL-friendly folder names.
     * @param string $projectDir The root directory of the project.
     */
    public function __construct(SluggerInterface $slugger, string $projectDir)
    {
        $this->slugger = $slugger;
        $this->projectDir = $projectDir;
    }

    /**
     * Handles the postPersist event for a Folder entity.
     *
     * This method is called after a new Folder entity is saved to the database.
     * It creates the corresponding physical folder on the file system.
     *
     * @param LifecycleEventArgs $args The event arguments.
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Folder) {
            return;
        }

        $this->createPhysicalFolder($entity);
    }

    /**
     * Handles the preRemove event for a Folder entity.
     *
     * This method is called before a Folder entity is removed from the database.
     * It deletes the corresponding physical folder and all its contents from the file system.
     *
     * @param LifecycleEventArgs $args The event arguments.
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Folder) {
            return;
        }

        $this->removePhysicalFolder($entity);
    }

    /**
     * Removes a physical folder from the file system.
     *
     * @param Folder $folder The Folder entity corresponding to the folder to be removed.
     */
    private function removePhysicalFolder(Folder $folder)
    {
        $folderPath = $this->getFolderPath($folder);
        if (file_exists($folderPath)) {
            $this->deleteDirectory($folderPath);
        }
    }

    /**
     * Recursively deletes a directory and all its contents.
     *
     * @param string $dir The path to the directory to be deleted.
     */
    private function deleteDirectory(string $dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }

        rmdir($dir);
    }

    /**
     * Creates a physical folder on the file system.
     *
     * @param Folder $folder The Folder entity for which to create the physical folder.
     */
    private function createPhysicalFolder(Folder $folder)
    {
        $folderPath = $this->getFolderPath($folder);
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
    }

    /**
     * Generates the full path for a given Folder entity.
     *
     * The path is created by joining the slugified names of the folder and its parents.
     *
     * @param Folder $folder The Folder entity.
     * @return string The full path to the folder on the file system.
     */
    private function getFolderPath(Folder $folder): string
    {
        $pathParts = [];
        $currentFolder = $folder;

        while ($currentFolder !== null) {
            array_unshift($pathParts, $this->slugger->slug($currentFolder->getName())->lower());
            $currentFolder = $currentFolder->getParent();
        }

        return $this->projectDir . '/public/uploads/' . implode('/', $pathParts);
    }
}