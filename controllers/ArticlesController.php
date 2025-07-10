<?php
class ArticlesController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Articles";
        $page_description = "Articles sur l'informatique, la cybersécurité et les technologies";
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $tag = isset($_GET['tag']) ? $_GET['tag'] : '';
        $skip = ($page - 1) * 9;
        try {
            $query = '/articles?skip=' . $skip . '&limit=9';
            if (!empty($tag)) {
                $query .= '&tag=' . urlencode($tag);
            }
            $articles_data = $this->api->request($query);
            $total_count = $this->api->request('/articles/count' . (!empty($tag) ? '?tag=' . urlencode($tag) : ''));
            $total_pages = ceil(($total_count['count'] ?? 1) / 9);
            $tags = $this->api->request('/tags');
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des articles: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            $articles_data = [];
            $total_pages = 1;
            $tags = [];
        }
        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/articles.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
