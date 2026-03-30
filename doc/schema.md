# Schéma de base de données

## Tables principales

- `utilisateurs`
- `categories`
- `articles`
- `commentaires`

## Légende des relations

1. **`utilisateurs` (1) → (N) `articles`**  
   Un utilisateur peut publier plusieurs articles.

2. **`categories` (1) → (N) `articles`**  
   Une catégorie peut contenir plusieurs articles.

3. **`articles` (1) → (N) `commentaires`**  
   Un article peut recevoir plusieurs commentaires.

4. **`utilisateurs` (1) → (N) `commentaires`**  
   Un utilisateur peut écrire plusieurs commentaires.

## Fichier du diagramme

Le schéma visuel est disponible ici :

- `doc/schema.png`

## Abréviations

- `PK` = clé primaire
- `FK` = clé étrangère
