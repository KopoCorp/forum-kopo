<?php
class AdminThreadsController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Gestion des discussions";
        $page_description = "Administrer les sujets du forum";

        if (!$this->api->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = 'admin/threads.php';
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
                    $this->api->request('/admin/threads/' . $id, 'DELETE', [], true);
                    $success = "Discussion supprimée.";
                } elseif ($action === 'edit' && $id) {
                    $title = sanitize_string($_POST['title'] ?? '');
                    $content = sanitize_string($_POST['content'] ?? '');
                    $this->api->request('/forum/threads/' . $id, 'PATCH', [
                        'title' => $title,
                        'content' => $content
                    ], true);
                    $success = "Discussion mise à jour.";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        try {
            $threads = $this->api->request('/admin/threads?limit=100', 'GET', [], true);
        } catch (Exception $e) {
            $threads = [];
            if (empty($error)) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/admin/threads.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
