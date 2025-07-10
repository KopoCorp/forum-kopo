<?php
class ForumsController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Forums";
        $page_description = "Forums de discussion sur l'informatique, la cybersécurité et les technologies";
        try {
            $categories = $this->api->request('/forum/categories');
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des forums: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            $categories = [];
        }
        $category_groups = [
            'Informatique & Développement' => [],
            'Cybersécurité & Réseaux' => [],
            'Gaming & Hardware' => [],
            'Général' => []
        ];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $category_name = $category['name'] ?? '';
                $group = 'Général';
                if (stripos($category_name, 'dev') !== false ||
                    stripos($category_name, 'prog') !== false ||
                    stripos($category_name, 'code') !== false ||
                    stripos($category_name, 'info') !== false) {
                    $group = 'Informatique & Développement';
                } elseif (stripos($category_name, 'secu') !== false ||
                          stripos($category_name, 'cyber') !== false ||
                          stripos($category_name, 'hack') !== false ||
                          stripos($category_name, 'réseau') !== false ||
                          stripos($category_name, 'network') !== false) {
                    $group = 'Cybersécurité & Réseaux';
                } elseif (stripos($category_name, 'game') !== false ||
                          stripos($category_name, 'jeu') !== false ||
                          stripos($category_name, 'hardware') !== false ||
                          stripos($category_name, 'matériel') !== false) {
                    $group = 'Gaming & Hardware';
                }
                $category_groups[$group][] = $category;
            }
        }
        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/forums.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
