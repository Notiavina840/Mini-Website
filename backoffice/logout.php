<?php
require_once __DIR__ . '/includes/security.php';

// Démarrer la session actuelle pour pouvoir la détruire
start_secure_session();

// Vider toutes les variables de session
$_SESSION = [];

// Supprimer le cookie de session s'il existe
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

// Détruire complètement la session
session_destroy();

// Rediriger l'utilisateur vers la page de connexion
header('Location: login.php');
exit;
