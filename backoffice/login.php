<?php
session_start();

// CSRF helpers
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'mini_website';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS');
$dbPass = $dbPass === false ? '' : $dbPass;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Optional rate limiting placeholder:
    // if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 5) { $error = 'Veuillez réessayer plus tard.'; }

    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(400);
        $error = 'Requête invalide.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Identifiants incorrects.';
        } else {
            try {
                $pdo = new PDO(
                    "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );

                $stmt = $pdo->prepare('SELECT id, username, password FROM utilisateurs WHERE username = :username LIMIT 1');
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Identifiants incorrects.';
                    // $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1; // optionnel
                }
            } catch (Throwable $e) {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion — Administration</title>
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #1e293b;
            --accent: #1e40af;
            --accent-dark: #1e3a8a;
            --accent-light: #3b82f6;
            --danger: #dc2626;
            --bg-primary: #f9fafb;
            --bg-secondary: #f3f4f6;
            --card-bg: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-light: #e5e7eb;
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 28px rgba(0, 0, 0, 0.12);
            --radius: 8px;
            --transition: all 0.2s ease-in-out;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.5s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: var(--shadow-md);
        }

        .login-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0 0 8px 0;
            color: var(--text-primary);
        }

        .login-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }

        .login-card:hover {
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.2);
            border-color: var(--accent-light);
        }

        .form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .label {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .input {
            border: 1.5px solid var(--border-light);
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            background: var(--card-bg);
            color: var(--text-primary);
            transition: var(--transition);
            width: 100%;
            font-family: inherit;
        }

        .input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: linear-gradient(135deg, var(--card-bg) 0%, #f0f4f9 100%);
        }

        .input::placeholder {
            color: var(--text-secondary);
        }

        .input[type="password"]:-webkit-autofill,
        .input[type="text"]:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px var(--card-bg) inset;
            -webkit-text-fill-color: var(--text-primary);
        }

        .feedback {
            padding: 12px 14px;
            border-radius: 8px;
            border-left: 4px solid;
            backdrop-filter: blur(10px);
            animation: slideDown 0.3s ease-out;
        }

        .feedback.error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(248, 113, 113, 0.05) 100%);
            color: #b91c1c;
            border-left-color: var(--danger);
        }

        .button {
            border: none;
            cursor: pointer;
            border-radius: 8px;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: var(--transition);
            letter-spacing: 0.2px;
            box-shadow: var(--shadow-md);
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #ffffff;
            width: 100%;
        }

        .button:hover {
            background: linear-gradient(135deg, var(--accent-dark) 0%, var(--primary) 100%);
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .button:active {
            transform: translateY(0);
        }

        .button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 24px;
            font-weight: 500;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 640px) {
            .login-card {
                padding: 24px;
            }

            .login-title {
                font-size: 20px;
            }

            .button {
                padding: 11px 14px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">🔐</div>
            <h1 class="login-title">Administration</h1>
            <p class="login-subtitle">Accédez à votre espace de gestion</p>
        </div>

        <div class="login-card">
            <?php if ($error !== ''): ?>
                <div class="feedback error" role="alert">
                    ⚠️ Identifiants incorrects. Veuillez réessayer.
                </div>
            <?php endif; ?>

            <form method="POST" novalidate class="form">
                <div class="form-group">
                    <label for="username" class="label">Nom d'utilisateur</label>
                    <input 
                        type="text" 
                        class="input" 
                        id="username" 
                        name="username" 
                        placeholder="Entrez votre identifiant"
                        required 
                        autocomplete="username"
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="label">Mot de passe</label>
                    <input 
                        type="password" 
                        class="input" 
                        id="password" 
                        name="password" 
                        placeholder="Entrez votre mot de passe"
                        required 
                        autocomplete="current-password"
                    >
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                <button type="submit" class="button">Se connecter</button>
            </form>

            <div class="footer-text">
                🔒 Connexion sécurisée — Toutes vos données sont protégées
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-jNQWmwFWivDS7kmjVfUZ0zKxVB1AkBkBxAeHMuJzDmDg09uXBJgp7wa9u0J7BXe0" crossorigin="anonymous" defer></script>
</body>
</html>
