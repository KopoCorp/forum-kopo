<!-- Page Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; Forums
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Forums de discussion</h1>
        <p style="color: #ccc; max-width: 700px;">Explorez nos forums thématiques dédiés à l'informatique et à la cybersécurité. Participez aux discussions et partagez vos connaissances.</p>
    </div>
</div>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <div class="grid grid-sidebar">
            <div class="forums-container">
                <!-- Forum Controls -->
                <div class="forum-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <div class="forum-stats">
                        <span style="font-weight: 500;"><?php echo count($categories); ?> Forums</span>
                        <?php
                        $thread_count = 0;
                        $reply_count = 0;
                        foreach ($categories as $cat) {
                            $thread_count += $cat['thread_count'] ?? 0;
                            $reply_count += $cat['reply_count'] ?? 0;
                        }
                        ?>
                        &bull; <span><?php echo $thread_count; ?> Discussions</span> 
                        &bull; <span><?php echo $reply_count; ?> Messages</span>
                    </div>
                    <div class="forum-actions">
                        <?php if ($api->isLoggedIn()): ?>
                            <a href="new-thread.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle discussion</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline">Connectez-vous pour participer</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Forum Categories -->
                <?php foreach ($category_groups as $group_name => $group_categories): ?>
                    <?php if (!empty($group_categories)): ?>
                        <div class="forum-container" style="margin-bottom: 2rem;">
                            <div class="forum-header" <?php if ($group_name === 'Cybersécurité & Réseaux') echo 'style="background-color: var(--accent-red);"'; ?>>
                                <h2 style="color: var(--white); margin: 0;"><?php echo $group_name; ?></h2>
                            </div>
                            
                            <?php foreach ($group_categories as $category): ?>
                                <div class="forum-category">
                                    <div class="category-header">
                                        <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                                        <span class="category-stats">
                                            <?php echo $category['thread_count'] ?? 0; ?> discussions &bull; 
                                            <?php echo $category['reply_count'] ?? 0; ?> messages
                                        </span>
                                    </div>
                                    
                                    <?php if (isset($category['description']) && !empty($category['description'])): ?>
                                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Get recent threads for this category
                                    $threads = [];
                                    try {
                                        $threads = $api->request('/forum/threads?category_id=' . $category['id'] . '&limit=1&skip=0');
                                    } catch (Exception $e) {
                                        // Silently fail if we can't get threads
                                    }
                                    ?>
                                    
                                    <ul class="forum-topics">
                                        <?php if (!empty($threads)): ?>
                                            <?php foreach ($threads as $thread): ?>
                                                <li class="topic-item">
                                                    <div class="topic-icon">
                                                        <i class="<?php echo $thread['is_pinned'] ? 'fas fa-thumbtack' : ($thread['reply_count'] > 10 ? 'fas fa-fire' : 'fas fa-comment'); ?>"></i>
                                                    </div>
                                                    <div class="topic-content">
                                                        <a href="thread.php?id=<?php echo $thread['id']; ?>" class="topic-title"><?php echo htmlspecialchars($thread['title']); ?></a>
                                                        <div class="topic-stats">
                                                            <span><i class="fas fa-comment"></i> <?php echo $thread['reply_count'] ?? 0; ?> réponses</span>
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
                                        <?php else: ?>
                                            <li class="topic-item" style="justify-content: center; color: #666;">
                                                Aucune discussion dans cette catégorie pour le moment.
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                    
                                    <a href="category.php?id=<?php echo $category['id']; ?>" class="view-all" style="display: block; text-align: right; padding: 0.75rem 0 0; font-size: 0.875rem; font-weight: 500;">
                                        Voir toutes les discussions <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if (empty($categories)): ?>
                    <div class="forum-container">
                        <div class="forum-category" style="text-align: center; padding: 3rem 1.5rem;">
                            <p>Aucune catégorie de forum trouvée.</p>
                            <?php if ($api->isLoggedIn() && isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
                                <a href="admin/categories.php" class="btn btn-primary" style="margin-top: 1rem;">Créer des catégories</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar">
                <?php if (!$api->isLoggedIn()): ?>
                    <!-- Login Widget -->
                    <div class="widget">
                        <div class="widget-header">
                            <h3>Connectez-vous</h3>
                        </div>
                        <div class="widget-content" style="text-align: center;">
                            <p style="margin-bottom: 1.25rem;">Connectez-vous pour participer aux discussions et accéder à toutes les fonctionnalités du forum.</p>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <a href="login.php" class="btn btn-primary" style="width: 100%;">Connexion</a>
                                <a href="register.php" class="btn btn-outline" style="width: 100%;">Inscription</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- User Widget -->
                    <div class="widget">
                        <div class="widget-header">
                            <h3>Mon profil</h3>
                        </div>
                        <div class="widget-content" style="text-align: center;">
                            <?php $user = $api->getCurrentUser(); ?>
                            <div style="margin-bottom: 1rem;">
                                <img src="<?php echo isset($user['avatar_url']) && !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url']) : DEFAULT_AVATAR_URL; ?>" alt="Avatar" class="user-avatar" style="margin: 0 auto 0.5rem; width: 80px; height: 80px;">
                                <h4 style="margin: 0;"><?php echo htmlspecialchars($user['username']); ?></h4>
                                <div style="color: #666; font-size: 0.875rem;">
                                    Membre depuis <?php echo date('M Y', strtotime($user['created_at'])); ?>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                                <div style="text-align: center;">
                                    <div style="font-size: 1.5rem; font-weight: 600;"><?php echo $user['thread_count'] ?? 0; ?></div>
                                    <div style="font-size: 0.75rem; color: #666;">Discussions</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 1.5rem; font-weight: 600;"><?php echo $user['reply_count'] ?? 0; ?></div>
                                    <div style="font-size: 0.75rem; color: #666;">Réponses</div>
                                </div>
                            </div>
                            <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-outline" style="width: 100%;">Voir mon profil</a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Latest Threads -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--purple);">
                        <h3>Discussions récentes</h3>
                    </div>
                    <div class="widget-content">
                        <ul class="recent-topics" style="list-style: none;">
                            <?php
                            try {
                                $recent_threads = $api->request('/forum/threads?skip=0&limit=5');
                                if (!empty($recent_threads)):
                                    foreach ($recent_threads as $thread):
                            ?>
                                <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                    <a href="thread.php?id=<?php echo $thread['id']; ?>" style="font-weight: 500;"><?php echo htmlspecialchars($thread['title']); ?></a>
                                    <div style="font-size: 0.8125rem; color: #666; margin-top: 0.25rem;">
                                        <span>par <a href="profile.php?id=<?php echo $thread['user_id']; ?>"><?php echo htmlspecialchars($thread['username'] ?? 'Utilisateur'); ?></a></span>
                                        <span style="margin-left: 0.5rem;"><?php echo date('d/m à H:i', strtotime($thread['created_at'])); ?></span>
                                    </div>
                                </li>
                            <?php
                                    endforeach;
                                else:
                            ?>
                                <li style="text-align: center; padding: 1rem 0;">Aucune discussion récente.</li>
                            <?php
                                endif;
                            } catch (Exception $e) {
                                echo '<li style="text-align: center; padding: 1rem 0;">Impossible de charger les discussions.</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Stats Widget -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--bright-blue);">
                        <h3>Statistiques</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <?php
                            try {
                                $stats = $api->request('/stats');
                            } catch (Exception $e) {
                                $stats = [
                                    'user_count' => 0,
                                    'thread_count' => 0,
                                    'reply_count' => 0,
                                    'most_active_user' => null
                                ];
                            }
                            ?>
                            <li style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span>Membres:</span>
                                <strong><?php echo $stats['user_count'] ?? 0; ?></strong>
                            </li>
                            <li style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span>Discussions:</span>
                                <strong><?php echo $stats['thread_count'] ?? 0; ?></strong>
                            </li>
                            <li style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span>Messages:</span>
                                <strong><?php echo $stats['reply_count'] ?? 0; ?></strong>
                            </li>
                            <?php if (isset($stats['most_active_user']) && !empty($stats['most_active_user'])): ?>
                                <li style="display: flex; justify-content: space-between;">
                                    <span>Membre le plus actif:</span>
                                    <strong><a href="profile.php?id=<?php echo $stats['most_active_user']['id']; ?>"><?php echo htmlspecialchars($stats['most_active_user']['username']); ?></a></strong>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

