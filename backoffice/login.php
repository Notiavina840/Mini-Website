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
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-GExl5Jy5o+4NQFylBzsA5Kx2aapgI2ofFKsgrLAc60h1nBQNyb4NVy4iNq+Y3r9M" crossorigin="anonymous">
    <style>
        body { background: #f5f7fb; }
        .login-card { max-width: 420px; margin: 72px auto; padding: 28px; border-radius: 12px; background: #fff; box-shadow: 0 8px 30px rgba(0,0,0,0.06); }
    </style>
</head>
<body>
    <div class="login-card">
        <h1 class="h4 mb-3 text-center">Connexion — Administration</h1>
        <p class="text-muted text-center mb-4">Accédez au backoffice pour gérer les articles.</p>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 mb-3" role="alert">Identifiants incorrects.</div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-jNQWmwFWivDS7kmjVfUZ0zKxVB1AkBkBxAeHMuJzDmDg09uXBJgp7wa9u0J7BXe0" crossorigin="anonymous" defer></script>
</body>
</html>
