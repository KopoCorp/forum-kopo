<?php
class DevController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Développement";
        $page_description = "Ressources, articles et discussions sur le développement informatique, la programmation et les langages de code";

        // Determine development tag id and map ids to names
        $dev_tag_id = null;
        $tag_map = [];
        try {
            $tags_list = $this->api->request('/tags');
            foreach ($tags_list as $t) {
                if (isset($t['id'])) {
                    $tag_map[$t['id']] = $t['name'] ?? '';
                    if ($dev_tag_id === null && detect_topic([$t['name']], $t['name']) === 'dev') {
                        $dev_tag_id = $t['id'];
                    }
                }
            }
        } catch (Exception $e) {
            $dev_tag_id = null;
            $tag_map = [];
        }

        try {
            // Fetch articles using the development tag when available
            if ($dev_tag_id !== null) {
                $dev_articles = $this->api->request('/articles?tag=' . $dev_tag_id . '&limit=6');
            } else {
                // Fallback to keyword detection on recent articles
                $all_articles = $this->api->request('/articles?limit=20');
                $dev_articles = [];
                foreach ($all_articles as $article) {
                    $tag_names = [];
                    if (isset($article['tags']) && is_array($article['tags'])) {
                        foreach ($article['tags'] as $t) {
                            if (is_array($t) && isset($t['name'])) {
                                $tag_names[] = $t['name'];
                            } elseif (isset($tag_map[$t])) {
                                $tag_names[] = $tag_map[$t];
                            }
                        }
                    }
                    if (detect_topic($tag_names, ($article['title'] ?? '') . ' ' . ($article['content'] ?? '')) === 'dev') {
                        $dev_articles[] = $article;
                    }
                    if (count($dev_articles) >= 6) {
                        break;
                    }
                }
            }

            // Load forum threads and filter them as well
            $all_threads = $this->api->request('/forum/threads?limit=20');
            $dev_threads = [];
            foreach ($all_threads as $thread) {
                if (detect_topic([], ($thread['title'] ?? '') . ' ' . ($thread['content'] ?? '')) === 'dev') {
                    $dev_threads[] = $thread;
                }
                if (count($dev_threads) >= 5) {
                    break;
                }
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des données: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            $dev_articles = [];
            $dev_threads = [];
        }

        // Try to load trending technologies separately so a 404 doesn't clear articles
        try {
            $trending_techs = $this->api->request('/technologies/trending?limit=5');
        } catch (Exception $e) {
            $trending_techs = [];
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/dev.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
