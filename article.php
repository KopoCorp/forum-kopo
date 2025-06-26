<?php
require_once __DIR__ . '/utils.php';
start_secure_session();
$token = $_SESSION['token'] ?? null;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$article = $id ? fetch_json("/articles/$id", [], $token) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $article['title'] ?? 'Article' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/css/site.css">
</head>
<body>
    <header class="site-header">
        <h1><a href="/">Kopo Forum</a></h1>
        <nav>
            <?php if ($token): ?>
                <a href="/logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="/login.php">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>
    <main id="article-content">
        <?php if ($article): ?>
            <h2><?= htmlspecialchars($article['title']) ?></h2>
            <div><?= $article['content'] ?></div>
        <?php else: ?>
            <p>Article introuvable.</p>
        <?php endif; ?>
    </main>
    <footer class="site-footer">&copy; 2025 Kopo</footer>
    <script src="/script.js"></script>
</body>
</html>
