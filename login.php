<?php
require_once __DIR__ . '/utils.php';
start_secure_session();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username && $password) {
        $resp = api_request('POST', '/login', ['username' => $username, 'password' => $password]);
        if ($resp && isset($resp['access_token'])) {
            $_SESSION['token'] = $resp['access_token'];
            header('Location: /');
            exit;
        }
        $errors[] = 'Identifiants invalides.';
    } else {
        $errors[] = 'Champs requis.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="icon" href="/assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/css/site.css">
</head>
<body>
<header class="site-header">
    <h1><a href="/">Kopo Forum</a></h1>
</header>
<main>
    <?php if ($errors): ?>
        <div class="errors">
            <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="/login.php">
        <label>Nom d'utilisateur
            <input type="text" name="username" required>
        </label>
        <label>Mot de passe
            <input type="password" name="password" required>
        </label>
        <button type="submit">Se connecter</button>
    </form>
    <p>Pas encore de compte ? <a href="/register.php">Inscription</a></p>
</main>
<footer class="site-footer">&copy; 2025 Kopo</footer>
</body>
</html>
