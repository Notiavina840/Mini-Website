<?php
// Connexion PDO centralisée pour frontoffice

declare(strict_types=1);

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: 'mini_website';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS');
    $dbPass = $dbPass === false ? '' : $dbPass;

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

    try {
        $pdo = new PDO(
            $dsn,
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (Throwable $e) {
        http_response_code(500);
        error_log('[db] Connexion échouée: ' . $e->getMessage());
        echo 'Erreur de connexion à la base de données.';
        exit;
    }

    return $pdo;
}

function fetch_latest_articles(PDO $pdo, int $limit = 5, ?string $excludeSlug = null): array
{
                    $sql = 'SELECT id, titre, resume, slug, image, COALESCE(created_at, date_publication) AS published_at
                        FROM articles
                        WHERE (statut IS NULL OR statut LIKE \'publi%\')';

    $params = [];
    if ($excludeSlug !== null) {
        $sql .= ' AND slug != :excludeSlug';
        $params['excludeSlug'] = $excludeSlug;
    }

    $sql .= ' ORDER BY COALESCE(created_at, date_publication) DESC LIMIT :limit';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll();
}
