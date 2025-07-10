<?php
require_once 'config.php';
require_once 'api.php';

// Check if user is logged in
if (!$api->isLoggedIn()) {
    $_SESSION['flash_message'] = "Vous devez être connecté pour supprimer du contenu.";
    $_SESSION['flash_type'] = "error";
    header('Location: index.php?route=login');
    exit();
}

$user = $api->getCurrentUser();
$user_id = $user['id'];

// Check if required parameters are provided
if (!isset($_GET['type']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = "Paramètres de suppression invalides.";
    $_SESSION['flash_type'] = "error";
    header('Location: my-content.php');
    exit();
}

$type = $_GET['type'];
$content_id = (int)$_GET['id'];

// Get confirmation
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

// If not confirmed, show confirmation page
if (!$confirmed) {
    $page_title = "Confirmer la suppression";
    $page_description = "Confirmer la suppression du contenu";
    include 'header.php';
?>
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="my-content.php" style="color: #999;">Mes publications</a> &raquo; 
            Supprimer le contenu
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Confirmer la suppression</h1>
    </div>
</div>

<main class="main-content section">
    <div class="container" style="max-width: 600px;">
        <div class="forum-container">
            <div class="forum-header" style="background-color: var(--accent-red);">
                <h2 style="color: var(--white); margin: 0; font-size: 1.5rem;">Attention</h2>
            </div>
            
            <div style="padding: 2rem; text-align: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--accent-red); margin-bottom: 1rem;"></i>
                
                <h3>Êtes-vous sûr de vouloir supprimer ce contenu ?</h3>
                
                <p>Cette action est irréversible. Une fois supprimé, ce contenu ne pourra pas être récupéré.</p>
                
                <p style="margin-top: 1rem;">Type de contenu : <strong><?php echo htmlspecialchars(ucfirst($type)); ?></strong></p>
                
                <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 2rem;">
                    <a href="my-content.php" class="btn btn-outline">Annuler</a>
                    <a href="delete-content.php?type=<?php echo urlencode($type); ?>&id=<?php echo $content_id; ?>&confirm=yes" class="btn btn-accent">Supprimer définitivement</a>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
    include 'footer.php';
    exit();
}

// If confirmed, proceed with deletion
try {
    switch ($type) {
        case 'article':
            $result = $api->request('/articles/' . $content_id, 'DELETE', [], true);
            $_SESSION['flash_message'] = "L'article a été supprimé avec succès.";
            break;
            
        case 'thread':
            $result = $api->request('/forum/threads/' . $content_id, 'DELETE', [], true);
            $_SESSION['flash_message'] = "La discussion a été supprimée avec succès.";
            break;
            
        case 'reply':
            $result = $api->request('/forum/replies/' . $content_id, 'DELETE', [], true);
            $_SESSION['flash_message'] = "La réponse a été supprimée avec succès.";
            break;
            
        case 'comment':
            $result = $api->request('/comments/' . $content_id, 'DELETE', [], true);
            $_SESSION['flash_message'] = "Le commentaire a été supprimé avec succès.";
            break;
            
        case 'draft':
            $result = $api->request('/drafts/' . $content_id, 'DELETE', [], true);
            $_SESSION['flash_message'] = "Le brouillon a été supprimé avec succès.";
            break;
            
        default:
            $_SESSION['flash_message'] = "Type de contenu non pris en charge.";
            $_SESSION['flash_type'] = "error";
            header('Location: my-content.php');
            exit();
    }
    
    $_SESSION['flash_type'] = "success";
} catch (Exception $e) {
    $_SESSION['flash_message'] = "Erreur lors de la suppression : " . $e->getMessage();
    $_SESSION['flash_type'] = "error";
}

// Redirect back to content management
header('Location: my-content.php');
exit();
?>