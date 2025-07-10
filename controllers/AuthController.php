<?php
class AuthController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function login() {
        $page_title = "Connexion";
        $page_description = "Connectez-vous à votre compte Kopo Forum";
        if ($this->api->isLoggedIn()) {
            header('Location: index.php');
            exit();
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            if (empty($username) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                try {
                    $this->api->login($username, $password);
                    $_SESSION['flash_message'] = "Connexion réussie! Bienvenue " . htmlspecialchars($username) . ".";
                    $_SESSION['flash_type'] = "success";
                    $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirect);
                    exit();
                } catch (Exception $e) {
                    $error = "Erreur de connexion: " . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/auth/login.php';
        require __DIR__ . '/../views/templates/footer.php';
    }

    public function register() {
        $page_title = "Inscription";
        $page_description = "Créez un compte sur Kopo Forum";
        if ($this->api->isLoggedIn()) {
            header('Location: index.php');
            exit();
        }
        $error = '';
        $success = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "Veuillez remplir tous les champs.";
            } elseif ($password !== $confirm_password) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                try {
                    $this->api->request('/users', 'POST', [
                        'username' => $username,
                        'email' => $email,
                        'password' => $password
                    ]);
                    $success = true;
                } catch (Exception $e) {
                    $error = "Erreur d'inscription: " . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/auth/register.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
