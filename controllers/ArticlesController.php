<?php
class ArticlesController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Articles";
        $page_description = "Articles sur l'informatique, la cybersécurité et les technologies";
        $page = isset($_GET['page']) ? sanitize_int($_GET['page']) : 1;
        $tag_id = isset($_GET['tag']) ? sanitize_int($_GET['tag']) : 0;
        $skip = ($page - 1) * 9;
        try {
            $query = '/articles?skip=' . $skip . '&limit=9';
            if ($tag_id > 0) {
                $query .= '&tag=' . $tag_id;
            }
            $articles_data = $this->api->request($query);
            $count_query = '/articles/count';
            if ($tag_id > 0) {
                $count_query .= '?tag=' . $tag_id;
            }
            $total_count = $this->api->request($count_query);
            $total_pages = ceil(($total_count['count'] ?? 1) / 9);
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des articles: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            $articles_data = [];
            $total_pages = 1;
        }

        // Charger la liste des tags séparément pour ne pas bloquer l'affichage des articles
        try {
            $tags = $this->api->request('/tags');
        } catch (Exception $e) {
            // Si l'API des tags n'est pas disponible, on continue sans les tags
            $tags = [];
        }

        $tag_name = '';
        if ($tag_id > 0 && !empty($tags)) {
            foreach ($tags as $t) {
                if ((int)$t['id'] === $tag_id) {
                    $tag_name = $t['name'];
                    break;
                }
            }
        }

        // Charger quelques articles récents pour la sidebar, ignorer les erreurs
        try {
            $recent_articles = $this->api->request('/articles?skip=0&limit=5');
        } catch (Exception $e) {
            $recent_articles = [];
        }
        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/articles.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
