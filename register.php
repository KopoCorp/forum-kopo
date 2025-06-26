<?php
require_once __DIR__ . '/utils.php';
start_secure_session();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Jeton CSRF invalide.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';
        if ($username && $password && $confirm) {
            if ($password !== $confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Le mot de passe doit comporter au moins 8 caractères.';
            } else {
                $resp = api_request('POST', '/register', ['username' => $username, 'password' => $password]);
                if ($resp && !isset($resp['error'])) {
                    header('Location: /login.php?registered=1');
                    exit;
                }
                $errors[] = isset($resp['error']) ? $resp['error'] : "Inscription échouée.";
            }
        } else {
            $errors[] = 'Champs requis.';
        }
    }
}
$csrf = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
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
    <form method="post" action="/register.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <label>Nom d'utilisateur
            <input type="text" name="username" required>
        </label>
        <label>Mot de passe
            <input type="password" name="password" required>
        </label>
        <label>Confirmez le mot de passe
            <input type="password" name="confirm" required>
        </label>
        <button type="submit">Créer le compte</button>
    </form>
    <p>Déjà inscrit ? <a href="/login.php">Connexion</a></p>
</main>
<footer class="site-footer">&copy; 2025 Kopo</footer>
</body>
</html>
