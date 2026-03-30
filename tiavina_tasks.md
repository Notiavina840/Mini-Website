# Mini-projet Web Design – Tâches pour Tiavina

## Rôle principal
Backoffice, authentification, documentation technique, configuration Docker.

---

## 1. Authentification du backoffice

### ✅ Page de connexion (`login.php`)
- Créer un formulaire avec champs `username` et `password`.
- Vérifier les identifiants dans la base de données (table `utilisateurs`).
- Démarrer une session PHP et enregistrer l’utilisateur.
- Rediriger vers `dashboard.php` en cas de succès.
- **Commit :** `feat: add login page for backoffice`

### ✅ Déconnexion (`logout.php`)
- Détruire la session et supprimer les cookies de session.
- Rediriger vers `login.php`.
- **Commit :** `feat: add logout and session handling`

### ✅ Protection des pages admin
- Ajouter en tête de chaque page du backoffice (`liste-articles.php`, `ajouter-article.php`, `modifier-article.php`, etc.) une vérification de session.
- Si l'utilisateur n'est pas connecté, rediriger vers `login.php`.
- **Commit :** `feat: add authentication check on admin pages`

### ✅ Identifiants par défaut
- Ajouter un utilisateur de test dans la base de données avec le login `admin` et le mot de passe `admin` (haché avec `password_hash`).
- Fournir ces identifiants dans la documentation.
- **Commit :** `docs: add default credentials for backoffice`

---

## 2. Documentation technique et livraison

### ✅ Modélisation de la base de données
- Réaliser un schéma entité-association (ou diagramme UML) de la base actuelle.
- Le placer dans le dossier `/doc` avec un fichier `schema.png` (ou `.drawio`).
- **Commit :** `docs: add database modeling`

### ✅ Captures d’écran du Front‑Office et du Back‑Office
- Capturer l’accueil (FO), une page article (FO), la liste des articles (BO), le formulaire d’ajout (BO), la page de connexion (BO).
- Les intégrer dans le document technique final.
- **Commit :** `docs: add screenshots of FO and BO`

### ✅ Rédaction du document technique final
- Contenu :
  - Introduction et objectifs
  - Choix techniques (PHP, MySQL, Docker, Bootstrap, TinyMCE local)
  - Instructions d’installation avec Docker
  - Identifiants par défaut
  - Schéma de la base
  - Captures d’écran
  - Numéros d’étudiants
- Format PDF ou Markdown dans le dossier `/doc`.
- **Commit :** `docs: final technical documentation`

### ✅ Finalisation Docker
- Vérifier que le `Dockerfile` et `docker-compose.yml` permettent un lancement immédiat (`docker compose up`).
- Tester que le site est accessible sur `http://localhost:8080`.
- S’assurer que les volumes (images uploadées) persistent.
- **Commit :** `chore: finalize Docker setup for delivery`

---

## 3. Collaboration et livraison finale
- Coordonner avec Notiavina pour les tests de recette.
- Vérifier que tous les fichiers sont présents avant l’archivage.
- L’archive ZIP devra contenir tout le projet, y compris les fichiers de documentation.
- Le dépôt GitHub/GitLab sera rendu public et le lien sera communiqué.

---

## Rappel des consignes de commit
- Chaque commit doit être atomique (une seule tâche).
- Le message doit commencer par le type (feat, fix, docs, chore, test) suivi d’un titre court en anglais.
- Exemple : `feat: add login page for backoffice`