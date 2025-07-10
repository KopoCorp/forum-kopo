
<!-- Page Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; Articles
            <?php if (!empty($tag_name)): ?>
                &raquo; Tag: <?php echo htmlspecialchars($tag_name); ?>
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
                <?php if (!empty($tag_name)): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <h2>Articles avec le tag: <?php echo htmlspecialchars($tag_name); ?></h2>
            <a href="index.php?route=articles" class="btn btn-outline btn-sm">Voir tous les articles</a>
                    </div>

                <?php endif; ?>

                <div class="article-controls" style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
                    <?php if ($api->isLoggedIn()): ?>
                        <a href="new-article.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel article</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Connectez-vous pour publier</a>
                    <?php endif; ?>
                </div>

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
                                        <a href="index.php?route=article&id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a>
                                    </h3>
                                    <div class="article-card-meta">
                                        <span>Par <?php echo htmlspecialchars(get_username($article) ?? 'Auteur'); ?></span> •
                                        <span><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
                                    </div>
                                    <p class="article-card-excerpt">
                                        <?php 
                                        $excerpt = strip_tags($article['content']);
                                        echo substr($excerpt, 0, 120) . (strlen($excerpt) > 120 ? '...' : '');
                                        ?>
                                    </p>
                                    <div class="article-card-footer">
                                        <a href="index.php?route=article&id=<?php echo $article['id']; ?>" class="read-more">Lire la suite <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination" style="margin-top: 2rem;">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $tag_id > 0 ? '&tag=' . $tag_id : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </a>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $tag_id > 0 ? '&tag=' . $tag_id : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $tag_id > 0 ? '&tag=' . $tag_id : ''; ?>">
                                    Suivant <i class="fas fa-chevron-right"></i>
                
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="text-align: center; padding: 2rem 0;">Aucun article disponible pour le moment.</p>
                <?php endif; ?>
            </div>
            <aside class="sidebar">
                <div class="widget">
                    <div class="widget-header">
                        <h3>Articles récents</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <?php if (!empty($recent_articles)): ?>
                                <?php foreach ($recent_articles as $ra): ?>
                                    <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                        <a href="index.php?route=article&id=<?php echo $ra['id']; ?>" style="font-weight: 500;">
                                            <?php echo htmlspecialchars($ra['title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li style="padding: 1rem 0; text-align: center;">Aucun article</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <?php if (!empty($tags)): ?>
                <div class="widget">
                    <div class="widget-header">
                        <h3>Tags populaires</h3>
                    </div>
                    <div class="widget-content">
                        <div class="article-tags">
                            <?php foreach ($tags as $t): ?>
                                <a href="index.php?route=articles&amp;tag=<?php echo $t['id']; ?>" class="article-tag">
                                    <?php echo htmlspecialchars($t['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</main>

