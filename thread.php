<?php
require_once __DIR__ . '/utils.php';
start_secure_session();
$token = $_SESSION['token'] ?? null;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$thread  = $id ? fetch_json("/forum/threads/$id", [], $token) : null;
$replies = $id ? fetch_json("/forum/threads/$id/replies", [], $token) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $thread['title'] ?? 'Discussion' ?></title>
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
    <main id="thread-content">
        <?php if ($thread): ?>
            <h2><?= htmlspecialchars($thread['title']) ?></h2>
            <div><?= $thread['content'] ?></div>
            <?php if ($replies): ?>
                <h3 class="section-title">Réponses</h3>
                <ul class="list">
                    <?php foreach ($replies as $r): ?>
                        <li><?= htmlspecialchars($r['content']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php else: ?>
            <p>Fil introuvable.</p>
        <?php endif; ?>
    </main>
    <footer class="site-footer">&copy; 2025 Kopo</footer>
    <script src="/script.js"></script>
</body>
</html>
