<?php
class AdminController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Administration";
        $page_description = "Tableau de bord modérateur";

        if (!$this->api->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = 'index.php?route=admin';
            header('Location: index.php?route=login');
            exit();
        }
        if (!$this->api->isModerator()) {
            $_SESSION['flash_message'] = "Accès réservé aux modérateurs.";
            $_SESSION['flash_type'] = "error";
            header('Location: index.php');
            exit();
        }

        try {
            $stats = calculate_forum_stats($this->api);
        } catch (Exception $e) {
            $stats = [];
        }

        $users = $articles = $threads = $comments = [];
        $alert = null;
        try { $users = $this->api->request('/admin/users?limit=5', 'GET', [], true); } catch (Exception $e) {}
        try { $articles = $this->api->request('/admin/articles?limit=5', 'GET', [], true); } catch (Exception $e) {}
        try { $threads = $this->api->request('/admin/threads?limit=5', 'GET', [], true); } catch (Exception $e) {}
        try { $comments = $this->api->request('/admin/comments?limit=5', 'GET', [], true); } catch (Exception $e) {}
        try { $alert = $this->api->request('/security/alerts/latest'); } catch (Exception $e) { $alert = null; }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/admin/dashboard.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
