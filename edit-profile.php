<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

// Vérifier si connecté
if (!$api->isLoggedIn()) {
    header('Location: index.php?route=login');
    exit;
}

$user = $api->getCurrentUser();
$user_id = $user['id'];

// Pré-remplir avec données actuelles
$current_username = $user['username'];
$current_email = $user['email'] ?? '';
$current_bio = $user['bio'] ?? '';
$current_avatar = $user['avatar_url'] ?? '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $username = sanitize_string($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $bio = sanitize_string($_POST['bio']);
    $avatar_url = filter_var($_POST['avatar_url'], FILTER_SANITIZE_URL);
    
    // Validation des champs
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = "Le nom d'utilisateur doit comporter entre 3 et 20 caractères (lettres, chiffres ou underscore).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (!empty($password) || !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas.";
        } elseif (strlen($password) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères.";
        }
    } else {
        try {
            // Préparer données pour mise à jour du compte (hors bio)
            $data = [
                'username'   => $username,
                'email'      => $email,
                'avatar_url' => $avatar_url
            ];
            if (!empty($password)) {
                $data['password'] = $password;
            }

            // Envoi à l'API (PUT /users/{id}) pour les infos du compte
            $api->request('/users/' . $user_id, 'PUT', $data, true);

            // Mise à jour de la bio via le nouvel endpoint
            $api->request('/users/' . $user_id . '/bio', 'PUT', ['bio' => $bio], true);

            // Mettre à jour la session
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['bio'] = $bio;
            $_SESSION['user']['avatar_url'] = $avatar_url;

            $success = "Profil mis à jour avec succès.";

            // Pour éviter le resubmit
            header('Location: edit-profile.php?updated=1');
            exit;

        } catch (Exception $e) {
            $error = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Message après redirection
if (isset($_GET['updated'])) {
    $success = "Profil mis à jour avec succès.";
}

$page_title = "Modifier mon profil";
$page_description = "Éditez les informations de votre profil sur KOPO Forum";

include 'header.php';
?>

<div class="container" style="max-width: 600px; margin: 2rem auto;">
    <div class="forum-container">
        <div class="forum-header">
            <h1 style="color: var(--white); margin: 0; text-align: center;">Modifier mon profil</h1>
        </div>
        <div style="padding: 2rem;">
            
            <?php if (!empty($error)): ?>
                <div class="notification notification-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="notification notification-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="edit-profile.php">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($current_username); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($current_email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                </div>
                

                <div class="form-group">
                    <label for="bio" class="form-label">Biographie</label>
                    <textarea id="bio" name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($current_bio); ?></textarea>
                </div>


                <div class="form-group">
                    <label for="avatar_url" class="form-label">URL de l'image de profil</label>
                    <input type="url" id="avatar_url" name="avatar_url" class="form-control" value="<?php echo htmlspecialchars($current_avatar); ?>">
                </div>
                
                <div class="form-group" style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
