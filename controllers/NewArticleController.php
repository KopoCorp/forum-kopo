<?php
class NewArticleController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function create() {
        $page_title = "Publier un article";
        $page_description = "Créer et publier un nouvel article";
        if (!$this->api->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = 'new-article.php';
            $_SESSION['flash_message'] = "Vous devez être connecté pour publier un article.";
            $_SESSION['flash_type'] = "error";
            header('Location: index.php?route=login');
            exit();
        }
        try {
            $tags = $this->api->request('/tags');
        } catch (Exception $e) {
            $tags = [];
        }
        $error = '';
        $success = false;
        $was_published = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $title = isset($_POST['title']) ? sanitize_string($_POST['title']) : '';
            $raw_content = isset($_POST['content']) ? trim($_POST['content']) : '';
            $content = markdown_to_html($raw_content);
            $selected_tags = isset($_POST['tags']) ? array_map('sanitize_int', (array)$_POST['tags']) : [];
            $new_tag = isset($_POST['new_tag']) ? sanitize_string($_POST['new_tag']) : '';
            $image_url = isset($_POST['image_url']) ? filter_var($_POST['image_url'], FILTER_SANITIZE_URL) : '';
            $is_published = isset($_POST['is_published']);
            $was_published = $is_published;
            if (empty($title)) {
                $error = "Le titre ne peut pas être vide.";
            } elseif (empty($content)) {
                $error = "Le contenu ne peut pas être vide.";
            } elseif (empty($selected_tags) && empty($new_tag)) {
                $error = "Veuillez sélectionner au moins un tag.";
            } else {
                try {
                    $user = $this->api->getCurrentUser();
                    $data = [
                        'title'   => $title,
                        'content' => $content,
                        'is_pub'  => $is_published,
                        'user_id' => $user['id'] ?? null
                    ];
                    if (!empty($selected_tags)) {
                        $data['tag_ids'] = $selected_tags;
                    }
                    if (!empty($new_tag)) {
                        $data['tag_names'] = [$new_tag];
                    }

                    // If the article should remain unpublished, create a draft
                    $endpoint = $is_published ? '/articles' : '/drafts';
                    if (!$is_published) {
                        $data['type'] = 'article';
                    }
                    $result = $this->api->request($endpoint, 'POST', $data, true);
                    if (isset($result['id'])) {
                        $article_id = $result['id'];
                        if (!empty($image_url)) {
                            try {
                                $this->api->request('/articles/' . $article_id, 'PATCH', ['image_url' => $image_url], true);
                            } catch (Exception $e) {
                                // ignore
                            }
                        }
                        $success = true;
                    }
                } catch (Exception $e) {
                    $error = "Erreur lors de la création de l'article: " . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/new_article.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
