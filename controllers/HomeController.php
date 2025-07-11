<?php
class HomeController {
    private $api;

    public function __construct($api) {
        $this->api = $api;
    }

    public function index() {
        $page_title = 'Accueil';
        $page_description = "Forum de discussion et actualités sur l'informatique, la cybersécurité et les technologies";
        try {
            // Get latest articles and ensure newest first
            $latest_articles = filter_published($this->api->request('/articles?skip=0&limit=3'));
            if (is_array($latest_articles)) {
                usort($latest_articles, function ($a, $b) {
                    $dateA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
                    $dateB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
                    return $dateB <=> $dateA; // newest first
                });
            }
            // Get forum categories
            $categories = $this->api->request('/forum/categories');
            // Get popular threads
            $popular_threads = $this->api->request('/forum/threads?skip=0&limit=5');
            // Latest security alert from CERT-FR
            $alerts = $this->api->request('/security/alerts?limit=1');
            $latest_security_alert = !empty($alerts) ? $alerts[0] : null;
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des données: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/home.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
