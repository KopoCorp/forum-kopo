<?php
require_once 'config.php';
require_once 'api.php';

// Vérifier si connecté
if (!$api->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $api->getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            // Préparer les données
            $data = [
                'titre' => $title,
                'content' => $content,
                'status' => 'en ligne'
            ];

            // Envoi à l'API
            $response = $api->request('/articles', 'POST', $data, true);

            $success = "Article créé avec succès.";

            // Rediriger vers la page de l'article nouvellement créé
            if (isset($response['id'])) {
                header('Location: article.php?id=' . $response['id']);
                exit;
            } else {
                header('Location: articles.php');
                exit;
            }

        } catch (Exception $e) {
            $error = "Erreur lors de la création de l'article : " . $e->getMessage();
        }
    }
}

$page_title = "Créer un article";
$page_description = "Publiez un nouvel article sur KOPO Forum";

include 'header.php';
?>

<div class="container" style="max-width: 700px; margin: 2rem auto;">
    <div class="forum-container">
        <div class="forum-header">
            <h1 style="color: var(--white); margin: 0; text-align: center;">Créer un Article</h1>
        </div>
        <div style="padding: 2rem;">
            
            <?php if (!empty($error)): ?>
                <div class="notification notification-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="notification notification-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="create-article.php">
                <div class="form-group">
                    <label for="title" class="form-label">Titre</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="content" class="form-label">Contenu</label>
                    <textarea id="content" name="content" class="form-control" rows="8" required></textarea>
                </div>
                
                <div class="form-group" style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Publier l'article
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
