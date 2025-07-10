<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

$page_title = "Mes publications";
$page_description = "Gérer vos articles et discussions";

// Check if user is logged in
if (!$api->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'my-content.php';
    $_SESSION['flash_message'] = "Vous devez être connecté pour accéder à cette page.";
    $_SESSION['flash_type'] = "error";
    header('Location: index.php?route=login');
    exit();
}

$user = $api->getCurrentUser();
$user_id = $user['id'];

// Get the current tab (default to articles)
$active_tab = isset($_GET['tab']) ? sanitize_string($_GET['tab']) : 'articles';

try {
    // Get user's articles
    $my_articles = $api->request('/users/' . $user_id . '/articles', 'GET', [], true);
    
    // Get user's threads
    $my_threads = $api->request('/users/' . $user_id . '/threads', 'GET', [], true);
    
    // Get user's drafts if on drafts tab
    $my_drafts = [];
    if ($active_tab === 'drafts') {
        $my_drafts = $api->request('/users/' . $user_id . '/drafts', 'GET', [], true);
    }
} catch (Exception $e) {
    $_SESSION['flash_message'] = "Erreur lors du chargement de vos publications: " . $e->getMessage();
    $_SESSION['flash_type'] = "error";
}

include 'header.php';
?>

<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="profile.php?id=<?php echo $user_id; ?>" style="color: #999;">Profil</a> &raquo; 
            Mes publications
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Mes publications</h1>
        <p style="color: #ccc; max-width: 700px;">Gérez et suivez tous vos articles et discussions.</p>
    </div>
</div>

<main class="main-content section">
    <div class="container">
        <div class="content-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div class="tabs" style="display: flex; gap: 1rem;">
                <a href="?tab=articles" class="<?php echo $active_tab === 'articles' ? 'btn btn-primary' : 'btn btn-outline'; ?>">
                    <i class="fas fa-newspaper"></i> Mes articles
                </a>
                <a href="?tab=threads" class="<?php echo $active_tab === 'threads' ? 'btn btn-primary' : 'btn btn-outline'; ?>">
                    <i class="fas fa-comments"></i> Mes discussions
                </a>
                <a href="?tab=drafts" class="<?php echo $active_tab === 'drafts' ? 'btn btn-primary' : 'btn btn-outline'; ?>">
                    <i class="fas fa-save"></i> Mes brouillons
                </a>
            </div>
            <div class="actions" style="display: flex; gap: 1rem;">
                <a href="new-article.php" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Nouvel article
                </a>
                <a href="new-thread.php" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Nouvelle discussion
                </a>
            </div>
        </div>
        
        <?php if ($active_tab === 'articles'): ?>
            <!-- Articles Tab -->
            <div class="forum-container">
                <div class="forum-header">
                    <h2 style="color: var(--white); margin: 0;">Mes articles</h2>
                </div>
                
                <?php if (empty($my_articles)): ?>
                    <div style="padding: 2rem; text-align: center;">
                        <p>Vous n'avez pas encore publié d'articles.</p>
                        <a href="new-article.php" class="btn btn-primary" style="margin-top: 1rem;">Publier votre premier article</a>
                    </div>
                <?php else: ?>
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 1rem;">Titre</th>
                                <th style="text-align: center; padding: 1rem;">Date de publication</th>
                                <th style="text-align: center; padding: 1rem;">Commentaires</th>
                                <th style="text-align: center; padding: 1rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_articles as $article): ?>
                                <tr>
                                    <td style="padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <a href="article.php?id=<?php echo $article['id']; ?>" style="font-weight: 500;">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <?php echo $article['comment_count'] ?? 0; ?>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <div style="display: flex; justify-content: center; gap: 0.5rem;">
                                            <a href="edit-article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline btn-sm" title="Éditer">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmDelete('article', <?php echo $article['id']; ?>)" class="btn btn-outline btn-sm" title="Supprimer" style="color: var(--accent-red); border-color: var(--accent-red);">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php elseif ($active_tab === 'threads'): ?>
            <!-- Threads Tab -->
            <div class="forum-container">
                <div class="forum-header" style="background-color: var(--purple);">
                    <h2 style="color: var(--white); margin: 0;">Mes discussions</h2>
                </div>
                
                <?php if (empty($my_threads)): ?>
                    <div style="padding: 2rem; text-align: center;">
                        <p>Vous n'avez pas encore créé de discussions.</p>
                        <a href="new-thread.php" class="btn btn-primary" style="margin-top: 1rem;">Créer votre première discussion</a>
                    </div>
                <?php else: ?>
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 1rem;">Titre</th>
                                <th style="text-align: center; padding: 1rem;">Catégorie</th>
                                <th style="text-align: center; padding: 1rem;">Date</th>
                                <th style="text-align: center; padding: 1rem;">Réponses</th>
                                <th style="text-align: center; padding: 1rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_threads as $thread): ?>
                                <tr>
                                    <td style="padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <a href="thread.php?id=<?php echo $thread['id']; ?>" style="font-weight: 500;">
                                            <?php echo htmlspecialchars($thread['title']); ?>
                                        </a>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <?php 
                                        if (isset($thread['category']) && isset($thread['category']['name'])) {
                                            echo htmlspecialchars($thread['category']['name']);
                                        } else {
                                            echo 'Non catégorisé';
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <?php echo date('d/m/Y', strtotime($thread['created_at'])); ?>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <?php echo $thread['reply_count'] ?? 0; ?>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <div style="display: flex; justify-content: center; gap: 0.5rem;">
                                            <a href="edit-thread.php?id=<?php echo $thread['id']; ?>" class="btn btn-outline btn-sm" title="Éditer">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmDelete('thread', <?php echo $thread['id']; ?>)" class="btn btn-outline btn-sm" title="Supprimer" style="color: var(--accent-red); border-color: var(--accent-red);">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php elseif ($active_tab === 'drafts'): ?>
            <!-- Drafts Tab -->
            <div class="forum-container">
                <div class="forum-header" style="background-color: var(--bright-blue);">
                    <h2 style="color: var(--white); margin: 0;">Mes brouillons</h2>
                </div>
                
                <?php if (empty($my_drafts)): ?>
                    <div style="padding: 2rem; text-align: center;">
                        <p>Vous n'avez pas de brouillons enregistrés.</p>
                        <a href="new-article.php" class="btn btn-primary" style="margin-top: 1rem;">Créer un nouvel article</a>
                    </div>
                <?php else: ?>
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 1rem;">Titre</th>
                                <th style="text-align: center; padding: 1rem;">Type</th>
                                <th style="text-align: center; padding: 1rem;">Dernière modification</th>
                                <th style="text-align: center; padding: 1rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_drafts as $draft): ?>
                                <tr>
                                    <td style="padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <?php echo htmlspecialchars($draft['title']); ?>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <?php echo $draft['type'] === 'article' ? 'Article' : 'Discussion'; ?>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <?php echo date('d/m/Y à H:i', strtotime($draft['updated_at'])); ?>
                                    </td>
                                    <td style="text-align: center; padding: 1rem; border-top: 1px solid var(--light-gray);">
                                        <div style="display: flex; justify-content: center; gap: 0.5rem;">
                                            <a href="edit-<?php echo $draft['type']; ?>.php?id=<?php echo $draft['id']; ?>" class="btn btn-outline btn-sm" title="Continuer à éditer">
                                                <i class="fas fa-edit"></i> Éditer
                                            </a>
                                            <button onclick="confirmDelete('draft', <?php echo $draft['id']; ?>)" class="btn btn-outline btn-sm" title="Supprimer" style="color: var(--accent-red); border-color: var(--accent-red);">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<div id="delete-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background-color: var(--white); border-radius: var(--border-radius); max-width: 500px; width: 100%; padding: 2rem; text-align: center;">
        <h3 style="margin-top: 0;">Confirmer la suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.</p>
        <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem;">
            <button onclick="closeDeleteModal()" class="btn btn-outline">Annuler</button>
            <button onclick="executeDelete()" class="btn btn-accent">Supprimer</button>
        </div>
    </div>
</div>

<script>
let deleteType = '';
let deleteId = null;

function confirmDelete(type, id) {
    deleteType = type;
    deleteId = id;
    document.getElementById('delete-modal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('delete-modal').style.display = 'none';
}

function executeDelete() {
    if (!deleteType || !deleteId) return;
    
    // Redirect to delete script with parameters
    window.location.href = `delete-content.php?type=${deleteType}&id=${deleteId}`;
}

// Close modal if user clicks outside of it
window.onclick = function(event) {
    const modal = document.getElementById('delete-modal');
    if (event.target === modal) {
        closeDeleteModal();
    }
}
</script>

<?php include 'footer.php'; ?>