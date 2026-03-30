# Mini-projet Web Design – Tâches pour Notiavina

## Rôle principal
SEO, optimisation, contenu, tests Lighthouse, livraison finale.

---

## 1. Optimisation SEO (On‑Page et Technique)

### ✅ Attributs `alt` pour toutes les images
- Ajouter un `alt` descriptif pour chaque image dans :
  - le front-office (accueil, détail article)
  - le backoffice (aperçu des articles)
- Si l’image est purement décorative, utiliser `alt=""`.
- **Commit :** `fix: add alt attributes to all images`

### ✅ Hiérarchie des titres (`h1` à `h6`)
- Vérifier qu’il y a un seul `h1` par page.
- Structurer les titres dans l’ordre logique (`h2` pour les sous‑sections, etc.).
- Appliquer sur toutes les pages (FO et BO).
- **Commit :** `fix: correct heading hierarchy for SEO`

### ✅ Balises meta
- Ajouter `meta name="description"` sur chaque page avec un résumé unique.
- Ajouter `meta name="robots"` avec `index, follow` pour les pages publiques, `noindex, nofollow` pour le backoffice.
- Ajouter un lien canonique (`<link rel="canonical">`) pour éviter le contenu dupliqué.
- **Commit :** `feat: add meta tags for SEO on all pages`

### ✅ Sitemap XML et robots.txt
- Créer `robots.txt` à la racine avec :
User-agent: *
Allow: /
Sitemap: http://localhost/sitemap.xml
- Générer dynamiquement `sitemap.xml` listant toutes les URLs du front‑office (accueil, chaque article).
- **Commit :** `feat: add sitemap.xml and robots.txt`

### ✅ Test Lighthouse
- Exécuter Lighthouse sur Chrome (mode mobile et desktop) pour :
- Page d’accueil
- Page détail d’un article
- Backoffice (en mode privé pour éviter les problèmes d’auth)
- Corriger les problèmes identifiés (performance, accessibilité, bonnes pratiques).
- Viser un score > 90 pour les 4 catégories sur les pages publiques.
- **Commit :** `fix: optimize for Lighthouse scores`

---

## 2. Contenu du site sur la guerre en Iran

### ✅ Création des articles
- Rédiger au moins **5 articles cohérents** avec :
- Titre
- Résumé
- Image (trouver des images libres de droits, les placer dans `src/assets/images/`)
- Corps de l’article en HTML (TinyMCE)
- Thème : Guerre en Iran (contexte historique, causes, conséquences, actualité récente – rester factuel).
- Utiliser le backoffice pour les ajouter.
- **Commit :** `content: add articles about Iran war`

### ✅ Vérification de l’affichage
- Tester sur la page d’accueil (liste des articles).
- Tester sur la page de détail (contenu complet, images, sidebar).
- S’assurer que les images s’affichent correctement.
- **Commit :** `test: verify content display on FO`

---

## 3. Livraison finale

### ✅ Préparation du dépôt public
- Créer un dépôt sur GitHub ou GitLab (public).
- Pousser tout le code avec l’historique des commits.
- Vérifier que le dépôt est accessible sans authentification.
- Fournir le lien dans la documentation.

### ✅ Création de l’archive ZIP
- Zipper tout le projet fonctionnel avec Docker.
- Vérifier que le fichier `.env` (si utilisé) ne contient pas de secrets réels (ou fournir un exemple `.env.example`).
- Nommer le fichier `Binome_Nom1_Nom2_WebDesign.zip`.

- **Commits associés :**
- `chore: prepare final delivery` (pour la préparation du dépôt)
- `chore: create final zip archive` (une fois l’archive testée)

---

## 4. Collaboration
- Travailler en parallèle avec Tiavina pour éviter les conflits de fichiers.
- Tester ensemble l’intégration avant la livraison.
- Mettre à jour le fichier `IMPLEMENTATION.md` avec les nouvelles fonctionnalités réalisées.

---

## Rappel des consignes de commit
- Chaque commit doit être atomique (une seule tâche).
- Le message doit commencer par le type (feat, fix, docs, chore, test, content) suivi d’un titre court en anglais.
- Exemple : `fix: add alt attributes to all images`