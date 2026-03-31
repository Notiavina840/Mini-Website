<?php
$dbHost=getenv('DB_HOST') ?: 'db';
$dbName=getenv('DB_NAME') ?: 'mini_website';
$dbUser=getenv('DB_USER') ?: 'mini_user';
$dbPass=getenv('DB_PASS');
$dbPass = ($dbPass === false) ? '' : $dbPass;

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "OK\n";
    $count = $pdo->query('SELECT count(*) FROM articles')->fetchColumn();
    echo "articles=$count\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage();
}
