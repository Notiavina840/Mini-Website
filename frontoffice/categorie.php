<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

function format_date(?string $value): string
{
    return $value ? date('d F Y', strtotime($value)) : '';
}

$slug = $_GET['slug'] ?? '';
if ($slug === '') {
    http_response_code(404);
    echo 'Catégorie introuvable.';
    exit;
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonical = 'http://' . $host . '/categorie/' . $slug;

$category = null;
$articles = [];
$errorMessage = '';

try {
    $pdo = get_pdo();
    $stmtCat = $pdo->prepare('SELECT nom, slug FROM categories WHERE slug = :slug LIMIT 1');
    $stmtCat->execute(['slug' => $slug]);
    $category = $stmtCat->fetch();

    if ($category) {
        $stmt = $pdo->prepare(
            'SELECT id, titre, resume, slug, image, meta_title, meta_description, created_at AS published_at
             FROM articles
             WHERE (statut IS NULL OR statut LIKE \'publi%\') AND categorie = :slug
             ORDER BY created_at DESC'
        );
        $stmt->execute(['slug' => $category['slug']]);
        $articles = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = "Impossible de charger la catégorie.";
    error_log('[frontoffice-categorie] DB error: ' . $e->getMessage());
}

if (!$category && $errorMessage === '') {
    http_response_code(404);
    $errorMessage = 'Catégorie introuvable.';
}

$pageTitle = $category ? ($category['nom'] . ' — Guerre en Iran') : 'Catégorie';
$metaDescription = $category ? ('Articles classés dans ' . $category['nom']) : '';
$metaImage = '/uploads/image2.jpg';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container layout-listing">
    <section class="headline">
        <p class="eyebrow">Catégorie</p>
        <h1><?php echo htmlspecialchars($category['nom'] ?? 'Catégorie'); ?></h1>
        <p class="lede">Sélection d'articles sur le thème choisi.</p>
    </section>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php elseif (empty($articles)): ?>
        <p>Aucun article pour cette catégorie.</p>
    <?php else: ?>
        <div class="grid grid--listing">
            <?php foreach ($articles as $article): ?>
                <article class="card card--compact">
                    <a class="card__image" href="/article/<?php echo htmlspecialchars($article['slug']); ?>">
                        <img src="/uploads/<?php echo htmlspecialchars($article['image'] ?: 'image3.jpg'); ?>" alt="<?php echo htmlspecialchars($article['meta_description'] ?? $article['resume'] ?? $article['titre']); ?>" loading="lazy">
                    </a>
                    <div class="card__body">
                        <p class="meta">Publié le <?php echo htmlspecialchars(format_date($article['published_at'])); ?></p>
                        <h2 class="card__title"><a href="/article/<?php echo htmlspecialchars($article['slug']); ?>"><?php echo htmlspecialchars($article['titre']); ?></a></h2>
                        <?php if (!empty($article['resume'])): ?>
                            <p class="card__summary"><?php echo htmlspecialchars($article['resume']); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
