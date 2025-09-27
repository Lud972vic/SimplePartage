<?php

namespace App\EventSubscriber;

use App\Repository\FolderRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class GlobalVariablesSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $folderRepository;

    public function __construct(Environment $twig, FolderRepository $folderRepository)
    {
        $this->twig = $twig;
        $this->folderRepository = $folderRepository;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // N'exécute pas pour les requêtes non-principales (comme les sous-requêtes)
        if (!$event->isMainRequest()) {
            return;
        }

        // Récupère tous les dossiers pour les afficher dans la navbar
        $folders = $this->folderRepository->findAll();
        
        // Ajoute les variables globales à Twig
        $this->twig->addGlobal('folders', $folders);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}