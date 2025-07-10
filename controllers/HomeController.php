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
            // Get latest articles
            $latest_articles = $this->api->request('/articles?skip=0&limit=3');
            // Get forum categories
            $categories = $this->api->request('/forum/categories');
            // Get popular threads
            $popular_threads = $this->api->request('/forum/threads?skip=0&limit=5');
            // Load latest CERT-FR alert
            $alerts = fetch_cert_alerts(1);
            $latest_security_alert = $alerts[0] ?? null;
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des données: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            $latest_security_alert = null;
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/home.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
