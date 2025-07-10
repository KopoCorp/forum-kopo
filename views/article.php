<!-- Article Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="articles.php" style="color: #999;">Articles</a> &raquo;
            <?php echo htmlspecialchars($article['title']); ?>
        </div>
    </div>
</div>

<!-- Main Content -->
<main class="main-content section">
    <div class="container" style="max-width: 900px;">
        <article class="article">
            <?php if (isset($article['image_url']) && !empty($article['image_url'])): ?>
                <div style="height: 400px; overflow: hidden;">
                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            <?php endif; ?>
            
            <div class="article-header">
                <?php if (isset($article['tags']) && !empty($article['tags'])): ?>
                    <div style="margin-bottom: 1rem;">
                        <?php foreach ($article['tags'] as $tag): ?>
                            <a href="articles.php?tag=<?php echo urlencode($tag['name']); ?>" class="article-category" style="margin-right: 0.5rem;">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                
                <div class="article-meta">
                    <div class="article-author">
                        <img src="<?php echo isset($article['author']['avatar_url']) && !empty($article['author']['avatar_url']) ? htmlspecialchars($article['author']['avatar_url']) : 'assets/kopologovide.png'; ?>" alt="Avatar" class="article-author-avatar">
                        <div>
                            <a href="profile.php?id=<?php echo $article['user_id']; ?>"><?php echo htmlspecialchars($article['username'] ?? 'Auteur'); ?></a>
                        </div>
                    </div>
                    <div><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></div>
                    <div><i class="far fa-clock"></i> <?php echo isset($article['read_time']) ? $article['read_time'] . ' min de lecture' : 'Lecture'; ?></div>
                    <div><i class="far fa-eye"></i> <?php echo $article['view_count'] ?? 0; ?> vues</div>
                </div>
            </div>
            
            <div class="article-content">
                <?php echo $article['content']; // We assume this is sanitized by the API ?>
            </div>
            
            <div class="article-footer">
                <div class="article-tags">
                    <?php if (isset($article['tags']) && !empty($article['tags'])): ?>
                        <?php foreach ($article['tags'] as $tag): ?>
                            <a href="articles.php?tag=<?php echo urlencode($tag['name']); ?>" class="article-tag">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="article-actions">
                    <?php if ($api->isLoggedIn()): ?>
                        <button type="button" class="btn btn-outline btn-sm" onclick="likeArticle(<?php echo $article_id; ?>)">
                            <i class="far fa-heart"></i> J'aime
                        </button>
                        
                        <button type="button" class="btn btn-outline btn-sm" onclick="shareArticle()">
                            <i class="fas fa-share-alt"></i> Partager
                        </button>
                        
                        <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] === $article['user_id']): ?>
                            <a href="edit-article.php?id=<?php echo $article_id; ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-edit"></i> Éditer
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>

