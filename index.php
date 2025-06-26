<?php
require_once __DIR__ . '/utils.php';
start_secure_session();
$token = $_SESSION['token'] ?? null;

$articles = fetch_json('/articles', ['limit' => 5], $token) ?? [];
$threads  = fetch_json('/forum/threads', ['limit' => 5], $token) ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Kopo Forum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/site.css">
</head>
<body>
    <header class="site-header">
        <h1>Kopo Forum</h1>
        <nav>
            <a href="#articles">Articles</a>
            <a href="#forum">Forum</a>
            <?php if ($token): ?>
                <a href="/logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="/login.php">Connexion</a>
                <a href="/register.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <section id="articles">
            <h2 class="section-title">Articles récents</h2>
            <ul class="list" id="articles-list">
                <?php foreach ($articles as $a): ?>
                <li><a href="/article.php?id=<?= htmlspecialchars($a['id']) ?>">
                    <?= htmlspecialchars($a['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <section id="forum">
            <h2 class="section-title">Derniers sujets du forum</h2>
            <ul class="list" id="threads-list">
                <?php foreach ($threads as $t): ?>
                <li><a href="/thread.php?id=<?= htmlspecialchars($t['id']) ?>">
                    <?= htmlspecialchars($t['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
    <footer class="site-footer">&copy; 2025 Kopo</footer>
    <script src="/script.js"></script>
</body>
</html>
