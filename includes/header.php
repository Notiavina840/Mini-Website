<?php
// En-tête commun frontoffice

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Guerre en Iran — Analyses et actualités';
$metaDescription = $metaDescription ?? "Suivez l'actualité et les analyses sur la guerre en Iran : contexte, acteurs, chronologie et impacts.";
$canonical = $canonical ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/'));
$metaImage = $metaImage ?? '/uploads/image1.jpg';
$robots = $robots ?? 'index, follow';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="<?php echo htmlspecialchars($robots); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <link rel="stylesheet" href="/assets/css/front.css">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($metaImage); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($metaImage); ?>">
</head>
<body>
<header class="masthead">
    <div class="container masthead__inner">
        <a class="brand" href="/articles">Guerre en Iran</a>
        <nav class="main-nav" aria-label="Navigation principale">
            <a href="/articles" class="nav-link">Accueil</a>
            <a href="/categorie/chronologie" class="nav-link">Chronologie</a>
            <a href="/categorie/geopolitique" class="nav-link">Géopolitique</a>
            <a href="/backoffice/login.php" class="nav-link nav-link--muted">Back-office</a>
        </nav>
    </div>
</header>
<main class="page-shell">
