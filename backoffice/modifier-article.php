<?php require_once __DIR__ . '/includes/auth_check.php'; ?>
<?php
require_once __DIR__ . '/includes/security.php';

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '', 'edit_article');

    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '') {
        $errors[] = 'Le titre est obligatoire.';
    }

    if ($summary === '') {
        $errors[] = 'Le résumé est obligatoire.';
    }

    if ($content === '') {
        $errors[] = 'Le contenu est obligatoire.';
    }

    if (!empty($_FILES['image']['name'])) {
        [$isValid, $safeName, $mime] = validate_image_upload($_FILES['image']);
        if (!$isValid) {
            $errors[] = $safeName;
        } else {
            if (!move_uploaded_image($_FILES['image'], $safeName)) {
                $errors[] = "Impossible d'enregistrer l'image.";
            }
        }
    }

    if (empty($errors)) {
        // TODO: Mettre à jour l'article en base (titre, résumé, contenu, image éventuelle)
        $successMessage = 'Article valide (simulation). Ajoutez la persistance en base.';
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

            <form class="form" id="edit-form" method="POST" enctype="multipart/form-data" novalidate data-validate="article">
                <div class="form-group">
                    <label class="label" for="title">Titre</label>
                    <input class="input" type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label class="label" for="summary">Résumé</label>
                    <input class="input" type="text" id="summary" name="summary" required>
                </div>

                <div class="form-group">
                    <label class="label" for="content">Contenu</label>
                    <textarea class="textarea" id="content" name="content" required></textarea>
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
        </div>
    </div>
    <script src="../assets/js/backoffice.js"></script>
</body>
</html>
