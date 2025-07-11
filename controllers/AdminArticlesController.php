<?php
class AdminArticlesController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Gestion des articles";
        $page_description = "Administrer les articles publiés";

        if (!$this->api->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = 'admin/articles.php';
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
                    $this->api->request('/admin/articles/' . $id, 'DELETE', [], true);
                    $success = "Article supprimé.";
                } elseif ($action === 'edit' && $id) {
                    $title = sanitize_string($_POST['title'] ?? '');
                    $content = sanitize_string($_POST['content'] ?? '');
                    $is_pub = isset($_POST['is_pub']);
                    $this->api->request('/articles/' . $id, 'PATCH', [
                        'title' => $title,
                        'content' => $content,
                        'is_pub' => $is_pub
                    ], true);
                    $success = "Article mis à jour.";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        try {
            $articles = $this->api->request('/admin/articles?limit=100', 'GET', [], true);
        } catch (Exception $e) {
            $articles = [];
            if (empty($error)) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/admin/articles.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
