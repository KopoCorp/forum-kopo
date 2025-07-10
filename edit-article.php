<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

$page_title = "Modifier l'article";
$page_description = "Modifier un article existant";

// Check if user is logged in
if (!$api->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'my-content.php';
    $_SESSION['flash_message'] = "Vous devez être connecté pour accéder à cette page.";
    $_SESSION['flash_type'] = "error";
    header('Location: index.php?route=login');
    exit();
}

// Check if article ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my-content.php');
    exit();
}

$article_id = sanitize_int($_GET['id']);
$user = $api->getCurrentUser();

try {
    // Get article details
    $article = $api->request('/articles/' . $article_id, 'GET', [], true);
    
    // Check if user is the author or an admin
    if ($article['user_id'] !== $user['id'] && !isset($user['is_admin'])) {
        $_SESSION['flash_message'] = "Vous n'êtes pas autorisé à modifier cet article.";
        $_SESSION['flash_type'] = "error";
        header('Location: my-content.php');
        exit();
    }
    
    // Get available tags
    $tags = $api->request('/tags');
    
    // Get article tags
    $article_tags = isset($article['tags']) ? $article['tags'] : [];
    $selected_tag_ids = array_map(function($tag) {
        return $tag['id'];
    }, $article_tags);
    
} catch (Exception $e) {
    $_SESSION['flash_message'] = "Erreur: " . $e->getMessage();
    $_SESSION['flash_type'] = "error";
    header('Location: my-content.php');
    exit();
}

$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $title = isset($_POST['title']) ? sanitize_string($_POST['title']) : '';
    $content = isset($_POST['content']) ? sanitize_string($_POST['content']) : '';
    $selected_tags = isset($_POST['tags']) ? array_map('sanitize_int', (array)$_POST['tags']) : [];
    $new_tag = isset($_POST['new_tag']) ? sanitize_string($_POST['new_tag']) : '';
    $is_published = isset($_POST['is_published']) ? true : false;

    // Basic validation
    if (empty($title)) {
        $error = "Le titre ne peut pas être vide.";
    } elseif (empty($content)) {
        $error = "Le contenu ne peut pas être vide.";
    } else {
        try {
            // Create tag if user provided one
            if (!empty($new_tag)) {
                try {
                    $tagResult = $api->request('/tags', 'POST', ['name' => $new_tag], true);
                    if (isset($tagResult['id'])) {
                        $selected_tags[] = $tagResult['id'];
                        $tags[] = ['id' => $tagResult['id'], 'name' => $new_tag];
                    }
                } catch (Exception $e) {
                    $error = "Erreur lors de la création du tag: " . $e->getMessage();
                }
            }

            // Update article
            $article_data = [
                'title' => $title,
                'content' => $content,
                'is_pub' => $is_published
            ];
            
            $api->request('/articles/' . $article_id, 'PATCH', $article_data, true);
            
            // Update tags - first remove existing tags
            try {
                $api->request('/articles/' . $article_id . '/tags', 'DELETE', [], true);
            } catch (Exception $e) {
                // Continue even if tag deletion fails
            }
            
            // Add selected tags
            if (!empty($selected_tags)) {
                foreach ($selected_tags as $tag_id) {
                    try {
                        $api->request('/articles/' . $article_id . '/tags', 'POST', ['tag_id' => $tag_id], true);
                    } catch (Exception $e) {
                        // Continue even if tag association fails
                    }
                }
            }
            
            // Handle image upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Upload image and associate with article
                $image_data = file_get_contents($_FILES['image']['tmp_name']);
                $image_name = $_FILES['image']['name'];
                
                // First upload the attachment
                $attachment_data = [
                    'file' => base64_encode($image_data),
                    'filename' => $image_name,
                    'content_type' => $_FILES['image']['type']
                ];
                
                $attachment = $api->request('/attachments', 'POST', $attachment_data, true);
                
                if (isset($attachment['id'])) {
                    // Associate the image with the article
                    $api->request('/articles/' . $article_id, 'PATCH', [
                        'image_url' => '/attachments/' . $attachment['id']
                    ], true);
                }
            }
            
            $success = true;
        } catch (Exception $e) {
            $error = "Erreur lors de la mise à jour de l'article: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="my-content.php" style="color: #999;">Mes publications</a> &raquo; 
            Modifier l'article
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Modifier l'article</h1>
        <p style="color: #ccc; max-width: 700px;">Mettre à jour le contenu de votre article.</p>
    </div>
</div>

<main class="main-content section">
    <div class="container" style="max-width: 900px;">
        <?php if ($success): ?>
            <div class="notification notification-success">
                <i class="fas fa-check-circle"></i>
                Votre article a été mis à jour avec succès!
                <div style="margin-top: 0.5rem;">
                    <a href="article.php?id=<?php echo $article_id; ?>" class="btn btn-primary">Voir l'article</a>
                    <a href="my-content.php" class="btn btn-outline">Retour à mes publications</a>
                </div>
            </div>
        <?php else: ?>
            <div class="forum-container">
                <div class="forum-header">
                    <h2 style="color: var(--white); margin: 0; font-size: 1.5rem;">Modifier l'article</h2>
                </div>
                
                <div style="padding: 2rem;">
                    <?php if (!empty($error)): ?>
                        <div class="notification notification-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="edit-article.php?id=<?php echo $article_id; ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <div class="form-group">
                            <label for="title" class="form-label">Titre de l'article</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="content" class="form-label">Contenu</label>
                            <textarea id="content" name="content" class="form-control" rows="15" required><?php echo htmlspecialchars($article['content']); ?></textarea>
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
                            <label for="tags" class="form-label">Tags</label>
                            <select id="tags" name="tags[]" class="form-control" multiple>
                                <?php if (!empty($tags)): ?>
                                    <?php foreach ($tags as $tag): ?>
                                        <option value="<?php echo $tag['id']; ?>" <?php echo in_array($tag['id'], $selected_tag_ids) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tag['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">Vous pouvez sélectionner plusieurs tags en maintenant la touche Ctrl (ou Cmd sur Mac).</div>
                        </div>

                        <div class="form-group">
                            <label for="new_tag" class="form-label">Ajouter un tag</label>
                            <input type="text" id="new_tag" name="new_tag" class="form-control" placeholder="Nouveau tag">
                            <div class="form-text">Si le tag n'existe pas encore, il sera créé puis associé à l'article.</div>
                        </div>
                        
                        <?php if (isset($article['image_url']) && !empty($article['image_url'])): ?>
                            <div class="form-group">
                                <label class="form-label">Image actuelle</label>
                                <div style="margin-bottom: 1rem;">
                                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="Image d'article" style="max-width: 300px; max-height: 200px; border-radius: var(--border-radius);">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="image" class="form-label">Nouvelle image d'en-tête (optionnelle)</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif">
                            <div class="form-text">Format recommandé: 1200 x 630 pixels. Taille maximale: 5 Mo.</div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="is_published" name="is_published" class="form-check-input" <?php echo $article['is_pub'] ? 'checked' : ''; ?>>
                                <label for="is_published">Publier</label>
                            </div>
                            <div class="form-text">Si non coché, l'article sera enregistré comme brouillon.</div>
                        </div>
                        
                        <div class="form-group" style="display: flex; justify-content: space-between;">
                            <a href="my-content.php" class="btn btn-outline">Annuler</a>
                            <button type="submit" class="btn btn-primary">Mettre à jour l'article</button>
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
</script>

<?php include 'footer.php'; ?>