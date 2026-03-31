<?php require_once __DIR__ . '/includes/auth_check.php'; ?>
<?php
require_once __DIR__ . '/includes/security.php';

$errors = [];
$successMessage = '';
$categories = [];

try {
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: 'mini_website';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS');
    $dbPass = $dbPass === false ? '' : $dbPass;

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->query('SELECT id, nom, slug FROM categories ORDER BY nom ASC');
    $categories = $stmt->fetchAll() ?: [];
} catch (Throwable $e) {
    error_log('[backoffice] Erreur catégories: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '', 'add_article');

    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if ($title === '') {
        $errors[] = 'Le titre est obligatoire.';
    }

    if ($summary === '') {
        $errors[] = 'Le résumé est obligatoire.';
    }

    if ($content === '') {
        $errors[] = 'Le contenu est obligatoire.';
    }

    // Contrôle de l'upload d'image (optionnel)
    if (!empty($_FILES['image']['name'])) {
        [$isValid, $safeName, $mime] = validate_image_upload($_FILES['image']);
        if (!$isValid) {
            $errors[] = $safeName; // safeName contient le message d'erreur dans ce cas
        } else {
            if (!move_uploaded_image($_FILES['image'], $safeName)) {
                $errors[] = "Impossible d'enregistrer l'image.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $dbHost = getenv('DB_HOST') ?: 'localhost';
            $dbName = getenv('DB_NAME') ?: 'mini_website';
            $dbUser = getenv('DB_USER') ?: 'root';
            $dbPass = getenv('DB_PASS');
            $dbPass = $dbPass === false ? '' : $dbPass;

            $pdo = new PDO(
                "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            // Générer un slug unique à partir du titre
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            
            // Vérifier unicité du slug
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM articles WHERE slug = :slug');
            $stmt->execute(['slug' => $slug]);
            $result = $stmt->fetch();
            if ($result['count'] > 0) {
                $slug = $slug . '-' . time();
            }

            $image = isset($safeName) ? $safeName : 'image1.jpg';
            $auteurId = $_SESSION['user_id'] ?? null;

            $stmt = $pdo->prepare(
                'INSERT INTO articles (titre, slug, resume, contenu, image, categorie, auteur_id) 
                 VALUES (:titre, :slug, :resume, :contenu, :image, :categorie, :auteur_id)'
            );
            $stmt->execute([
                ':titre' => $title,
                ':slug' => $slug,
                ':resume' => $summary,
                ':contenu' => $content,
                ':image' => $image,
                ':categorie' => $category ?: null,
                ':auteur_id' => $auteurId,
            ]);

            $successMessage = 'Article créé avec succès !';
        } catch (Throwable $e) {
            $errors[] = 'Erreur lors de la création : ' . $e->getMessage();
        }
    }
}

$csrfToken = generate_csrf_token('add_article');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Ajouter un article</title>
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body>
    <div class="layout">
        <div class="navbar">
            <div class="title">Ajouter un article</div>
            <div class="nav-actions">
                <a class="button-secondary" href="dashboard.php">Dashboard</a>
                <a class="button" href="ajouter-article.php">Ajouter article</a>
                <a class="button-secondary" href="logout.php">Déconnexion</a>
            </div>
        </div>

        <h1 style="margin:0 0 16px 0;">Administration — Ajouter un article</h1>

        <div class="card">
            <h2 style="margin-top:0;">Nouveau contenu</h2>

            <?php if (!empty($errors)): ?>
                <div class="feedback error">
                    <ul style="margin:0; padding-left:18px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($successMessage !== ''): ?>
                <div class="feedback success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <form class="form" id="add-form" method="POST" enctype="multipart/form-data" novalidate data-validate="article">
                <div class="form-group">
                    <label class="label" for="title">Titre</label>
                    <input class="input" type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label class="label" for="summary">Résumé</label>
                    <input class="input" type="text" id="summary" name="summary" required>
                </div>

                <div class="form-group">
                    <label class="label" for="category">Catégorie</label>
                    <select class="input" id="category" name="category">
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['slug']); ?>">
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="label" for="content">Contenu</label>
                    <textarea class="textarea" id="content" name="content" required></textarea>
                </div>

                <div class="form-group">
                    <label class="label" for="image">Image (jpeg/png/gif, max 2 Mo)</label>
                    <input class="file-input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="actions-row">
                    <button class="button" type="submit">Publier</button>
                    <a class="button-secondary" href="dashboard.php">Annuler</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../assets/js/backoffice.js"></script>
</body>
</html>
