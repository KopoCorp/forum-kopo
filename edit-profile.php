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
$current_bio = $user['bio'] ?? '';
$current_avatar = $user['avatar_url'] ?? '';
$current_display_name = $user['display_name'] ?? '';
$current_location = $user['location'] ?? '';
$current_website = $user['website'] ?? '';
$current_birth_date = $user['birth_date'] ?? '';
$current_gender = $user['gender'] ?? '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $username = sanitize_string($_POST['username']);
    $bio = sanitize_string($_POST['bio']);
    $avatar_url = filter_var($_POST['avatar_url'], FILTER_SANITIZE_URL);

    $display_name = sanitize_string($_POST['display_name']);
    $location = sanitize_string($_POST['location']);
    $website = filter_var($_POST['website'], FILTER_SANITIZE_URL);
    $birth_date = sanitize_string($_POST['birth_date']);
    $gender = sanitize_string($_POST['gender']);
    
    // Validation username
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = "Le nom d'utilisateur doit comporter entre 3 et 20 caractères (lettres, chiffres ou underscore).";
    } elseif (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $error = "URL de site invalide.";
    } elseif (!empty($birth_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date)) {
        $error = "Format de date invalide.";
    } else {
        try {
            // Préparer données pour mise à jour du compte (hors bio)
            $data = [
                'username'      => $username,
                'avatar_url'    => $avatar_url
            ];

            // Envoi à l'API (PUT /users/{id}) pour les infos du compte
            $api->request('/users/' . $user_id, 'PUT', $data, true);

            // Mise à jour de la bio via le nouvel endpoint
            $api->request('/users/' . $user_id . '/bio', 'PUT', ['bio' => $bio], true);

            // Mise à jour des informations de profil étendu
            $profileData = [
                'display_name' => $display_name,
                'website'      => $website,
                'location'     => $location,
                'birth_date'   => $birth_date,
                'gender'       => $gender
            ];
            $api->request('/users/' . $user_id . '/profile', 'PUT', $profileData, true);

            // Mettre à jour la session
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['bio'] = $bio;
            $_SESSION['user']['avatar_url'] = $avatar_url;
            $_SESSION['user']['display_name'] = $display_name;
            $_SESSION['user']['website'] = $website;
            $_SESSION['user']['location'] = $location;
            $_SESSION['user']['birth_date'] = $birth_date;
            $_SESSION['user']['gender'] = $gender;

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
                    <label for="display_name" class="form-label">Nom affiché</label>
                    <input type="text" id="display_name" name="display_name" class="form-control" value="<?php echo htmlspecialchars($current_display_name); ?>">
                </div>

                <div class="form-group">
                    <label for="bio" class="form-label">Biographie</label>
                    <textarea id="bio" name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($current_bio); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="website" class="form-label">Site Web</label>
                    <input type="url" id="website" name="website" class="form-control" value="<?php echo htmlspecialchars($current_website); ?>">
                </div>

                <div class="form-group">
                    <label for="location" class="form-label">Localisation</label>
                    <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($current_location); ?>">
                </div>

                <div class="form-group">
                    <label for="birth_date" class="form-label">Date de naissance</label>
                    <input type="date" id="birth_date" name="birth_date" class="form-control" value="<?php echo htmlspecialchars($current_birth_date); ?>">
                </div>

                <div class="form-group">
                    <label for="gender" class="form-label">Genre</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="" <?php echo $current_gender === '' ? 'selected' : ''; ?>>Non spécifié</option>
                        <option value="male" <?php echo $current_gender === 'male' ? 'selected' : ''; ?>>Homme</option>
                        <option value="female" <?php echo $current_gender === 'female' ? 'selected' : ''; ?>>Femme</option>
                        <option value="other" <?php echo $current_gender === 'other' ? 'selected' : ''; ?>>Autre</option>
                    </select>
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
