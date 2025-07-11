<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Bienvenue sur Kopo Forum</h1>
            <p>Rejoignez notre communauté dédiée à l'informatique, la cybersécurité et les technologies du numérique.</p>
            <div class="hero-buttons">
                <a href="index.php?route=register" class="btn btn-primary">Rejoindre la communauté</a>
                <a href="index.php?route=forums" class="btn btn-outline" style="background-color: transparent; border-color: var(--white); color: var(--white);">Explorer les discussions</a>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <div class="grid grid-sidebar">
            <!-- Main Content -->
            <div class="main">
                <!-- Featured Forums -->
                <section>
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2>Forums populaires</h2>
                        <a href="forums.php" class="view-all">Voir tous les forums <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="forum-container">
                        <?php if (isset($categories) && !empty($categories)): ?>
                            <?php foreach (array_slice($categories, 0, 2) as $category): ?>
                                <div class="forum-category">
                                    <div class="category-header">
                                        <h3 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                                        <?php
                                            $catThread = $category['thread_count'] ?? null;
                                            $catReply = $category['reply_count'] ?? null;
                                            if ($catThread === null || $catReply === null) {
                                                try {
                                                    $allThreads = $this->api->request('/forum/threads?category_id=' . $category['id']);
                                                    $catThread = is_array($allThreads) ? count($allThreads) : 0;
                                                    $catReply = 0;
                                                    if (is_array($allThreads)) {
                                                        foreach ($allThreads as $th) {
                                                            $catReply += $th['reply_count'] ?? 0;
                                                        }
                                                    }
                                                } catch (Exception $e) {
                                                    $catThread = 0;
                                                    $catReply = 0;
                                                }
                                            }
                                        ?>
                                        <span class="category-stats">
                                            <?php echo $catThread . ' discussions · ' . $catReply . ' messages'; ?>
                                        </span>
                                    </div>
                                    <?php if (isset($category['description'])): ?>
                                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                                    <?php endif; ?>
                                    <ul class="forum-topics">
                                        <?php
                                        $threads = [];
                                        try {
                                            $threads = $this->api->request('/forum/threads?category_id=' . $category['id'] . '&limit=2&skip=0');
                                        } catch (Exception $e) {
                                            // Silently fail if we can't get threads
                                        }
                                        if (!empty($threads)):
                                            foreach ($threads as $thread):
                                        ?>
                                            <li class="topic-item">
                                                <div class="topic-icon">
                                                    <i class="<?php echo $thread['is_pinned'] ? 'fas fa-thumbtack' : 'fas fa-comment'; ?>"></i>
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
                                                        <div>par <a href="profile.php?id=<?php echo $thread['user_id']; ?>"><?php echo htmlspecialchars(get_username($thread) ?? 'Utilisateur'); ?></a></div>
                                                        <div><?php echo date('d/m à H:i', strtotime($thread['created_at'])); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                        <?php
                                            endforeach;
                                        else:
                                        ?>
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
                        <?php else: ?>
                            <div class="forum-category">
                                <p style="text-align: center; padding: 2rem 0;">Aucune catégorie trouvée.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                <!-- Featured Articles -->
                <section style="margin-top: 3rem;">
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2>Articles à la une</h2>
                        <a href="articles.php" class="view-all">Tous les articles <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="grid grid-3">
                        <?php if (isset($latest_articles) && !empty($latest_articles)): ?>
                            <?php foreach ($latest_articles as $article): ?>
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
                                            <a href="article.php?id=<?php echo $article['id']; ?>" class="read-more">Lire la suite <i class="fas fa-arrow-right"></i></a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="article-card" style="grid-column: 1 / -1;">
                                <div class="article-card-content" style="text-align: center; padding: 3rem 0;">
                                    <p>Aucun article disponible pour le moment.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                <!-- Cybersecurity Alert -->
                <?php if (isset($latest_security_alert) && !empty($latest_security_alert)): ?>
                <section class="security-alert" style="margin-top: 3rem;">
                    <h4>
                        <i class="fas fa-shield-alt"></i> Alerte de sécurité
                        <?php if (isset($latest_security_alert['severity'])): ?>
                            <span class="badge" style="font-size: 0.7rem; background-color: <?php echo $latest_security_alert['severity'] === 'high' ? 'var(--accent-red)' : ($latest_security_alert['severity'] === 'medium' ? 'var(--bright-blue)' : 'var(--purple)'); ?>; float: right;">
                                <?php echo strtoupper($latest_security_alert['severity']); ?>
                            </span>
                        <?php endif; ?>
                    </h4>
                    <p><?php echo htmlspecialchars($latest_security_alert['title']); ?></p>
                    <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                        <span><?php echo date('d/m/Y', strtotime($latest_security_alert['published'] ?? $latest_security_alert['created_at'])); ?></span>
                        <a href="<?php echo htmlspecialchars($latest_security_alert['link']); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Voir sur CERT-FR</a>
                    </div>
                </section>
                <?php endif; ?>
                <!-- CTA Section -->
                <section class="cta-section" style="margin: 3rem 0; padding: 3rem; background: linear-gradient(135deg, #504A97, #23255D); color: var(--white); border-radius: var(--border-radius); text-align: center;">
                    <h2 style="color: var(--white); font-size: 2rem; margin-bottom: 1.5rem;">Rejoignez la conversation</h2>
                    <p style="max-width: 700px; margin: 0 auto 2rem; font-size: 1.125rem;">Participez aux discussions, partagez vos connaissances et connectez-vous avec une communauté passionnée d'informatique et de cybersécurité.</p>
                    <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                        <a href="index.php?route=register" class="btn btn-primary" style="min-width: 160px;">S'inscrire</a>
                        <a href="index.php?route=forums" class="btn btn-outline" style="min-width: 160px; border-color: var(--white); color: var(--white);">Explorer les forums</a>
                    </div>
                </section>
            </div>
            <!-- Sidebar -->
            <aside class="sidebar">
                <?php if (!$this->api->isLoggedIn()): ?>
                <!-- Login Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <h3>Connectez-vous</h3>
                    </div>
                    <div class="widget-content">
                        <form action="index.php?route=login" method="post">
                            <div class="form-group">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="form-check">
                                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                                    <label for="remember">Se souvenir de moi</label>
                                </div>
                                <a href="forgot-password.php" style="font-size: 0.875rem;">Mot de passe oublié?</a>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Connexion</button>
                        </form>
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="index.php?route=register">Créer un compte</a>
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
                        <?php $user = $this->api->getCurrentUser(); ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="<?php echo isset($user['avatar_url']) && !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url']) : DEFAULT_AVATAR_URL; ?>" alt="Avatar" class="user-avatar" style="margin: 0 auto 0.5rem; width: 80px; height: 80px;">
                            <h4 style="margin: 0;">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </h4>
                            <div style="color: #666; font-size: 0.875rem;">
                                Membre depuis <?php echo date('M Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 600;">
                                    <?php echo $user['thread_count'] ?? 0; ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #666;">Discussions</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 600;">
                                    <?php echo $user['reply_count'] ?? 0; ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #666;">Réponses</div>
                            </div>
                        </div>
                        <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-outline" style="width: 100%;">Voir mon profil</a>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Hot Topics -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--accent-red);">
                        <h3>Sujets populaires</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <?php if (isset($popular_threads) && !empty($popular_threads)): ?>
                                <?php foreach (array_slice($popular_threads, 0, 5) as $thread): ?>
                                    <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                        <a href="thread.php?id=<?php echo $thread['id']; ?>" style="font-weight: 500;"><?php echo htmlspecialchars($thread['title']); ?></a>
                                        <div style="font-size: 0.8125rem; color: #666; margin-top: 0.25rem;">
                                            <span><?php echo $thread['reply_count'] ?? 0; ?> réponses</span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li style="padding: 1rem 0; text-align: center;">Aucun sujet populaire pour le moment.</li>
                              <?php endif; ?>
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
                                $stats = calculate_forum_stats($this->api);
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