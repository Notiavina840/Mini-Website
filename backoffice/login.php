<?php
require_once __DIR__ . '/includes/security.php';

// --------------------------------------------------
// 1) Démarrer la session PHP avec paramètres sécurisés
// --------------------------------------------------
start_secure_session();

// --------------------------------------------------
// 2) Configuration de la connexion MySQL
//    Compatible local + Docker via variables d'environnement
// --------------------------------------------------
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'mini_website';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS');
$dbPass = $dbPass === false ? '' : $dbPass;

// Variable pour stocker les messages d'erreur
$errorMessage = '';

// --------------------------------------------------
// 3) Si l'utilisateur est déjà connecté, redirection
// --------------------------------------------------
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// --------------------------------------------------
// 4) Traitement du formulaire lors de l'envoi en POST
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '', 'login_form');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Vérifier que les champs ne sont pas vides
    if ($username === '' || $password === '') {
        $errorMessage = 'Veuillez remplir tous les champs.';
    } else {
        try {
            // --------------------------------------------------
            // 5) Connexion à la base de données MySQL avec PDO
            // --------------------------------------------------
            $pdo = new PDO(
                "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            // --------------------------------------------------
            // 6) Rechercher l'utilisateur dans la table utilisateurs
            // --------------------------------------------------
            $stmt = $pdo->prepare('SELECT id, username, password FROM utilisateurs WHERE username = :username LIMIT 1');
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            // --------------------------------------------------
            // 7) Vérifier le mot de passe
            //    - password_verify() pour mot de passe hashé
            //    - comparaison simple en secours si non hashé
            // --------------------------------------------------
            $isValidPassword = $user && password_verify($password, $user['password']);

            if ($isValidPassword) {
                // --------------------------------------------------
                // 8) Authentification réussie : enregistrer la session
                // --------------------------------------------------
                regenerate_session_id_if_needed();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirection vers le tableau de bord
                header('Location: dashboard.php');
                exit;
            }

            // Si aucun utilisateur valide n'est trouvé
            $errorMessage = 'Nom d’utilisateur ou mot de passe incorrect.';
        } catch (PDOException $e) {
            // --------------------------------------------------
            // 9) Gestion d'une erreur de connexion ou de requête SQL
            // --------------------------------------------------
            $errorMessage = 'Erreur de connexion à la base de données.';
        }
    }
}

// Après traitement, régénérer un token CSRF pour l'affichage du formulaire
$csrfToken = generate_csrf_token('login_form');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-box {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            color: #222;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0078d4;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #005ea6;
        }

        .error-message {
            background: #fdecea;
            color: #b42318;
            border: 1px solid #f5c2c7;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Connexion</h1>

        <!-- 10) Afficher un message d'erreur si le login échoue -->
        <?php if ($errorMessage !== ''): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- 11) Formulaire HTML de connexion -->
        <form method="POST" action="">
            <label for="username">Nom d'utilisateur</label>
            <input
                type="text"
                id="username"
                name="username"
                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                required
            >

            <label for="password">Mot de passe</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
