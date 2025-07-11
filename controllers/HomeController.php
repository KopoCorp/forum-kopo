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
            // Get at least 3 published articles for the home page
            $articles = $this->api->request('/articles?skip=0&limit=10');
            $latest_articles = filter_published(is_array($articles) ? $articles : []);
            if (is_array($latest_articles)) {
                usort($latest_articles, function ($a, $b) {
                    $dateA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
                    $dateB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
                    return $dateB <=> $dateA; // newest first
                });
                $latest_articles = array_slice($latest_articles, 0, 3);
            }
            // Get forum categories
            $categories = $this->api->request('/forum/categories');
            if (is_array($categories)) {
                foreach ($categories as &$cat) {
                    $catThread = $cat['thread_count'] ?? null;
                    $catReply = $cat['reply_count'] ?? null;
                    if ($catThread === null || $catReply === null) {
                        try {
                            $threads = $this->api->request('/forum/threads?category_id=' . $cat['id']);
                            $catThread = is_array($threads) ? count($threads) : 0;
                            $catReply = 0;
                            if (is_array($threads)) {
                                foreach ($threads as $th) {
                                    $catReply += $th['reply_count'] ?? 0;
                                }
                            }
                        } catch (Exception $e) {
                            $catThread = 0;
                            $catReply = 0;
                        }
                    }
                    $cat['thread_count'] = $catThread;
                    $cat['reply_count'] = $catReply;
                    $cat['content_score'] = $catThread + $catReply;
                }
                unset($cat);
                usort($categories, function ($a, $b) {
                    return ($b['content_score'] ?? 0) <=> ($a['content_score'] ?? 0);
                });
            }
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
