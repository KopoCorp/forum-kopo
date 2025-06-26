<?php
$page_title = "Articles";
$page_description = "Articles sur l'informatique, la cybersécurité et les technologies";
require_once 'header.php';

// Get query parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';
$skip = ($page - 1) * 9; // 9 articles per page

try {
    // Get articles
    $query = '/articles?skip=' . $skip . '&limit=9';
    if (!empty($tag)) {
        $query .= '&tag=' . urlencode($tag);
    }
    $articles_data = $api->request($query);
    
    // Get total count for pagination
    $total_count = $api->request('/articles/count' . (!empty($tag) ? '?tag=' . urlencode($tag) : ''));
    $total_pages = ceil(($total_count['count'] ?? 1) / 9);
    
    // Get tags for filter
    $tags = $api->request('/tags');
} catch (Exception $e) {
    $_SESSION['flash_message'] = "Erreur lors du chargement des articles: " . $e->getMessage();
    $_SESSION['flash_type'] = "error";
    $articles_data = [];
    $total_pages = 1;
    $tags = [];
}
?>

<!-- Page Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; Articles
            <?php if (!empty($tag)): ?>
                &raquo; Tag: <?php echo htmlspecialchars($tag); ?>
            <?php endif; ?>
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Articles</h1>
        <p style="color: #ccc; max-width: 700px;">Analyses, tutoriels et actualités sur l'informatique, la cybersécurité et les technologies.</p>
    </div>
</div>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <div class="grid grid-sidebar">
            <!-- Articles List -->
            <div class="articles-list">
                <?php if (!empty($tag)): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <h2>Articles avec le tag: <?php echo htmlspecialchars($tag); ?></h2>
                        <a href="articles.php" class="btn btn-outline btn-sm">Voir tous les articles</a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($articles_data)): ?>
                    <div class="grid grid-3">
                        <?php foreach ($articles_data as $article): ?>
                            <article class="article-card">
                                <div class="article-card-image">
                                    <?php if (isset($article['image_url']) && !empty($article['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                    <?php endif; ?>
                                    <?php 
                                    $label_text = '';
                                    $label_color = '';
                                    
                                    if (isset($article['tags']) && !empty($article['tags'])) {
                                        $label_text = $article['tags'][0]['name'];
                                        
                                        switch(strtolower($label_text)) {
                                            case 'cybersécurité':
                                            case 'security':
                                                $label_color = 'var(--accent-red)';
                                                break;
                                            case 'développement':
                                            case 'dev':
                                                $label_color = 'var(--purple)';
                                                break;
                                            case 'tech':
                                            case 'technologie':
                                                $label_color = 'var(--bright-blue)';
                                                break;
                                            default:
                                                $label_color = 'var(--dark-blue)';
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($label_text)): ?>
                                        <div class="article-card-label" style="background-color: <?php echo $label_color; ?>;">
                                            <?php echo strtoupper(htmlspecialchars($label_text)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="article-card-content">
                                    <h3 class="article-card-title">
                                        <a href="article.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a>
                                    </h3>
                                    <div class="article-card-meta">
                                        <span>Par <?php echo htmlspecialchars($article['username'] ?? 'Auteur'); ?></span> • 
                                        <span><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
                                    </div>
                                    <p class="article-card-excerpt">
                                        <?php 
                                        $excerpt = strip_tags($article['content']);
                                        echo substr($excerpt, 0, 120) . (strlen($excerpt) > 120 ? '...' : '');
                                        ?>
                                    </p>
                                    <div class="article-card-footer">
                                        <a href="article.php?id=<?php echo $article['id']; ?>" class="read-more">Lire la suite <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination" style="margin-top: 2rem;">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($tag) ? '&tag=' . urlencode($tag) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo !empty($tag) ? '&tag=' . urlencode($tag) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($tag) ? '&tag=' . urlencode($tag) : ''; ?>">
                                    Suivant <i class="fas fa-chevron-right"></i>
                