<?php
class CybersecuriteController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Cybersécurité";
        $page_description = "Information et discussions sur la cybersécurité, les vulnérabilités, la sécurité informatique et la protection des données";

        // Retrieve tag id for security and map tag ids to names
        $security_tag_id = null;
        $tag_map = [];
        try {
            $tags_list = $this->api->request('/tags');
            foreach ($tags_list as $t) {
                if (isset($t['id'])) {
                    $tag_map[$t['id']] = $t['name'] ?? '';
                    if ($security_tag_id === null && detect_topic([$t['name']], $t['name']) === 'security') {
                        $security_tag_id = $t['id'];
                    }
                }
            }
        } catch (Exception $e) {
            $security_tag_id = null;
            $tag_map = [];
        }

        try {
            // Fetch articles using the security tag when available
            if ($security_tag_id !== null) {
                $security_articles = filter_published($this->api->request('/articles?tag=' . $security_tag_id . '&limit=6'));
            } else {
                // Fallback to keyword detection on recent articles
                $all_articles = filter_published($this->api->request('/articles?limit=20'));
                $security_articles = [];
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
                    if (detect_topic($tag_names, ($article['title'] ?? '') . ' ' . ($article['content'] ?? '')) === 'security') {
                        $security_articles[] = $article;
                    }
                    if (count($security_articles) >= 6) {
                        break;
                    }
                }
            }

            // Load forum threads and filter them as well
            $all_threads = $this->api->request('/forum/threads?limit=20');
            $security_threads = [];
            foreach ($all_threads as $thread) {
                if (detect_topic([], ($thread['title'] ?? '') . ' ' . ($thread['content'] ?? '')) === 'security') {
                    $security_threads[] = $thread;
                }
                if (count($security_threads) >= 5) {
                    break;
                }
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des données: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            $security_articles = [];
            $security_threads = [];
        }

        // Load security alerts separately to avoid wiping article data if unavailable
        try {
            $security_alerts = $this->api->request('/security/alerts?limit=3');
        } catch (Exception $e) {
            $security_alerts = [];
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/cyber_securite.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
