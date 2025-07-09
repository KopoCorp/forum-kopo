<?php
require_once 'config.php';
require_once 'api.php';

$page_title = "Connexion";
$page_description = "Connectez-vous à votre compte Kopo Forum";

// Check if user is already logged in
if ($api->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $result = $api->login($username, $password);
            
            // If we reach here, login was successful
            $_SESSION['flash_message'] = "Connexion réussie! Bienvenue " . htmlspecialchars($username) . ".";
            $_SESSION['flash_type'] = "success";
            
            // Redirect to home page or previous page
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']);
            
            header('Location: ' . $redirect);
            exit();
        } catch (Exception $e) {
            $error = "Erreur de connexion: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="container" style="max-width: 500px; margin: 2rem auto;">
    <div class="forum-container">
        <div class="forum-header">
            <h1 style="color: var(--white); margin: 0; text-align: center;">Connexion</h1>
        </div>
        <div style="padding: 2rem;">
            <?php if (!empty($error)): ?>
                <div class="notification notification-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="login.php" data-validate>
                <div class="form-group">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="form-check">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="forgot-password.php" style="font-size: 0.875rem;">Mot de passe oublié?</a>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Connexion</button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 1rem;">
                <p>Vous n'avez pas de compte? <a href="register.php">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>