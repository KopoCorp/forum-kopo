<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: forums.php');
    exit();
}

$category_id = sanitize_int($_GET['id']);
$page = isset($_GET['page']) ? sanitize_int($_GET['page']) : 1;
$limit = 10;
$skip = ($page - 1) * $limit;

try {
    $categories = $api->request('/forum/categories');
    $category = null;
    foreach ($categories as $cat) {
        if ((int)$cat['id'] === $category_id) {
            $category = $cat;
            break;
        }
    }
    if (!$category) {
        throw new Exception('Catégorie introuvable');
    }

    $threads = $api->request('/forum/threads?category_id=' . $category_id . '&skip=' . $skip . '&limit=' . $limit);
    $total_pages = isset($category['thread_count']) ? max(1, ceil($category['thread_count'] / $limit)) : 1;

    $page_title = $category['name'];
    $page_description = isset($category['description']) ? $category['description'] : 'Discussions dans la catégorie ' . $category['name'];
} catch (Exception $e) {
    $_SESSION['flash_message'] = "Erreur: " . $e->getMessage();
    $_SESSION['flash_type'] = "error";
    header('Location: forums.php');
    exit();
}

include 'header.php';
?>

<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="forums.php" style="color: #999;">Forums</a> &raquo;
            <?php echo htmlspecialchars($category['name']); ?>
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">
            <?php echo htmlspecialchars($category['name']); ?>
        </h1>
        <?php if (!empty($category['description'])): ?>
            <p style="color: #ccc; max-width: 700px;">
                <?php echo htmlspecialchars($category['description']); ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<main class="main-content section">
    <div class="container">
        <div class="forum-container">
            <?php if (!empty($threads)): ?>
                <ul class="forum-topics">
                    <?php foreach ($threads as $thread): ?>
                        <li class="topic-item">
                            <div class="topic-icon">
                                <i class="<?php echo $thread['is_pinned'] ? 'fas fa-thumbtack' : ($thread['reply_count'] > 10 ? 'fas fa-fire' : 'fas fa-comment'); ?>"></i>
                            </div>
                            <div class="topic-content">
                                <a href="thread.php?id=<?php echo $thread['id']; ?>" class="topic-title"><?php echo htmlspecialchars($thread['title']); ?></a>
                                <div class="topic-stats">
                                    <span><i class="fas fa-comment"></i> <?php echo $thread['reply_count'] ?? 0; ?> réponses</span>
                                    <span><i class="fas fa-eye"></i> <?php echo $thread['view_count'] ?? 0; ?> vues</span>
                                </div>
                            </div>
                            <div class="topic-last-post">
                                <?php if (isset($thread['last_reply_user'])): ?>
                                    <div>par <a href="profile.php?id=<?php echo $thread['last_reply_user']['id']; ?>"><?php echo htmlspecialchars($thread['last_reply_user']['username']); ?></a></div>
                                    <div><?php echo date('d/m à H:i', strtotime($thread['last_reply_at'])); ?></div>
                                <?php else: ?>
                                    <div>par <a href="profile.php?id=<?php echo $thread['user_id']; ?>"><?php echo htmlspecialchars($thread['username'] ?? 'Utilisateur'); ?></a></div>
                                    <div><?php echo date('d/m à H:i', strtotime($thread['created_at'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem;">
                    <p>Aucune discussion dans cette catégorie pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination" style="margin-top: 2rem;">
                <?php if ($page > 1): ?>
                    <a href="?id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>">
                        <i class="fas fa-chevron-left"></i> Précédent
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>">
                        Suivant <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$api->isLoggedIn()): ?>
            <div class="cta-section" style="margin-top: 3rem; padding: 2.5rem; background-color: var(--purple); color: var(--white); border-radius: var(--border-radius); text-align: center;">
                <h2 style="color: var(--white); font-size: 1.75rem; margin-bottom: 1rem;">Rejoignez la discussion</h2>
                <p style="max-width: 700px; margin: 0 auto 1.5rem;">Créez un compte pour participer à cette catégorie et rejoindre la communauté.</p>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="index.php?route=register" class="btn btn-primary" style="min-width: 150px;">Créer un compte</a>
                    <a href="index.php?route=login" class="btn btn-outline" style="background-color: transparent; border-color: var(--white); color: var(--white); min-width: 150px;">Connexion</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
