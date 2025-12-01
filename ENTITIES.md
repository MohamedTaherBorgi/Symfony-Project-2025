# Documentation des Entités - EcoStore

## Vue d'ensemble

Ce document décrit toutes les entités du projet EcoStore et leurs relations.

## Entités

### 1. Utilisateur

**Namespace:** `App\Entity\Utilisateur`

**Attributs:**
- `id` (int, PK)
- `nom` (string)
- `email` (string, unique)
- `passwordHash` (string)
- `roles` (array)
- `dateInscription` (datetime)

**Méthodes:**
- `login()` - Authentifier l'utilisateur
- `logout()` - Déconnecter l'utilisateur
- `modifierProfil()` - Modifier le profil utilisateur
- `consulterCommandes()` - Consulter les commandes

**Relations:**
- OneToMany: Commande (inverse: utilisateur)
- OneToOne: Panier (inverse: utilisateur)

---

### 2. Categorie

**Namespace:** `App\Entity\Categorie`

**Attributs:**
- `id` (int, PK)
- `nom` (string)

**Méthodes:**
- `ajouterProduits()` - Ajouter des produits à la catégorie
- `afficherProduits()` - Afficher les produits de la catégorie

**Relations:**
- OneToMany: Produit (inverse: categorie)

---

### 3. Produit

**Namespace:** `App\Entity\Produit`

**Attributs:**
- `id` (int, PK)
- `nom` (string)
- `description` (text)
- `prix` (float)
- `stock` (int)
- `image` (string)

**Méthodes:**
- `modifierStock()` - Modifier le stock du produit
- `afficherDetails()` - Afficher les détails du produit

**Relations:**
- ManyToOne: Categorie (inverse: produits)
- OneToMany: LignePanier (inverse: produit)
- OneToMany: LigneCommande (inverse: produit)

---

### 4. Panier

**Namespace:** `App\Entity\Panier`

**Attributs:**
- `id` (int, PK)
- `dateTime` (datetime)
- `prixTotal` (float)

**Méthodes:**
- `ajouterProduit()` - Ajouter un produit au panier
- `retirerProduit()` - Retirer un produit du panier
- `viderPanier()` - Vider le panier
- `calculerTotal()` - Calculer le total du panier
- `validerCommande()` - Valider la commande

**Relations:**
- OneToOne: Utilisateur (inverse: panier)
- OneToMany: LignePanier (inverse: panier)

---

### 5. LignePanier

**Namespace:** `App\Entity\LignePanier`

**Attributs:**
- `id` (int, PK)
- `quantite` (int)
- `prixUnitaire` (float)
- `sousTotal` (float)

**Méthodes:**
- `modifierQuantite()` - Modifier la quantité
- `calculerSousTotal()` - Calculer le sous-total

**Relations:**
- ManyToOne: Panier (inverse: lignes)
- ManyToOne: Produit (inverse: lignesPanier)

---

### 6. Commande

**Namespace:** `App\Entity\Commande`

**Attributs:**
- `id` (int, PK)
- `date` (datetime)
- `total` (float)
- `statut` (string)
- `adresse` (string)

**Méthodes:**
- `confirmer()` - Confirmer la commande
- `annuler()` - Annuler la commande
- `suivreStatut()` - Suivre le statut de la commande

**Relations:**
- ManyToOne: Utilisateur (inverse: commandes)
- OneToMany: LigneCommande (inverse: commande)

---

### 7. LigneCommande

**Namespace:** `App\Entity\LigneCommande`

**Attributs:**
- `id` (int, PK)
- `quantite` (int)
- `prix` (float)

**Méthodes:**
- `getSubtotal()` - Obtenir le sous-total

**Relations:**
- ManyToOne: Commande (inverse: lignes)
- ManyToOne: Produit (inverse: lignesCommande)

---

## Diagramme de Relations

```
Utilisateur (1) -------- (1) Panier
    |
    | (1)
    |
    +------ (n) Commande

Categorie (1) -------- (n) Produit

Panier (1) -------- (n) LignePanier
                        |
                        | (n)
                        |
                    Produit (1)
                        |
                        | (n)
                        |
                    LigneCommande

Commande (1) -------- (n) LigneCommande
                        |
                        | (n)
                        |
                    Produit (1)
```

## Repositories

Chaque entité dispose d'un repository pour accéder aux données :

- `UtilisateurRepository` - Requêtes sur les utilisateurs
- `CategorieRepository` - Requêtes sur les catégories
- `ProduitRepository` - Requêtes sur les produits
- `PanierRepository` - Requêtes sur les paniers
- `LignePanierRepository` - Requêtes sur les lignes de panier
- `CommandeRepository` - Requêtes sur les commandes
- `LigneCommandeRepository` - Requêtes sur les lignes de commande

## Migrations

Les migrations Doctrine permettent de créer et modifier la structure de la base de données :

```bash
# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Voir l'état des migrations
php bin/console doctrine:migrations:status
```

## Exemple d'utilisation

### Créer un utilisateur

```php
$utilisateur = new Utilisateur();
$utilisateur->setNom('Jean Dupont');
$utilisateur->setEmail('jean@example.com');
$utilisateur->setPasswordHash(password_hash('password123', PASSWORD_BCRYPT));
$utilisateur->setRoles(['ROLE_USER']);

$entityManager->persist($utilisateur);
$entityManager->flush();
```

### Créer un produit

```php
$produit = new Produit();
$produit->setNom('Shampooing Bio');
$produit->setDescription('Shampooing 100% naturel');
$produit->setPrix(29.99);
$produit->setStock(100);
$produit->setCategorie($categorie);

$entityManager->persist($produit);
$entityManager->flush();
```

### Ajouter un produit au panier

```php
$lignePanier = new LignePanier();
$lignePanier->setProduit($produit);
$lignePanier->setQuantite(2);
$lignePanier->setPrixUnitaire($produit->getPrix());
$lignePanier->setSousTotal(2 * $produit->getPrix());
$lignePanier->setPanier($panier);

$entityManager->persist($lignePanier);
$entityManager->flush();
```

