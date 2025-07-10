<?php
require_once 'config.php';
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $selected_tags = isset($_POST['tags']) ? $_POST['tags'] : [];
    $is_published = isset($_POST['is_published']) ? true : false;

    // Basic validation
    if (empty($title)) {
        $error = "Le titre ne peut pas être vide.";
    } elseif (empty($content)) {
        $error = "Le contenu ne peut pas être vide.";
    } else {
        try {
            // Create article first
            $article_data = [
                'title' => $title,
                'content' => $content,
                'is_pub' => $is_published
            ];
            
            $result = $api->request('/articles', 'POST', $article_data, true);
            
            // If article created successfully, add tags
            if (isset($result['id'])) {
                $article_id = $result['id'];
                
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
                    
                    <form method="post" action="new-article.php" enctype="multipart/form-data">
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
                            <label for="tags" class="form-label">Tags</label>
                            <select id="tags" name="tags[]" class="form-control" multiple>
                                <?php if (!empty($tags)): ?>
                                    <?php foreach ($tags as $tag): ?>
                                        <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">Vous pouvez sélectionner plusieurs tags en maintenant la touche Ctrl (ou Cmd sur Mac).</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="image" class="form-label">Image d'en-tête (optionnelle)</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif">
                            <div class="form-text">Format recommandé: 1200 x 630 pixels. Taille maximale: 5 Mo.</div>
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
</script>

<?php include 'footer.php'; ?>