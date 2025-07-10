<?php
class NewThreadController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function create() {
        $page_title = "Nouvelle discussion";
        $page_description = "Créer une nouvelle discussion dans le forum";
        if (!$this->api->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = 'new-thread.php';
            $_SESSION['flash_message'] = "Vous devez être connecté pour créer une discussion.";
            $_SESSION['flash_type'] = "error";
            header('Location: index.php?route=login');
            exit();
        }
        $category_id = isset($_GET['category_id']) ? sanitize_int($_GET['category_id']) : null;
        try {
            $categories = $this->api->request('/forum/categories');
        } catch (Exception $e) {
            $categories = [];
        }
        $error = '';
        $success = false;
        $thread_id = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $title = isset($_POST['title']) ? sanitize_string($_POST['title']) : '';
            $content = isset($_POST['content']) ? sanitize_string($_POST['content']) : '';
            $selected_category = isset($_POST['category_id']) ? sanitize_int($_POST['category_id']) : null;
            if (empty($title)) {
                $error = "Le titre ne peut pas être vide.";
            } elseif (empty($content)) {
                $error = "Le contenu ne peut pas être vide.";
            } elseif ($selected_category === null) {
                $error = "Veuillez sélectionner une catégorie.";
            } else {
                try {
                    $user = $this->api->getCurrentUser();
                    $data = [
                        'title' => $title,
                        'content' => $content,
                        'category_id' => $selected_category,
                        'user_id' => $user['id'] ?? null
                    ];
                    $result = $this->api->request('/forum/threads', 'POST', $data, true);
                    if (isset($result['id'])) {
                        $thread_id = $result['id'];
                        $success = true;
                    }
                } catch (Exception $e) {
                    $error = "Erreur lors de la création de la discussion: " . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/new_thread.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
