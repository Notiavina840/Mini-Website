<?php require_once __DIR__ . '/includes/auth_check.php'; ?>
<?php
require_once __DIR__ . '/includes/security.php';

$errors = [];
$successMessage = '';
$article = null;
$categories = [];

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'mini_website';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS');
$dbPass = $dbPass === false ? '' : $dbPass;

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

    // Charger les catégories
    $stmt = $pdo->query('SELECT id, nom, slug FROM categories ORDER BY nom ASC');
    $categories = $stmt->fetchAll() ?: [];

    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        $errors[] = 'Article non trouvé.';
    } else {
        $stmt = $pdo->prepare('SELECT id, titre, resume, contenu, image, categorie FROM articles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $errors[] = 'Article non trouvé.';
        }
    }
} catch (Throwable $e) {
    $errors[] = 'Erreur de connexion à la base de données.';
    error_log('[modifier-article] DB error: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $article) {
    verify_csrf_token($_POST['csrf_token'] ?? '', 'edit_article');

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

    $image = $article['image'];
    if (!empty($_FILES['image']['name'])) {
        [$isValid, $safeName, $mime] = validate_image_upload($_FILES['image']);
        if (!$isValid) {
            $errors[] = $safeName;
        } else {
            if (!move_uploaded_image($_FILES['image'], $safeName)) {
                $errors[] = "Impossible d'enregistrer l'image.";
            } else {
                $image = $safeName;
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE articles SET titre = :titre, resume = :resume, contenu = :contenu, image = :image, categorie = :categorie WHERE id = :id'
            );
            $stmt->execute([
                ':titre' => $title,
                ':resume' => $summary,
                ':contenu' => $content,
                ':image' => $image,
                ':categorie' => $category ?: null,
                ':id' => $article['id'],
            ]);
            $successMessage = 'Article modifié avec succès !';
            $article['titre'] = $title;
            $article['resume'] = $summary;
            $article['contenu'] = $content;
            $article['image'] = $image;
            $article['categorie'] = $category ?: null;
        } catch (Throwable $e) {
            $errors[] = 'Erreur lors de la modification : ' . $e->getMessage();
        }
    }
}

$csrfToken = generate_csrf_token('edit_article');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Modifier un article</title>
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body>
    <div class="layout">
        <div class="navbar">
            <div class="title">Modifier un article</div>
            <div class="nav-actions">
                <a class="button-secondary" href="dashboard.php">Dashboard</a>
                <a class="button" href="ajouter-article.php">Ajouter article</a>
                <a class="button-secondary" href="logout.php">Déconnexion</a>
            </div>
        </div>

        <h1 style="margin:0 0 16px 0;">Administration — Modifier un article</h1>

        <div class="card">
            <h2 style="margin-top:0;">Édition</h2>

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

            <?php if ($article): ?>
            <form class="form" id="edit-form" method="POST" enctype="multipart/form-data" novalidate data-validate="article">
                <div class="form-group">
                    <label class="label" for="title">Titre</label>
                    <input class="input" type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['titre']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="label" for="summary">Résumé</label>
                    <input class="input" type="text" id="summary" name="summary" value="<?php echo htmlspecialchars($article['resume']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="label" for="category">Catégorie</label>
                    <select class="input" id="category" name="category">
                        <option value="">-- Sélectionner une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['slug']); ?>" 
                                <?php echo $article['categorie'] === $cat['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="label" for="content">Contenu</label>
                    <textarea class="textarea" id="content" name="content" required><?php echo htmlspecialchars($article['contenu']); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="label" for="image">Nouvelle image (jpeg/png/gif, max 2 Mo)</label>
                    <input class="file-input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="actions-row">
                    <button class="button" type="submit">Mettre à jour</button>
                    <a class="button-secondary" href="dashboard.php">Annuler</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/backoffice.js"></script>
</body>
</html>
