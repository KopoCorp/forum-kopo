<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

$page_title = "Publier un article";
$page_description = "Créer et publier un nouvel article";

// Check if user is logged in
if (!$api->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'new-article.php';
    $_SESSION['flash_message'] = "Vous devez être connecté pour publier un article.";
    $_SESSION['flash_type'] = "error";
    header('Location: index.php?route=login');
    exit();
}

// Get available tags for the dropdown
try {
    $tags = $api->request('/tags');
} catch (Exception $e) {
    $tags = [];
}

$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $title = isset($_POST['title']) ? sanitize_string($_POST['title']) : '';
    $raw_content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $content = markdown_to_html($raw_content);
    $selected_tags = isset($_POST['tags']) ? array_map('sanitize_int', (array)$_POST['tags']) : [];
    $new_tag = isset($_POST['new_tag']) ? sanitize_string($_POST['new_tag']) : '';
    $image_url = isset($_POST['image_url']) ? filter_var($_POST['image_url'], FILTER_SANITIZE_URL) : '';
    $is_published = isset($_POST['is_published']) ? true : false;

    // Basic validation
    if (empty($title)) {
        $error = "Le titre ne peut pas être vide.";
    } elseif (empty($content)) {
        $error = "Le contenu ne peut pas être vide.";
    } elseif (empty($selected_tags) && empty($new_tag)) {
        $error = "Veuillez sélectionner au moins un tag.";
    } else {
        try {
            // Build article data
            $user = $api->getCurrentUser();
            $article_data = [
                'title'   => $title,
                'content' => $content,
                'is_pub'  => $is_published,
                'user_id' => $user['id'] ?? null
            ];

            // Associate tags with the article
            if (!empty($selected_tags)) {
                $article_data['tag_ids'] = $selected_tags;
            }
            if (!empty($new_tag)) {
                $article_data['tag_names'] = [$new_tag];
            }

            $result = $api->request('/articles', 'POST', $article_data, true);

            if (isset($result['id'])) {
                $article_id = $result['id'];

                // Set image via URL if provided
                if (!empty($image_url)) {
                    try {
                        $api->request('/articles/' . $article_id, 'PATCH', [
                            'image_url' => $image_url
                        ], true);
                    } catch (Exception $e) {
                        // Ignore URL update errors
                    }
                }

                $success = true;
            }
        } catch (Exception $e) {
            $error = "Erreur lors de la création de l'article: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="articles.php" style="color: #999;">Articles</a> &raquo; 
            Publier un article
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Publier un article</h1>
        <p style="color: #ccc; max-width: 700px;">Partagez vos connaissances et votre expertise avec la communauté.</p>
    </div>
</div>

<main class="main-content section">
    <div class="container" style="max-width: 900px;">
        <?php if ($success): ?>
            <div class="notification notification-success">
                <i class="fas fa-check-circle"></i>
                Votre article a été publié avec succès!
                <div style="margin-top: 0.5rem;">
                    <a href="articles.php" class="btn btn-primary">Voir tous les articles</a>
                    <a href="new-article.php" class="btn btn-outline">Publier un autre article</a>
                </div>
            </div>
        <?php else: ?>
            <div class="forum-container">
                <div class="forum-header">
                    <h2 style="color: var(--white); margin: 0; font-size: 1.5rem;">Créer un nouvel article</h2>
                </div>
                
                <div style="padding: 2rem;">
                    <?php if (!empty($error)): ?>
                        <div class="notification notification-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="new-article.php">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <div class="form-group">
                            <label for="title" class="form-label">Titre de l'article</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="content" class="form-label">Contenu</label>
                            <textarea id="content" name="content" class="form-control" rows="15" required></textarea>
                            <div class="form-text">
                                Vous pouvez utiliser le format Markdown pour la mise en forme. 
                                <a href="#" onclick="toggleFormatHelp(); return false;">Voir les options de formatage</a>
                            </div>
                            <div id="format-help" style="display: none; margin-top: 1rem; padding: 1rem; background-color: #f8f8f8; border-radius: var(--border-radius);">
                                <h4 style="margin-top: 0;">Guide de formatage</h4>
                                <div class="grid grid-2" style="gap: 1rem;">
                                    <div>
                                        <p><strong>Formatage de base:</strong></p>
                                        <pre style="margin: 0.5rem 0; background-color: var(--near-black); color: var(--white); padding: 0.5rem;">**Texte en gras**
*Texte en italique*
~~Texte barré~~
`Code en ligne`</pre>
                                    </div>
                                    <div>
                                        <p><strong>Listes:</strong></p>
                                        <pre style="margin: 0.5rem 0; background-color: var(--near-black); color: var(--white); padding: 0.5rem;"># Titre de niveau 1
## Titre de niveau 2
- Élément de liste
1. Liste numérotée</pre>
                                    </div>
                                    <div>
                                        <p><strong>Liens et images:</strong></p>
                                        <pre style="margin: 0.5rem 0; background-color: var(--near-black); color: var(--white); padding: 0.5rem;">[Texte du lien](URL)
![Texte alternatif](URL_image)</pre>
                                    </div>
                                    <div>
                                        <p><strong>Blocs de code:</strong></p>
                                        <pre style="margin: 0.5rem 0; background-color: var(--near-black); color: var(--white); padding: 0.5rem;">```language
code sur plusieurs lignes
```</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="tags-input" class="form-label">Tags</label>
                            <input id="tags-input" class="form-control" placeholder="Choisissez des tags">
                            <div id="selected-tags"></div>
                            <div class="form-text">Commencez à taper pour rechercher ou ajouter un tag.</div>
                        </div>

                        <div class="form-group">
                            <label for="new_tag" class="form-label">Ajouter un tag</label>
                            <input type="text" id="new_tag" name="new_tag" class="form-control" placeholder="Nouveau tag">
                            <div class="form-text">Si le tag n'existe pas encore, il sera créé puis associé à l'article.</div>
                        </div>
                        

                        <div class="form-group">
                            <label for="image_url" class="form-label">URL de l'image d'en-tête (optionnelle)</label>
                            <input type="url" id="image_url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="is_published" name="is_published" class="form-check-input" checked>
                                <label for="is_published">Publier immédiatement</label>
                            </div>
                            <div class="form-text">Si non coché, l'article sera enregistré comme brouillon.</div>
                        </div>
                        
                        <div class="form-group" style="display: flex; justify-content: space-between;">
                            <a href="articles.php" class="btn btn-outline">Annuler</a>
                            <button type="submit" class="btn btn-primary">Publier l'article</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleFormatHelp() {
    const helpPanel = document.getElementById('format-help');
    if (helpPanel.style.display === 'none') {
        helpPanel.style.display = 'block';
    } else {
        helpPanel.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const tagInput = document.getElementById('tags-input');
    if (tagInput) {
        const tagData = <?php echo json_encode($tags); ?>;
        const tagify = new Tagify(tagInput, {
            whitelist: tagData.map(t => ({ value: t.id, name: t.name })),
            enforceWhitelist: true,
            tagTextProp: 'name',
            dropdown: {
                enabled: 0,
                maxItems: 20,
                mapValueTo: 'name',
                searchKeys: ['name']
            }
        });

        const container = document.getElementById('selected-tags');
        function updateHidden() {
            container.innerHTML = '';
            tagify.value.forEach(tag => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'tags[]';
                hidden.value = tag.value;
                container.appendChild(hidden);
            });
        }
        tagify.on('add', updateHidden);
        tagify.on('remove', updateHidden);
    }
});
</script>

<?php include 'footer.php'; ?>