<?php
// Déconnexion sécurisée
session_start(); // 1) accéder à la session active

// 2) Vider les données de session côté serveur
$_SESSION = [];

// 3) Supprimer le cookie de session côté client si utilisé
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 4) Détruire la session côté serveur
session_destroy();

// 5) Rediriger vers la page de connexion
header('Location: login.php');

// 6) Stopper l'exécution
exit();
