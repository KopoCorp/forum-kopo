<?php
require_once 'config.php';
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
$current_bio = $user['bio'] ?? '';
$current_avatar = $user['avatar_url'] ?? '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $bio = trim($_POST['bio']);
    $avatar_url = trim($_POST['avatar_url']);
    
    // Validation username
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = "Le nom d'utilisateur doit comporter entre 3 et 20 caractères (lettres, chiffres ou underscore).";
    } else {
        try {
            // Préparer données
            $data = [
                'username' => $username,
                'bio' => $bio,
                'avatar_url' => $avatar_url
            ];

            // Envoi à l'API (PUT /users/{id})
            $api->request('/users/' . $user_id, 'PUT', $data, true);

            // Mettre à jour la session
            $_SESSION['user']['username'] = $username;
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
                <div class="form-group">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($current_username); ?>" required>
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
