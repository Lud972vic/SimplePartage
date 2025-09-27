# SimplePartage - Application de partage de fichiers avec Symfony

SimplePartage est une application de partage de fichiers développée avec Symfony, permettant aux utilisateurs de gérer des dossiers et des fichiers avec un système de permissions avancé.

![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.40.12.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.40.46.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.43.18.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.43.34.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.43.50.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.44.04.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.44.13.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.44.38.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.44.48.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.45.16.png>)
![alt text](<public/uploads/docs/Capture d’écran 2025-09-28 à 00.45.34.png>)

## Fonctionnalités

- Gestion des utilisateurs et des groupes
- Système de dossiers hiérarchiques
- Upload et téléchargement de fichiers
- Système de permissions avancé (par utilisateur et par groupe)
- Interface utilisateur intuitive

## Prérequis

- PHP 8.1 ou supérieur
- Composer
- MariaDB ou MySQL
- Symfony CLI (recommandé pour le développement)

## Installation

1.  **Clonez le dépôt :**

    ```bash
    git clone https://github.com/votre-utilisateur/SimplePartage.git
    cd SimplePartage
    ```

2. Installer les dépendances :
```bash
composer install
```

3. Configurer la base de données dans le fichier `.env` :
```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/SimplePartage?serverVersion=mariadb-10.5.8"
```

4. Créer la base de données :
```bash
php bin/console doctrine:database:create
```

5. Exécuter les migrations :
```bash
php bin/console doctrine:migrations:migrate
```

6. Initialiser l'application avec un utilisateur admin et des dossiers de base :
```bash
php bin/console app:create-admin
```

    L'utilisateur admin par défaut est :
    - Email : admin@SimplePartage.com
    - Mot de passe : `admin123`

7. Démarrer le serveur de développement :
```bash
symfony server:start
```

## Utilisation

1. Accéder à l'application via `http://localhost:8000`
2. Se connecter avec les identifiants admin :
   - Email : admin@SimplePartage.com
   - Mot de passe : admin123

## Structure du projet

- `src/Entity/` : Définition des entités (User, Group, Folder, File, FolderPermission)
- `src/Controller/` : Contrôleurs pour la gestion des dossiers et fichiers
- `src/Service/` : Services, notamment le PermissionCheckerService
- `src/Security/` : Voters pour la gestion des permissions
- `templates/` : Templates Twig pour l'interface utilisateur

## Système de permissions

Le système de permissions permet de définir des droits d'accès pour chaque dossier :

- Voir le dossier
- Voir les fichiers dans le dossier
- Télécharger les fichiers du dossier

Les permissions peuvent être attribuées à des utilisateurs individuels ou à des groupes d'utilisateurs.

## Développement

Pour ajouter de nouvelles fonctionnalités ou modifier l'application :

1. Créer une nouvelle branche :
```bash
git checkout -b feature/ma-nouvelle-fonctionnalite
```

2. Effectuer les modifications nécessaires

3. Exécuter les tests :
```bash
php bin/phpunit
```

4. Soumettre les modifications :
```bash
git add .
git commit -m "Ajout de ma nouvelle fonctionnalité"
git push origin feature/ma-nouvelle-fonctionnalite
```

## Licence

Ce projet est sous licence MIT.