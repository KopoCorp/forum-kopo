<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

if (!$api->isLoggedIn()) {
    header('Location: index.php?route=login');
    exit();
}

$user = $api->getCurrentUser();
$user_id = $user['id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $password = $_POST['password'] ?? '';
    if (empty($password)) {
        $error = "Veuillez saisir votre mot de passe.";
    } else {
        try {
            // Re-login to verify password
            $api->login($user['username'], $password);
            // Delete account via API
            $api->request('/users/' . $user_id, 'DELETE', [], true);
            $api->logout();
            $_SESSION['flash_message'] = "Votre compte a été supprimé.";
            $_SESSION['flash_type'] = "success";
            header('Location: index.php');
            exit();
        } catch (Exception $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}

$page_title = "Supprimer mon compte";
$page_description = "Suppression définitive du compte";

include 'header.php';
?>
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo;
            <a href="settings.php" style="color: #999;">Paramètres</a> &raquo;
            Supprimer mon compte
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Supprimer mon compte</h1>
    </div>
</div>

<main class="main-content section">
    <div class="container" style="max-width: 600px;">
        <div class="forum-container">
            <div class="forum-header" style="background-color: var(--accent-red);">
                <h2 style="color: var(--white); margin: 0; text-align: center;">Confirmation</h2>
            </div>
            <div style="padding: 2rem;">
                <?php if (!empty($error)): ?>
                    <div class="notification notification-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <p>Cette action est <strong>irréversible</strong>. Toutes vos données seront perdues.</p>
                <form method="post" action="delete-account.php">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <div class="form-group">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-accent" style="width: 100%; background-color: var(--accent-red);">Supprimer définitivement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
