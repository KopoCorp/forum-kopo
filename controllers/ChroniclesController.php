<?php
class ChroniclesController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Chroniques";
        $page_description = "Chroniques, récits et histoires de la communauté Kopo - Découvrez les expériences et témoignages de nos membres";

        // Determine chronicles tag id and map ids to names
        $chronicles_tag_id = null;
        $tag_map = [];
        try {
            $tags_list = $this->api->request('/tags');
            foreach ($tags_list as $t) {
                if (isset($t['id'])) {
                    $tag_map[$t['id']] = $t['name'] ?? '';
                    if ($chronicles_tag_id === null && (
                        stripos($t['name'], 'chronique') !== false ||
                        stripos($t['name'], 'histoire') !== false ||
                        stripos($t['name'], 'récit') !== false ||
                        stripos($t['name'], 'témoignage') !== false
                    )) {
                        $chronicles_tag_id = $t['id'];
                    }
                }
            }
        } catch (Exception $e) {
            $chronicles_tag_id = null;
            $tag_map = [];
        }

        try {
            // Fetch articles using the chronicles tag when available
            if ($chronicles_tag_id !== null) {
                $chronicles_articles = filter_published($this->api->request('/articles?tag=' . $chronicles_tag_id . '&limit=6'));
            } else {
                // Fallback to keyword detection on recent articles
                $all_articles = filter_published($this->api->request('/articles?limit=20'));
                $chronicles_articles = [];
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
                    $content_text = ($article['title'] ?? '') . ' ' . ($article['content'] ?? '');
                    if (stripos($content_text, 'chronique') !== false ||
                        stripos($content_text, 'histoire') !== false ||
                        stripos($content_text, 'récit') !== false ||
                        stripos($content_text, 'témoignage') !== false ||
                        stripos($content_text, 'expérience') !== false) {
                        $chronicles_articles[] = $article;
                    }
                    if (count($chronicles_articles) >= 6) {
                        break;
                    }
                }
            }

            // Load forum threads and filter them as well
            $all_threads = $this->api->request('/forum/threads?limit=20');
            $chronicles_threads = [];
            foreach ($all_threads as $thread) {
                $content_text = ($thread['title'] ?? '') . ' ' . ($thread['content'] ?? '');
                if (stripos($content_text, 'chronique') !== false ||
                    stripos($content_text, 'histoire') !== false ||
                    stripos($content_text, 'récit') !== false ||
                    stripos($content_text, 'témoignage') !== false ||
                    stripos($content_text, 'expérience') !== false) {
                    $chronicles_threads[] = $thread;
                }
                if (count($chronicles_threads) >= 5) {
                    break;
                }
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des données: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            $chronicles_articles = [];
            $chronicles_threads = [];
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/chronicles.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>