# Guide d'Installation - EcoStore

## Prérequis

- PHP 8.1 ou supérieur
- Composer 2.0+
- MySQL 5.7+ ou PostgreSQL 12+
- Git

## Installation Locale

### Étape 1: Cloner le projet

```bash
git clone <repository-url>
cd ecostore
```

### Étape 2: Installer les dépendances

```bash
composer install
```

### Étape 3: Configurer l'environnement

Copier le fichier `.env` et le configurer :

```bash
cp .env .env.local
```

Éditer `.env.local` et configurer :

```env
# Base de données
DATABASE_URL="mysql://root:password@127.0.0.1:3306/ecostore"

# Symfony
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=your-secret-key

# Mailer
MAILER_DSN=smtp://localhost
```

### Étape 4: Créer la base de données

```bash
php bin/console doctrine:database:create
```

### Étape 5: Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

### Étape 6: Charger les données de test (optionnel)

```bash
php bin/console doctrine:fixtures:load
```

### Étape 7: Démarrer le serveur

```bash
symfony server:start
```

L'application est accessible à `http://localhost:8000`

## Configuration de la Base de Données

### MySQL

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/ecostore"
```

### PostgreSQL

```env
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/ecostore"
```

## Structure des Répertoires

```
ecostore/
├── bin/                 # Scripts exécutables
├── config/              # Configuration Symfony
├── migrations/          # Migrations de base de données
├── public/              # Fichiers publics (CSS, JS, images)
├── src/                 # Code source
│   ├── Controller/      # Contrôleurs
│   ├── Entity/          # Entités Doctrine
│   └── Repository/      # Repositories
├── templates/           # Templates Twig
├── tests/               # Tests
├── var/                 # Fichiers générés (cache, logs)
├── vendor/              # Dépendances Composer
├── .env                 # Variables d'environnement
├── .env.local           # Variables locales (à ne pas commiter)
├── composer.json        # Dépendances PHP
└── symfony.lock         # Verrouillage des dépendances
```

## Commandes Utiles

### Gestion de la Base de Données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Supprimer la base de données
php bin/console doctrine:database:drop

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Voir l'état des migrations
php bin/console doctrine:migrations:status
```

### Gestion du Cache

```bash
# Vider le cache
php bin/console cache:clear

# Vider le cache de production
php bin/console cache:clear --env=prod
```

### Gestion des Assets

```bash
# Installer les assets
php bin/console assets:install public
```

### Gestion des Utilisateurs

```bash
# Créer un utilisateur
php bin/console app:create-user

# Lister les utilisateurs
php bin/console doctrine:query:sql "SELECT * FROM utilisateur"
```

## Déploiement en Production

### Préparer le déploiement

```bash
# Installer les dépendances sans dev
composer install --no-dev

# Générer les assets
php bin/console assets:install public --env=prod

# Vider le cache
php bin/console cache:clear --env=prod
```

### Configuration du serveur web

#### Apache

Créer un fichier `.htaccess` dans le répertoire `public/` :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

#### Nginx

Configurer le bloc `server` :

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/ecostore/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

## Dépannage

### Erreur: "No database found"

```bash
php bin/console doctrine:database:create
```

### Erreur: "Migration not found"

```bash
php bin/console doctrine:migrations:migrate
```

### Erreur: "Permission denied"

```bash
chmod -R 777 var/
```

### Cache non mis à jour

```bash
php bin/console cache:clear
```

## Support

Pour toute question ou problème, veuillez contacter : support@ecostore.com
