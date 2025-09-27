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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Commande pour créer un utilisateur administrateur initial.
 * Cette commande est utile pour la configuration initiale de l'application.
 */
#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur et initialise les dossiers de base',
)]
class CreateAdminCommand extends Command
{
    /**
     * @var EntityManagerInterface Le gestionnaire d'entités pour interagir avec la base de données.
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var UserPasswordHasherInterface L'utilitaire pour hasher les mots de passe.
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Constructeur de la commande.
     *
     * @param EntityManagerInterface    $entityManager Le gestionnaire d'entités.
     * @param UserPasswordHasherInterface $passwordHasher  L'utilitaire de hashage de mot de passe.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Exécute la commande pour créer l'utilisateur administrateur.
     *
     * @param InputInterface  $input  L'interface d'entrée de la console.
     * @param OutputInterface $output L'interface de sortie de la console.
     * @return int Le code de statut de la commande.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Crée une nouvelle instance de l'entité User pour l'administrateur.
        $admin = new User();
        $admin->setEmail('admin@SimplePartage.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('SimplePartage');
        $admin->setRoles(['ROLE_ADMIN']);
        
        // Hashe le mot de passe de l'administrateur.
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'
        );
        $admin->setPassword($hashedPassword);
        
        // Persiste le nouvel utilisateur en base de données.
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        // Affiche un message de succès.
        $io->success('Utilisateur admin créé avec succès (email: admin@SimplePartage.com, mot de passe: admin123)');

        return Command::SUCCESS;
    }
}