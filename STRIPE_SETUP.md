# Finalisation de l'intégration Stripe - SoccerMidable ⚽

Ce document récapitule les étapes nécessaires pour activer le paiement Stripe sur votre formulaire d'inscription.

## 1. Préparation dans le Dashboard Stripe
Connectez-vous à votre [Tableau de bord Stripe](https://dashboard.stripe.com/) et effectuez les actions suivantes :

### A. Création des Produits et Prix
Pour chaque programme de votre formulaire, créez un produit dans Stripe :
1. Allez dans **Produits** > **Ajouter un produit**.
2. Nommez-le (ex: "Camp d'été").
3. Définissez le tarif (ex: 150.00 CAD).
4. Une fois créé, copiez l'**ID de l'API du prix** (commence par `price_...`).

### B. Récupération des Clés API
Allez dans **Développeurs** > **Clés API** :
*   **Clé Secrète (Secret Key)** : Commencez par la clé de **test** (`sk_test_...`). Vous utiliserez la clé **live** uniquement au moment du lancement officiel.

---

## 2. Installation sur le Serveur (PHP)
Le script `checkout.php` nécessite la bibliothèque officielle Stripe.

### Option A : Avec Composer (Recommandé)
Si vous avez accès à un terminal sur votre serveur :
```bash
composer require stripe/stripe-php
```

### Option B : Installation manuelle (Sans Composer)
1. Téléchargez la dernière version de [stripe-php ici](https://github.com/stripe/stripe-php/releases).
2. Décompressez le dossier à la racine de votre projet (nommez le dossier `stripe-php`).
3. Dans `checkout.php`, remplacez la ligne `require_once 'vendor/autoload.php';` par :
   ```php
   require_once 'stripe-php/init.php';
   ```

---

## 3. Configuration du fichier `checkout.php`
Ouvrez `checkout.php` et modifiez les sections suivantes :

1.  **Ligne 6** : Remplacez `'sk_test_...'` par votre véritable clé secrète Stripe.
2.  **Lignes 19 à 25** : Remplacez les valeurs temporaires (ex: `price_123_ANNUEL`) par les IDs de prix (`price_...`) que vous avez copiés depuis votre Dashboard Stripe.

> **Note :** Veillez à ce que le nom du programme dans le tableau PHP corresponde exactement au texte dans les `<option>` de votre fichier `index.html`.

---

## 4. Tests et Mise en ligne
1. **Mode Test** : Remplissez le formulaire. Vous devriez être redirigé vers une page Stripe affichant "Mode test". Utilisez les [cartes de test Stripe](https://stripe.com/docs/testing) (ex: 4242 4242...) pour simuler un paiement.
2. **Vérification** : Après le paiement, vérifiez que vous revenez bien sur votre site avec le message de succès.
3. **Passage en Live** :
    * Remplacez la clé `sk_test_...` par votre clé `sk_live_...` dans `checkout.php`.
    * Remplacez les `price_...` de test par vos `price_...` de production.
