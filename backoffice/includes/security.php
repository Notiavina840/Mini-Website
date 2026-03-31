<?php
// Helpers de sécurité et d'upload pour le backoffice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generate_csrf_token(string $context): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$context] = $token;
    return $token;
}

function verify_csrf_token(?string $token, string $context): void
{
    $expected = $_SESSION['csrf_tokens'][$context] ?? '';
    if (!$token || !$expected || !hash_equals($expected, $token)) {
        http_response_code(400);
        exit('Requête CSRF invalide.');
    }
    unset($_SESSION['csrf_tokens'][$context]);
}

function validate_image_upload(array $file): array
{
    if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [false, "Aucun fichier envoyé.", null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return [false, "Erreur lors du téléversement.", null];
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return [false, "Fichier trop volumineux (2 Mo max).", null];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        return [false, "Format d'image non supporté.", $mime];
    }

    $ext = $allowed[$mime];
    $safeName = uniqid('img_', true) . '.' . $ext;
    return [true, $safeName, $mime];
}

function move_uploaded_image(array $file, string $safeName): bool
{
    $targetDir = __DIR__ . '/../../uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    return move_uploaded_file($file['tmp_name'], $targetDir . $safeName);
}
