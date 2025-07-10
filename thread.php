<?php
require_once 'config.php';
require_once 'api.php';

// Check if thread ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: forums.php');
    exit();
}

$thread_id = (int)$_GET['id'];

try {
    // Get thread details
    $thread = $api->request('/forum/threads/' . $thread_id);
    
    // Get thread replies
    $replies = $api->request('/forum/threads/' . $thread_id . '/replies');
    
    // Update view count
    $api->request('/forum/threads/' . $thread_id . '/view', 'POST');
    
    $page_title = $thread['title'];
    $page_description = substr(strip_tags($thread['content']), 0, 160);
} catch (Exception $e) {
    $_SESSION['flash_message'] = "Erreur: " . $e->getMessage();
    $_SESSION['flash_type'] = "error";
    
    header('Location: forums.php');
    exit();
}

// Handle reply submission
$reply_error = '';
$reply_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $api->isLoggedIn()) {
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    
    if (empty($content)) {
        $reply_error = "Le contenu de la réponse ne peut pas être vide.";
    } else {
        try {
            $result = $api->request('/forum/threads/' . $thread_id . '/replies', 'POST', [
                'thread_id' => $thread_id,
                'content' => $content
            ], true);
            
            // Refresh the page to show the new reply
            header('Location: thread.php?id=' . $thread_id . '&reply=success#reply-' . $result['id']);
            exit();
        } catch (Exception $e) {
            $reply_error = "Erreur lors de l'envoi de votre réponse: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<!-- Page Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 1.5rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="forums.php" style="color: #999;">Forums</a> &raquo;
            <?php if (isset($thread['category'])): ?>
                <a href="category.php?id=<?php echo $thread['category']['id']; ?>" style="color: #999;"><?php echo htmlspecialchars($thread['category']['name']); ?></a> &raquo;
            <?php endif; ?>
            <?php echo htmlspecialchars($thread['title']); ?>
        </div>
    </div>
</div>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <!-- Thread Container -->
        <div class="thread-container">
            <div class="thread-header">
                <h1 class="thread-title"><?php echo htmlspecialchars($thread['title']); ?></h1>
                <div class="thread-meta">
                    <?php if ($thread['is_pinned']): ?>
                        <span style="color: var(--bright-blue);"><i class="fas fa-thumbtack"></i> Épinglé</span>
                    <?php endif; ?>
                    <?php if ($thread['is_locked']): ?>
                        <span style="color: var(--accent-red);"><i class="fas fa-lock"></i> Verrouillé</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Original Post -->
            <div class="post">
                <div class="post-sidebar">
                    <img src="<?php echo isset($thread['user']['avatar_url']) && !empty($thread['user']['avatar_url']) ? htmlspecialchars($thread['user']['avatar_url']) : 'assets/kopologovide.png'; ?>" alt="Avatar" class="user-avatar">
                    <div class="user-name"><?php echo htmlspecialchars($thread['username'] ?? 'Utilisateur'); ?></div>
                    <div class="user-info">
                        <?php
                        $role = isset($thread['user']['role']) ? $thread['user']['role'] : '';
                        if (!empty($role)) {
                            echo '<span style="color: ' . ($role === 'admin' ? 'var(--accent-red)' : 'var(--bright-blue)') . ';">' . htmlspecialchars($role) . '</span><br>';
                        }
                        ?>
                        Messages: <?php echo $thread['user']['post_count'] ?? 0; ?><br>
                        Inscrit: <?php echo isset($thread['user']['created_at']) ? date('M Y', strtotime($thread['user']['created_at'])) : ''; ?>
                    </div>
                </div>
                
                <div class="post-content">
                    <div class="post-text">
                        <?php echo $thread['content']; // We assume this is sanitized by the API ?>
                    </div>
                    
                    <div class="post-meta" style="display: flex; justify-content: space-between; color: #666; font-size: 0.875rem; margin-top: 1rem;">
                        <div>
                            <i class="far fa-clock"></i> <?php echo date('d/m/Y à H:i', strtotime($thread['created_at'])); ?>
                        </div>
                        
                        <div class="post-actions">
                            <?php if ($api->isLoggedIn()): ?>
                                <button type="button" class="btn btn-outline btn-sm" onclick="quotePost(<?php echo $thread_id; ?>, '<?php echo htmlspecialchars($thread['username'] ?? 'Utilisateur'); ?>')">
                                    <i class="fas fa-quote-right"></i> Citer
                                </button>
                                
                                <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] === $thread['user_id']): ?>
                                    <a href="edit-thread.php?id=<?php echo $thread_id; ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-edit"></i> Éditer
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
                                    <a href="admin/moderate-thread.php?id=<?php echo $thread_id; ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-gavel"></i> Modérer
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Replies -->
            <?php if (!empty($replies)): ?>
                <div class="thread-replies">
                    <?php foreach ($replies as $reply): ?>
                        <div class="post" id="reply-<?php echo $reply['id']; ?>">
                            <div class="post-sidebar">
                                <img src="<?php echo isset($reply['user']['avatar_url']) && !empty($reply['user']['avatar_url']) ? htmlspecialchars($reply['user']['avatar_url']) : 'assets/kopologovide.png'; ?>" alt="Avatar" class="user-avatar">
                                <div class="user-name"><?php echo htmlspecialchars($reply['username'] ?? 'Utilisateur'); ?></div>
                                <div class="user-info">
                                    <?php
                                    $role = isset($reply['user']['role']) ? $reply['user']['role'] : '';
                                    if (!empty($role)) {
                                        echo '<span style="color: ' . ($role === 'admin' ? 'var(--accent-red)' : 'var(--bright-blue)') . ';">' . htmlspecialchars($role) . '</span><br>';
                                    }
                                    ?>
                                    Messages: <?php echo $reply['user']['post_count'] ?? 0; ?><br>
                                    Inscrit: <?php echo isset($reply['user']['created_at']) ? date('M Y', strtotime($reply['user']['created_at'])) : ''; ?>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <div class="post-text">
                                    <?php echo $reply['content']; // We assume this is sanitized by the API ?>
                                </div>
                                
                                <div class="post-meta" style="display: flex; justify-content: space-between; color: #666; font-size: 0.875rem; margin-top: 1rem;">
                                    <div>
                                        <i class="far fa-clock"></i> <?php echo date('d/m/Y à H:i', strtotime($reply['created_at'])); ?>
                                        <?php if (isset($reply['updated_at']) && $reply['updated_at'] !== $reply['created_at']): ?>
                                            <em>(édité le <?php echo date('d/m/Y à H:i', strtotime($reply['updated_at'])); ?>)</em>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="post-actions">
                                        <?php if ($api->isLoggedIn()): ?>
                                            <button type="button" class="btn btn-outline btn-sm" onclick="quotePost(<?php echo $reply['id']; ?>, '<?php echo htmlspecialchars($reply['username'] ?? 'Utilisateur'); ?>')">
                                                <i class="fas fa-quote-right"></i> Citer
                                            </button>
                                            
                                            <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] === $reply['user_id']): ?>
                                                <a href="edit-reply.php?id=<?php echo $reply['id']; ?>" class="btn btn-outline btn-sm">
                                                    <i class="fas fa-edit"></i> Éditer
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
                                                <a href="admin/moderate-reply.php?id=<?php echo $reply['id']; ?>" class="btn btn-outline btn-sm">
                                                    <i class="fas fa-gavel"></i> Modérer
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Reply Form -->
        <?php if (!$thread['is_locked']): ?>
            <div class="forum-container" style="margin-top: 2rem;">
                <div class="forum-header">
                    <h2 style="color: var(--white); margin: 0; font-size: 1.5rem;">Répondre</h2>
                </div>
                
                <div style="padding: 1.5rem;">
                    <?php if ($api->isLoggedIn()): ?>
                        <?php if (!empty($reply_error)): ?>
                            <div class="notification notification-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $reply_error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['reply']) && $_GET['reply'] === 'success'): ?>
                            <div class="notification notification-success">
                                <i class="fas fa-check-circle"></i>
                                Votre réponse a été ajoutée avec succès.
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="thread.php?id=<?php echo $thread_id; ?>" id="reply-form">
                            <div class="form-group">
                                <label for="content" class="form-label">Votre réponse</label>
                                <textarea id="content" name="content" class="form-control" rows="8" required></textarea>
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
                            
                            <button type="submit" class="btn btn-primary">Envoyer la réponse</button>
                        </form>
                    <?php else: ?>
                        <div style="text-align: center; padding: 1rem 0;">
                            <p>Vous devez être connecté pour répondre à cette discussion.</p>
                            <div style="margin-top: 1rem;">
                                <a href="index.php?route=login&redirect=thread.php?id=<?php echo $thread_id; ?>" class="btn btn-primary">Connexion</a>
                                <a href="index.php?route=register" class="btn btn-outline">Inscription</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="notification notification-info" style="margin-top: 2rem;">
                <i class="fas fa-lock"></i>
                Cette discussion est verrouillée. Vous ne pouvez pas y ajouter de réponses.
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function quotePost(postId, username) {
    const textarea = document.getElementById('content');
    if (textarea) {
        const quote = `[quote="${username}"]Le contenu que vous souhaitez citer...[/quote]\n\n`;
        textarea.value += quote;
        textarea.focus();
        textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
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