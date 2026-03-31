<?php
// Dynamic sitemap generator
header('Content-Type: application/xml; charset=utf-8');

$baseUrl = 'http://localhost:8080';
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

    // Fetch all articles (no status column in current schema; include all rows)
    $stmt = $pdo->query("SELECT slug, updated_at, created_at FROM articles ORDER BY updated_at DESC");
    $articles = $stmt->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Internal error generating sitemap';
    exit;
}

// Helper to format lastmod
$formatDate = function ($row) {
    if (!empty($row['updated_at'])) {
        return date('c', strtotime($row['updated_at']));
    }
    if (!empty($row['created_at'])) {
        return date('c', strtotime($row['created_at']));
    }
    return null;
};

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Homepage
echo "  <url>\n";
echo "    <loc>{$baseUrl}/</loc>\n";
echo "    <changefreq>daily</changefreq>\n";
echo "    <priority>1.0</priority>\n";
echo "  </url>\n";

// Articles
foreach ($articles as $article) {
    $slug = $article['slug'] ?? '';
    if ($slug === '') {
        continue;
    }
    $loc = htmlspecialchars($baseUrl . '/articles/' . $slug, ENT_QUOTES, 'UTF-8');
    $lastmod = $formatDate($article);

    echo "  <url>\n";
    echo "    <loc>{$loc}</loc>\n";
    if ($lastmod) {
        echo "    <lastmod>{$lastmod}</lastmod>\n";
    }
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';
