<?php
class AdminCategoriesController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Gestion des catégories";
        $page_description = "Créer, éditer et supprimer les catégories du forum";

        if (!$this->api->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = 'admin/categories.php';
            header('Location: index.php?route=login');
            exit();
        }
        if (!$this->api->isModerator()) {
            $_SESSION['flash_message'] = "Accès réservé aux modérateurs.";
            $_SESSION['flash_type'] = "error";
            header('Location: index.php');
            exit();
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $action = sanitize_string($_POST['action'] ?? '');
            $name = sanitize_string($_POST['name'] ?? '');
            $description = sanitize_string($_POST['description'] ?? '');
            $id = isset($_POST['id']) ? sanitize_int($_POST['id']) : null;
            try {
                if ($action === 'create') {
                    if (empty($name)) {
                        $error = "Le nom de la catégorie est obligatoire.";
                    } else {
                        $this->api->request('/forum/categories', 'POST', [
                            'name' => $name,
                            'description' => $description
                        ], true);
                        $success = "Catégorie créée avec succès.";
                    }
                } elseif ($action === 'edit' && $id) {
                    if (empty($name)) {
                        $error = "Le nom de la catégorie est obligatoire.";
                    } else {
                        $this->api->request('/forum/categories/' . $id, 'PATCH', [
                            'name' => $name,
                            'description' => $description
                        ], true);
                        $success = "Catégorie mise à jour.";
                    }
                } elseif ($action === 'delete' && $id) {
                    $this->api->request('/forum/categories/' . $id, 'DELETE', [], true);
                    $success = "Catégorie supprimée.";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        try {
            $categories = $this->api->request('/forum/categories');
        } catch (Exception $e) {
            $categories = [];
            if (empty($error)) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/admin/categories.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
