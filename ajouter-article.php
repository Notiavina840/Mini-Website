<?php
// Démarrer la session pour vérifier l'authentification
session_start();

// Si l'utilisateur n'est pas connecté, le renvoyer vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un article</title>
</head>
<body>
    <h1>Ajouter un article</h1>
</body>
</html>
