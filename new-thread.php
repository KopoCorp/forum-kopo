<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

$page_title = "Nouvelle discussion";
$page_description = "Créer une nouvelle discussion dans le forum";

// Check if user is logged in
if (!$api->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'new-thread.php';
    $_SESSION['flash_message'] = "Vous devez être connecté pour créer une discussion.";
    $_SESSION['flash_type'] = "error";
    header('Location: index.php?route=login');
    exit();
}

// Get category ID from query string if present
$category_id = isset($_GET['category_id']) ? sanitize_int($_GET['category_id']) : null;

// Get categories for dropdown
try {
    $categories = $api->request('/forum/categories');
} catch (Exception $e) {
    $categories = [];
}

$error = '';
$success = false;
$thread_id = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $title = isset($_POST['title']) ? sanitize_string($_POST['title']) : '';
    $content = isset($_POST['content']) ? sanitize_string($_POST['content']) : '';
    $selected_category = isset($_POST['category_id']) ? sanitize_int($_POST['category_id']) : null;

    // Basic validation
    if (empty($title)) {
        $error = "Le titre ne peut pas être vide.";
    } elseif (empty($content)) {
        $error = "Le contenu ne peut pas être vide.";
    } elseif ($selected_category === null) {
        $error = "Veuillez sélectionner une catégorie.";
    } else {
        try {
            $user = $api->getCurrentUser();
            $thread_data = [
                'title' => $title,
                'content' => $content,
                'category_id' => $selected_category,
                'user_id' => $user['id'] ?? null
            ];
            
            $result = $api->request('/forum/threads', 'POST', $thread_data, true);
            
            if (isset($result['id'])) {
                $thread_id = $result['id'];
                $success = true;
            }
        } catch (Exception $e) {
            $error = "Erreur lors de la création de la discussion: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="forums.php" style="color: #999;">Forums</a> &raquo; 
            Nouvelle discussion
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Créer une nouvelle discussion</h1>
        <p style="color: #ccc; max-width: 700px;">Posez vos questions et partagez vos idées avec la communauté.</p>
    </div>
</div>

<main class="main-content section">
    <div class="container" style="max-width: 900px;">
        <?php if ($success): ?>
            <div class="notification notification-success">
                <i class="fas fa-check-circle"></i>
                Votre discussion a été créée avec succès!
                <div style="margin-top: 0.5rem;">
                    <a href="thread.php?id=<?php echo $thread_id; ?>" class="btn btn-primary">Voir la discussion</a>
                    <a href="new-thread.php" class="btn btn-outline">Créer une autre discussion</a>
                </div>
            </div>
        <?php else: ?>
            <div class="forum-container">
                <div class="forum-header">
                    <h2 style="color: var(--white); margin: 0; font-size: 1.5rem;">Créer une nouvelle discussion</h2>
                </div>
                
                <div style="padding: 2rem;">
                    <?php if (!empty($error)): ?>
                        <div class="notification notification-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="new-thread.php">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <div class="form-group">
                            <label for="category_id" class="form-label">Catégorie</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title" class="form-label">Titre de la discussion</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                            <div class="form-text">Choisissez un titre clair et précis pour obtenir de meilleures réponses.</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="content" class="form-label">Contenu</label>
                            <textarea id="content" name="content" class="form-control" rows="10" required></textarea>
                            <div class="form-text">
                                Expliquez votre question ou sujet en détail. Vous pouvez utiliser des balises de formatage.
                                <a href="#" onclick="toggleFormatHelp(); return false;">Voir les options de formatage</a>
                            </div>
                            <div id="format-help" style="display: none; margin-top: 1rem; padding: 1rem; background-color: #f8f8f8; border-radius: var(--border-radius);">
                                <h4 style="margin-top: 0;">Guide de formatage</h4>
                                <div class="grid grid-2" style="gap: 1rem;">
                                    <div>
                                        <p><code>[b]Texte en gras[/b]</code></p>
                                        <p><code>[i]Texte en italique[/i]</code></p>
                                        <p><code>[u]Texte souligné[/u]</code></p>
                                    </div>
                                    <div>
                                        <p><code>[url=https://exemple.com]Lien[/url]</code></p>
                                        <p><code>[img]https://exemple.com/image.jpg[/img]</code></p>
                                        <p><code>[code]Code source[/code]</code></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-formatting" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('b')"><i class="fas fa-bold"></i></button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('i')"><i class="fas fa-italic"></i></button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('u')"><i class="fas fa-underline"></i></button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('s')"><i class="fas fa-strikethrough"></i></button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('code')"><i class="fas fa-code"></i></button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('url')"><i class="fas fa-link"></i></button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('img')"><i class="fas fa-image"></i></button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('ul')"><i class="fas fa-list-ul"></i></button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="insertFormatting('ol')"><i class="fas fa-list-ol"></i></button>
                        </div>
                        
                        <div class="form-group" style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                            <a href="forums.php" class="btn btn-outline">Annuler</a>
                            <button type="submit" class="btn btn-primary">Créer la discussion</button>
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

function insertFormatting(tag) {
    const textarea = document.getElementById('content');
    if (!textarea) return;
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    let insertText = '';
    
    switch(tag) {
        case 'b':
            insertText = `[b]${selectedText}[/b]`;
            break;
        case 'i':
            insertText = `[i]${selectedText}[/i]`;
            break;
        case 'u':
            insertText = `[u]${selectedText}[/u]`;
            break;
        case 's':
            insertText = `[s]${selectedText}[/s]`;
            break;
        case 'code':
            insertText = `[code]${selectedText}[/code]`;
            break;
        case 'url':
            const url = selectedText.length > 0 ? selectedText : 'https://';
            insertText = `[url=${url}]Lien[/url]`;
            break;
        case 'img':
            insertText = `[img]${selectedText}[/img]`;
            break;
        case 'ul':
            insertText = `[ul]\n[li]Élément 1[/li]\n[li]Élément 2[/li]\n[li]Élément 3[/li]\n[/ul]`;
            break;
        case 'ol':
            insertText = `[ol]\n[li]Élément 1[/li]\n[li]Élément 2[/li]\n[li]Élément 3[/li]\n[/ol]`;
            break;
    }
    
    textarea.focus();
    document.execCommand('insertText', false, insertText);
    
    // For browsers where execCommand is deprecated
    if (textarea.value.substring(start, start + insertText.length) !== insertText) {
        textarea.value = textarea.value.substring(0, start) + insertText + textarea.value.substring(end);
        textarea.selectionStart = start + insertText.length;
        textarea.selectionEnd = start + insertText.length;
    }
}
</script>

<?php include 'footer.php'; ?>