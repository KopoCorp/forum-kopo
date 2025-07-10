<?php
class ArticleController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }

    /**
     * Retrieve a single article and ensure tags contain id and name
     *
     * @param mixed $identifier numeric id or slug
     * @param bool $use_slug whether identifier is a slug
     * @return array article data
     */
    private function fetchArticleWithTags($identifier, $use_slug) {
        $endpoint = $use_slug
            ? '/articles/slug/' . urlencode($identifier)
            : '/articles/' . (int)$identifier;

        $article = $this->api->request($endpoint);

        // Normalize tags. Some API responses return only tag IDs.
        if (isset($article['tags']) && !empty($article['tags'])) {
            $first = $article['tags'][0];
            if (!is_array($first) || !isset($first['name'])) {
                try {
                    $allTags = $this->api->request('/tags');
                    $map = [];
                    foreach ($allTags as $t) {
                        $map[$t['id']] = $t['name'];
                    }
                    $article['tags'] = array_map(function ($id) use ($map) {
                        return [
                            'id' => $id,
                            'name' => $map[$id] ?? $id
                        ];
                    }, $article['tags']);
                } catch (Exception $e) {
                    $article['tags'] = [];
                }
            }
        }

        return $article;
    }
    public function show($id) {
        if (!isset($id)) {
            header('Location: articles.php');
            exit();
        }

        $use_slug = !is_numeric($id);
        $endpoint_base = $use_slug
            ? '/articles/slug/' . urlencode($id)
            : '/articles/' . (int)$id;
        try {
            $article = $this->fetchArticleWithTags($id, $use_slug);
            $comments = $this->api->request($endpoint_base . '/comments');
            // Use numeric ID from returned data for view tracking and comment posting
            $article_id = $article['id'];
            $page_title = $article['title'];
            $page_description = substr(strip_tags($article['content']), 0, 160);
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            header('Location: articles.php');
            exit();
        }

        // Fetch latest articles for the sidebar (ignore errors silently)
        try {
            $recent_articles = $this->api->request('/articles?skip=0&limit=5');
        } catch (Exception $e) {
            $recent_articles = [];
        }

        // L'API actuelle ne dispose pas d'un endpoint dédié au suivi des vues.
        // On supprime donc l'appel pour éviter des requêtes 404 inutiles qui
        // ralentissent l'affichage de la page.

        $comment_error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->api->isLoggedIn()) {
            $content = isset($_POST['content']) ? sanitize_string($_POST['content']) : '';
            $parent_id = isset($_POST['parent_id']) ? sanitize_int($_POST['parent_id']) : null;
            if (empty($content)) {
                $comment_error = "Le contenu du commentaire ne peut pas être vide.";
            } else {
                try {
                    $data = [
                        'post_id' => $article_id,
                        'content' => $content
                    ];
                    if ($parent_id) {
                        $data['parent_id'] = $parent_id;
                    }
                    $result = $this->api->request('/articles/' . $article_id . '/comments', 'POST', $data, true);
                    header('Location: article.php?id=' . $article_id . '&comment=success#comment-' . $result['id']);
                    exit();
                } catch (Exception $e) {
                    $comment_error = "Erreur lors de l'envoi de votre commentaire: " . $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/article.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
