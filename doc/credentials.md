# Identifiants de test

## Connexion par défaut

Utiliser les identifiants suivants pour tester le backoffice :

- **Login** : `admin`
- **Mot de passe** : `admin`

> Le mot de passe est stocké en base sous forme **hachée** avec `password_hash()`.

## Préparer la base MySQL

Exécuter le script SQL suivant pour créer la table et l’utilisateur de test :

- `doc/create_test_user.sql`

## Accès à la page de connexion

Ouvrir la page :

- `backend/login.php`

Puis saisir :

- **Nom d’utilisateur** : `admin`
- **Mot de passe** : `admin`

## Remarque

Si nécessaire, vérifier les paramètres de connexion MySQL dans `backend/login.php` :

- `$dbHost`
- `$dbName`
- `$dbUser`
- `$dbPass`
