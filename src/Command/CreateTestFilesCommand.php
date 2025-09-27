<?php

namespace App\Command;

use App\Entity\Folder;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-files',
    description: 'Creates test files and folders for development.',
)]
class CreateTestFilesCommand extends Command
{
    private $entityManager;
    private $uploadDir;

    public function __construct(EntityManagerInterface $entityManager, string $uploadDir)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->uploadDir = $uploadDir;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating test files and folders');

        // Find admin user
        $userRepository = $this->entityManager->getRepository(User::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@SimplePartage.com']);

        if (!$admin) {
            $io->error('Admin user not found. Please create an admin user first with app:create-admin.');
            return Command::FAILURE;
        }

        // Check if root folder already exists
        $folderRepository = $this->entityManager->getRepository(Folder::class);
        $rootFolder = $folderRepository->findOneBy(['name' => 'Dossier de test racine', 'parent' => null]);

        if (!$rootFolder) {
            // Create root folder
            $rootFolder = new Folder();
            $rootFolder->setName('Dossier de test racine');
            $rootFolder->setParent(null);
            $this->entityManager->persist($rootFolder);
        }

        // Check if subfolder already exists
        $subFolder = $folderRepository->findOneBy(['name' => 'Sous-dossier de test', 'parent' => $rootFolder]);

        if (!$subFolder) {
            // Create subfolder
            $subFolder = new Folder();
            $subFolder->setName('Sous-dossier de test');
            $subFolder->setParent($rootFolder);
            $this->entityManager->persist($subFolder);
        }
        
        $io->success('Les fichiers et dossiers de test ont été créés avec succès.');

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}