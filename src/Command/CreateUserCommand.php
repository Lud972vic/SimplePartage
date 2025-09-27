<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Commande pour créer un nouvel utilisateur.
 * Cette commande permet de créer un utilisateur avec un email, un mot de passe et des rôles.
 */
#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un nouvel utilisateur.',
)]
class CreateUserCommand extends Command
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
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Configure la commande en définissant les arguments attendus.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'L\'email de l\'utilisateur.')
            ->addArgument('password', InputArgument::REQUIRED, 'Le mot de passe de l\'utilisateur.')
            ->addArgument('roles', InputArgument::IS_ARRAY, 'Les rôles de l\'utilisateur.');
    }

    /**
     * Exécute la commande pour créer l'utilisateur.
     *
     * @param InputInterface  $input  L'interface d'entrée de la console.
     * @param OutputInterface $output L'interface de sortie de la console.
     * @return int Le code de statut de la commande.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupère les arguments de la commande.
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $roles = $input->getArgument('roles');

        // Crée une nouvelle instance de l'entité User.
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($email);
        $user->setLastName($email);
        
        // Hashe le mot de passe et le définit pour l'utilisateur.
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles($roles);

        // Persiste le nouvel utilisateur en base de données.
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Affiche un message de succès.
        $io->success(sprintf('Utilisateur %s créé avec succès.', $email));

        return Command::SUCCESS;
    }
}