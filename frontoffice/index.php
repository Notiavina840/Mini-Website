<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

function format_date(?string $value): string
{
    return $value ? date('d F Y', strtotime($value)) : '';
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonical = 'http://' . $host . '/articles';

$articles = [];
$errorMessage = '';

try {
    $pdo = get_pdo();
    $stmt = $pdo->query(
        'SELECT id, titre, resume, slug, image, meta_title, meta_description, created_at AS published_at
         FROM articles
         WHERE (statut IS NULL OR statut LIKE \'publi%\')
         ORDER BY created_at DESC'
    );
    $articles = $stmt->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = "Impossible de charger les articles. Vérifiez la connexion à la base de données.";
    error_log('[frontoffice-index] DB error: ' . $e->getMessage());
}

$metaImage = '/uploads/' . (($articles[0]['image'] ?? '') !== '' ? $articles[0]['image'] : 'image1.jpg');
$pageTitle = 'Articles — Guerre en Iran';
$metaDescription = "Actualités, analyses et chronologie du conflit iranien, mises à jour en continu.";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container layout-listing">
    <section class="headline">
        <p class="eyebrow">En continu</p>
        <h1>Articles sur la guerre en Iran</h1>
        <p class="lede">Actualités et analyses publiées récemment.</p>
    </section>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php elseif (empty($articles)): ?>
        <p>Aucun article disponible pour le moment.</p>
    <?php else: ?>
        <?php $featured = $articles[0]; $rest = array_slice($articles, 1); ?>
        <article class="card card--featured">
            <a class="card__image" href="/article/<?php echo htmlspecialchars($featured['slug']); ?>">
                <img src="/uploads/<?php echo htmlspecialchars($featured['image'] && $featured['image'] !== '' ? $featured['image'] : 'image1.jpg'); ?>" alt="<?php echo htmlspecialchars($featured['meta_description'] ?? $featured['resume'] ?? $featured['titre']); ?>" loading="eager" fetchpriority="high">
            </a>
            <div class="card__body">
                <p class="meta">Publié le <?php echo htmlspecialchars(format_date($featured['published_at'])); ?></p>
                <h2 class="card__title"><a href="/article/<?php echo htmlspecialchars($featured['slug']); ?>"><?php echo htmlspecialchars($featured['titre']); ?></a></h2>
                <?php if (!empty($featured['resume'])): ?>
                    <p class="card__summary"><?php echo htmlspecialchars($featured['resume']); ?></p>
                <?php endif; ?>
                <a class="card__cta" href="/article/<?php echo htmlspecialchars($featured['slug']); ?>">Lire l'article</a>
            </div>
        </article>

        <div class="grid grid--listing">
            <?php foreach ($rest as $article): ?>
                <article class="card card--compact">
                    <a class="card__image" href="/article/<?php echo htmlspecialchars($article['slug']); ?>">
                        <img src="/uploads/<?php echo htmlspecialchars($article['image'] && $article['image'] !== '' ? $article['image'] : 'image2.jpg'); ?>" alt="<?php echo htmlspecialchars($article['meta_description'] ?? $article['resume'] ?? $article['titre']); ?>" loading="lazy">
                    </a>
                    <div class="card__body">
                        <p class="meta">Publié le <?php echo htmlspecialchars(format_date($article['published_at'])); ?></p>
                        <h3 class="card__title"><a href="/article/<?php echo htmlspecialchars($article['slug']); ?>"><?php echo htmlspecialchars($article['titre']); ?></a></h3>
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
