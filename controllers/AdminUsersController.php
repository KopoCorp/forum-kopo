<?php
class AdminUsersController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Gestion des utilisateurs";
        $page_description = "Administrer les comptes utilisateurs";

        if (!$this->api->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = 'admin/users.php';
            header('Location: ../index.php?route=login');
            exit();
        }
        if (!$this->api->isModerator()) {
            $_SESSION['flash_message'] = "Accès réservé aux modérateurs.";
            $_SESSION['flash_type'] = "error";
            header('Location: ../index.php');
            exit();
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $action = sanitize_string($_POST['action'] ?? '');
            $id = isset($_POST['id']) ? sanitize_int($_POST['id']) : null;
            try {
                if ($action === 'delete' && $id) {
                    $this->api->request('/admin/users/' . $id, 'DELETE', [], true);
                    $success = "Utilisateur supprimé.";
                } elseif ($action === 'edit' && $id) {
                    $username = sanitize_string($_POST['username'] ?? '');
                    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                    $is_active = isset($_POST['is_active']);
                    $this->api->request('/users/' . $id, 'PATCH', [
                        'username' => $username,
                        'email' => $email,
                        'is_active' => $is_active
                    ], true);
                    $success = "Utilisateur mis à jour.";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        try {
            $users = $this->api->request('/admin/users?limit=100', 'GET', [], true);
        } catch (Exception $e) {
            $users = [];
            if (empty($error)) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/admin/users.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
