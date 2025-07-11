<?php
class ArticleController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }

    /**
     * Fetch an article and resolve tag ids to name arrays
     *
     * @param int $id   Article id
     * @param bool $auth Whether to authenticate API request
     * @return array
     */
    private function fetchArticleWithTags($id, $auth = false) {
        $article = $this->api->request('/articles/' . $id, 'GET', [], $auth);
        if (isset($article['tags']) && is_array($article['tags'])) {
            try {
                $all_tags = $this->api->request('/tags');
            } catch (Exception $e) {
                $all_tags = [];
            }
            $tag_map = [];
            foreach ($all_tags as $t) {
                if (isset($t['id'])) {
                    $tag_map[$t['id']] = $t['name'] ?? '';
                }
            }
            $resolved = [];
            foreach ($article['tags'] as $tagId) {
                if (is_array($tagId) && isset($tagId['name'])) {
                    $resolved[] = $tagId;
                } else {
                    $resolved[] = ['id' => (int)$tagId, 'name' => $tag_map[$tagId] ?? ''];
                }
            }
            $article['tags'] = $resolved;
        }
        return $article;
    }

    public function show($id) {
        if (!$id || !is_numeric($id)) {
            http_response_code(404);
            require '404.html';
            return;
        }
        $article_id = (int)$id;

        try {
            $article = $this->fetchArticleWithTags($article_id, $this->api->isLoggedIn());

            // If the article is a draft, only the author or an admin may view it
            if (isset($article['is_pub']) && !$article['is_pub']) {
                $authorized = false;
                if ($this->api->isLoggedIn()) {
                    $current = $this->api->getCurrentUser();
                    if (($current['id'] ?? null) === ($article['user_id'] ?? null) || ($current['is_admin'] ?? false)) {
                        $authorized = true;
                    }
                }
                if (!$authorized) {
                    http_response_code(404);
                    require '404.html';
                    return;
                }
            }

            $comments = $this->api->request('/articles/' . $article_id . '/comments');
            $recent_articles = filter_published($this->api->request('/articles?skip=0&limit=5'));
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            header('Location: index.php?route=articles');
            exit();
        }

        $page_title = $article['title'] ?? 'Article';
        $page_description = substr(strip_tags($article['content'] ?? ''), 0, 160);

        $comment_error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->api->isLoggedIn() && verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $content = isset($_POST['content']) ? sanitize_string($_POST['content']) : '';
            if (empty($content)) {
                $comment_error = "Le contenu du commentaire ne peut pas être vide.";
            } else {
                try {
                    $user = $this->api->getCurrentUser();
                    $data = [
                        'post_id' => $article_id,
                        'content' => $content,
                        'user_id' => $user['id'] ?? null
                    ];
                    $this->api->request(
                        '/articles/' . $article_id . '/comments',
                        'POST',
                        $data,
                        true
                    );
                    header('Location: article.php?id=' . $article_id . '&comment=success#comments');
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
?>
