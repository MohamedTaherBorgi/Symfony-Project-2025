# EcoStore - Plateforme E-Commerce Éco-Friendly

## Description

EcoStore est une application web Symfony dédiée à la vente de produits écologiques et durables. Le projet utilise le framework Symfony 6.4, Doctrine ORM pour la gestion des données, et le template Hairnic pour le frontend.

## Architecture du Projet

### Entités (Entities)

Le projet inclut les entités suivantes :

1. **Utilisateur** - Gestion des utilisateurs et authentification
   - id, nom, email, passwordHash, roles, dateInscription
   - Relations: OneToMany avec Commande, OneToOne avec Panier

2. **Categorie** - Classification des produits
   - id, nom
   - Relations: OneToMany avec Produit

3. **Produit** - Produits disponibles à la vente
   - id, nom, description, prix, stock, image
   - Relations: ManyToOne avec Categorie, OneToMany avec LignePanier et LigneCommande

4. **Panier** - Panier d'achat de l'utilisateur
   - id, dateTime, prixTotal
   - Relations: OneToOne avec Utilisateur, OneToMany avec LignePanier

5. **LignePanier** - Articles dans le panier
   - id, quantite, prixUnitaire, sousTotal
   - Relations: ManyToOne avec Panier et Produit

6. **Commande** - Commandes passées par les utilisateurs
   - id, date, total, statut, adresse
   - Relations: ManyToOne avec Utilisateur, OneToMany avec LigneCommande

7. **LigneCommande** - Articles dans une commande
   - id, quantite, prix
   - Relations: ManyToOne avec Commande et Produit

### Structure des Dossiers

```
ecostore/
├── src/
│   ├── Controller/          # Contrôleurs Symfony
│   ├── Entity/              # Entités Doctrine
│   ├── Repository/          # Repositories pour accès aux données
│   └── Kernel.php
├── templates/               # Templates Twig
│   ├── base.html.twig       # Template de base
│   ├── home/                # Pages d'accueil
│   ├── products/            # Pages produits
│   ├── about/               # Pages à propos
│   └── contact/             # Pages contact
├── public/                  # Fichiers publics (CSS, JS, images)
│   ├── css/                 # Feuilles de style
│   ├── js/                  # Scripts JavaScript
│   ├── img/                 # Images
│   └── lib/                 # Bibliothèques externes (Bootstrap, etc.)
├── config/                  # Configuration Symfony
├── migrations/              # Migrations de base de données
└── composer.json            # Dépendances PHP
```

## Installation

### Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL/MariaDB ou PostgreSQL
- Node.js (optionnel, pour les assets)

### Étapes d'Installation

1. **Cloner le projet**
```bash
git clone <repository-url>
cd ecostore
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer la base de données**
Éditer le fichier `.env` et configurer `DATABASE_URL` :
```
DATABASE_URL="mysql://user:password@127.0.0.1:3306/ecostore"
```

4. **Créer la base de données**
```bash
php bin/console doctrine:database:create
```

5. **Exécuter les migrations**
```bash
php bin/console doctrine:migrations:migrate
```

6. **Charger les données de test (optionnel)**
```bash
php bin/console doctrine:fixtures:load
```

7. **Démarrer le serveur de développement**
```bash
symfony server:start
```

Le projet sera accessible à `http://localhost:8000`

## Contrôleurs

### HomeController

- `GET /` - Page d'accueil
- `GET /products` - Liste des produits
- `GET /about` - Page à propos
- `GET /contact` - Page contact

## Templates Twig

- **base.html.twig** - Template de base avec navigation et footer
- **home/index.html.twig** - Page d'accueil avec sections hero et produits populaires
- **products/index.html.twig** - Catalogue de produits avec filtres
- **about/index.html.twig** - Page à propos avec équipe
- **contact/index.html.twig** - Formulaire de contact

## Dépendances Principales

- **symfony/framework-bundle** - Framework Symfony
- **doctrine/orm** - ORM Doctrine
- **symfony/twig-bundle** - Moteur de templates Twig
- **symfony/form** - Gestion des formulaires
- **symfony/validator** - Validation des données
- **symfony/security-bundle** - Authentification et autorisation

## Configuration de la Base de Données

### Doctrine Configuration

La configuration de Doctrine se trouve dans `config/packages/doctrine.yaml`. 

### Migrations

Les migrations sont gérées par Doctrine Migrations. Pour créer une nouvelle migration :

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## Frontend

Le frontend utilise le template Hairnic qui inclut :

- Bootstrap 5 pour la mise en page responsive
- CSS personnalisé pour le style
- JavaScript pour les interactions
- Images et icônes Font Awesome

## Prochaines Étapes

1. **Implémenter l'authentification** - Ajouter la connexion/inscription
2. **Créer les API endpoints** - Pour les opérations CRUD
3. **Implémenter le panier** - Gestion du panier côté serveur
4. **Ajouter le système de paiement** - Intégration Stripe/PayPal
5. **Créer un panel administrateur** - Gestion des produits et commandes
6. **Ajouter des tests** - Tests unitaires et fonctionnels

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

## Support

Pour toute question ou support, veuillez contacter : info@ecostore.com
