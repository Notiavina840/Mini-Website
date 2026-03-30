<?php
// Common security helpers for backoffice forms and sessions.

// Start a session with safe cookie params if not already started.
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// Regenerate the session ID to prevent fixation.
function regenerate_session_id_if_needed(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

// Require the user to be authenticated; otherwise redirect to login.
function require_authentication(): void
{
    start_secure_session();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Generate a CSRF token for a given form key and store it in session.
function generate_csrf_token(string $formKey): string
{
    start_secure_session();
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$formKey] = $token;
    return $token;
}

// Verify and consume the CSRF token for a given form key.
function verify_csrf_token(?string $token, string $formKey): void
{
    start_secure_session();
    $valid = isset($_SESSION['csrf_tokens'][$formKey]) &&
        is_string($token) &&
        hash_equals($_SESSION['csrf_tokens'][$formKey], $token);

    unset($_SESSION['csrf_tokens'][$formKey]);

    if (!$valid) {
        http_response_code(400);
        exit('CSRF token invalide.');
    }
}

// Validate an uploaded image: type whitelist, max size, and safe name.
function validate_image_upload(array $file, array $allowedMime = ['image/jpeg', 'image/png', 'image/gif'], int $maxBytes = 2_000_000): array
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Erreur lors de l\'upload du fichier.', null];
    }

    if ($file['size'] > $maxBytes) {
        return [false, 'Fichier trop volumineux.', null];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedMime, true)) {
        return [false, 'Type de fichier non autorise.', null];
    }

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];
    $ext = $extensions[$mime] ?? 'dat';
    $safeName = bin2hex(random_bytes(16)) . '.' . $ext;

    return [true, $safeName, $mime];
}

// Move an uploaded file to the uploads directory with a safe name.
function move_uploaded_image(array $file, string $safeName): bool
{
    $uploadsDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    $destination = $uploadsDir . '/' . $safeName;
    return move_uploaded_file($file['tmp_name'], $destination);
}
