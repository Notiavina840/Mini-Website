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
    echo 'Article introuvable.';
    exit;
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonical = 'http://' . $host . '/article/' . $slug;

$article = null;
$latest = [];
$errorMessage = '';

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare(
        'SELECT a.id, a.titre, a.resume, a.contenu, a.slug, a.image, a.meta_title, a.meta_description,
                COALESCE(a.created_at, a.date_publication) AS published_at,
                COALESCE(a.updated_at, a.date_modification) AS updated_at,
                a.categorie, u.username AS auteur
         FROM articles a
         LEFT JOIN utilisateurs u ON a.auteur_id = u.id
         WHERE a.slug = :slug AND (a.statut IS NULL OR a.statut LIKE \'publi%\')
         LIMIT 1'
    );
    $stmt->execute(['slug' => $slug]);
    $article = $stmt->fetch();

    if ($article) {
        $latest = fetch_latest_articles($pdo, 5, $slug);
    }
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = "Erreur serveur lors du chargement de l'article.";
    error_log('[frontoffice-article] DB error: ' . $e->getMessage());
}

if (!$article) {
    if ($errorMessage === '') {
        http_response_code(404);
        $errorMessage = 'Article introuvable.';
        error_log('[frontoffice-article] Article introuvable pour le slug: ' . $slug);
    }
}

$metaTitle = $article['meta_title'] ?? ($article['titre'] ?? 'Article');
$metaDescription = $article['meta_description'] ?? ($article['resume'] ?? '');
$metaImage = '/uploads/' . (($article['image'] ?? '') !== '' ? $article['image'] : 'image1.jpg');
$pageTitle = $metaTitle;

$readingTime = 0;
if ($article && !empty($article['contenu'])) {
    $words = str_word_count(strip_tags($article['contenu']));
    $readingTime = max(1, (int) ceil($words / 200));
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container article-layout">
    <div class="article-main">
        <a class="breadcrumb" href="/articles">← Retour aux articles</a>

        <?php if ($errorMessage !== '' && !$article): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php elseif ($article): ?>
            <p class="eyebrow">Conflit Iran</p>
            <h1 class="article-title"><?php echo htmlspecialchars($article['titre']); ?></h1>
            <div class="article-meta">
                <span>Par <?php echo htmlspecialchars($article['auteur'] ?: 'Rédaction'); ?></span>
                <span>Publié le <?php echo htmlspecialchars(format_date($article['published_at'])); ?></span>
                <?php if ($readingTime > 0): ?>
                    <span><?php echo $readingTime; ?> min de lecture</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($article['image'])): ?>
                <figure class="article-hero">
                    <img src="/uploads/<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['meta_description'] ?? $article['resume'] ?? $article['titre']); ?>" loading="eager" fetchpriority="high">
                </figure>
            <?php endif; ?>

            <?php if (!empty($article['resume'])): ?>
                <p class="chapeau"><?php echo htmlspecialchars($article['resume']); ?></p>
            <?php endif; ?>

            <article class="article-body">
                <?php echo $article['contenu']; ?>
            </article>
        <?php endif; ?>
    </div>

    <aside class="sidebar" aria-label="Derniers articles">
        <div class="sidebar__header">
            <p class="eyebrow">Le fil info</p>
            <h2>Derniers articles</h2>
        </div>
        <?php if (!empty($latest)): ?>
            <ul class="sidebar-list">
                <?php foreach ($latest as $item): ?>
                    <li class="sidebar-item">
                        <a href="/article/<?php echo htmlspecialchars($item['slug']); ?>" class="sidebar-link">
                            <span class="sidebar-title"><?php echo htmlspecialchars($item['titre']); ?></span>
                            <span class="sidebar-date"><?php echo htmlspecialchars(format_date($item['published_at'])); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="sidebar-empty">Aucun autre article pour le moment.</p>
        <?php endif; ?>
    </aside>
</div>

<?php if ($article): ?>
<script type="application/ld+json">
<?php echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $article['titre'],
    'description' => $metaDescription,
    'image' => $metaImage,
    'datePublished' => $article['published_at'],
    'dateModified' => $article['updated_at'] ?? $article['published_at'],
    'author' => [
        '@type' => 'Person',
        'name' => $article['auteur'] ?: 'Rédaction',
    ],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $canonical,
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
