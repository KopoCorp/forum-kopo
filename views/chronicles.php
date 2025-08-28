<!-- Hero Banner -->
<section class="hero" style="background-color: var(--near-black); color: var(--white); padding: 4rem 0; position: relative;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(25,25,32,0.85);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h1 style="color: var(--white); font-size: 2.5rem; margin-bottom: 1.5rem;">Chroniques</h1>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">Découvrez les récits, témoignages et histoires de notre communauté. Partagez vos expériences et explorez celles des autres membres.</p>
            <div class="tech-stack-list" style="justify-content: center;">
                <div class="tech-badge" style="background-color: var(--purple);">RÉCITS</div>
                <div class="tech-badge" style="background-color: var(--bright-blue);">HISTOIRES</div>
                <div class="tech-badge" style="background-color: var(--dark-blue);">TÉMOIGNAGES</div>
                <div class="tech-badge" style="background-color: var(--accent-red);">EXPÉRIENCES</div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <!-- Chronique du jour Section -->
        <section class="chronicle-highlight-section" style="margin-bottom: 3rem;">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="display: flex; align-items: center; gap: 0.75rem;"><i class="fas fa-book-open" style="color: var(--purple);"></i> Chronique mise en avant</h2>
            </div>
            
            <div class="forum-container">
                <div class="snippet-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--light-gray); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 1.25rem;">Mon premier projet open source</h3>
                    <div class="tech-badge" style="background-color: var(--bright-blue);">Témoignage</div>
                </div>
                
                <div class="snippet-content" style="padding: 1.5rem;">
                    <p>Découvrez l'expérience inspirante d'un développeur qui a contribué pour la première fois à un projet open source et les leçons qu'il en a tirées.</p>
                    
                    <blockquote style="margin: 1.5rem 0; padding: 1rem; border-left: 4px solid var(--bright-blue); background-color: rgba(87, 184, 255, 0.1); font-style: italic;">
                        "Au début, j'avais peur de faire des erreurs. Mais la communauté open source m'a appris que les erreurs font partie de l'apprentissage. Chaque pull request était une opportunité d'apprendre quelque chose de nouveau."
                    </blockquote>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--light-gray);">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-user" style="color: #666;"></i>
                            <span style="color: #666;">Par Alex D.</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-calendar" style="color: #666;"></i>
                            <span style="color: #666;">Il y a 2 jours</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid-sidebar">
            <div>
                <!-- Latest Chronicles Articles -->
                <section>
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2>Articles récents</h2>
                        <a href="index.php?route=articles&tag=chroniques" class="view-all">Tous les articles <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if (!empty($chronicles_articles)): ?>
                        <div class="articles-grid">
                            <?php foreach (array_slice($chronicles_articles, 0, 6) as $article): ?>
                                <article class="article-card">
                                    <div class="article-meta">
                                        <span class="article-date"><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
                                    </div>
                                    <h3><a href="index.php?route=article&id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                                    <p><?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 150)); ?>...</p>
                                    <div class="article-footer">
                                        <div class="article-author">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars(get_username($article) ?? 'Auteur'); ?></span>
                                        </div>
                                        <?php if (isset($article['tags']) && is_array($article['tags'])): ?>
                                            <div class="article-tags">
                                                <?php foreach (array_slice($article['tags'], 0, 2) as $tag): ?>
                                                    <span class="tag"><?php echo htmlspecialchars(is_array($tag) ? $tag['name'] : $tag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="forum-container" style="text-align: center; padding: 2rem;">
                            <p>Aucune chronique disponible pour le moment.</p>
                            <a href="index.php?route=new-article" class="btn btn-primary" style="margin-top: 1rem;">Écrire une chronique</a>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Forum Discussions -->
                <section>
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2>Discussions récentes</h2>
                        <a href="index.php?route=forums&category=chronicles" class="view-all">Toutes les discussions <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="forum-container">
                        <?php if (!empty($chronicles_threads)): ?>
                            <ul class="forum-topics">
                                <?php foreach ($chronicles_threads as $thread): ?>
                                    <li class="topic-item">
                                        <div class="topic-icon">
                                            <i class="fas fa-comments" style="color: var(--purple);"></i>
                                        </div>
                                        <div class="topic-info">
                                            <h4><a href="thread.php?id=<?php echo $thread['id']; ?>"><?php echo htmlspecialchars($thread['title']); ?></a></h4>
                                            <div class="topic-meta">
                                                <div>par <a href="profile.php?id=<?php echo $thread['user_id']; ?>"><?php echo htmlspecialchars(get_username($thread) ?? 'Utilisateur'); ?></a></div>
                                                <div><?php echo date('d/m à H:i', strtotime($thread['created_at'])); ?></div>
                                            </div>
                                        </div>
                                        <div class="topic-stats">
                                            <?php if (isset($thread['responses_count'])): ?>
                                                <div class="stat">
                                                    <span class="stat-number"><?php echo $thread['responses_count']; ?></span>
                                                    <span class="stat-label">réponses</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="topic-last-activity">
                                            <?php if (isset($thread['last_activity'])): ?>
                                                <div>Dernière activité</div>
                                                <div>par <a href="profile.php?id=<?php echo $thread['user_id']; ?>"><?php echo htmlspecialchars(get_username($thread) ?? 'Utilisateur'); ?></a></div>
                                                <div><?php echo date('d/m à H:i', strtotime($thread['created_at'])); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem;">
                                <p>Aucune discussion sur les chroniques disponible pour le moment.</p>
                                <a href="index.php?route=new-thread" class="btn btn-primary" style="margin-top: 1rem;">Démarrer une discussion</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar">
                
                <!-- Writing Tips -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--purple);">
                        <h3>Conseils d'écriture</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-lightbulb" style="width: 20px; margin-right: 0.75rem; color: var(--purple);"></i>
                                    <div>
                                        <strong>Soyez authentique</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Partagez vos vraies expériences</p>
                                    </div>
                                </div>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-heart" style="width: 20px; margin-right: 0.75rem; color: var(--accent-red);"></i>
                                    <div>
                                        <strong>Racontez avec passion</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">L'émotion rend les histoires mémorables</p>
                                    </div>
                                </div>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-users" style="width: 20px; margin-right: 0.75rem; color: var(--bright-blue);"></i>
                                    <div>
                                        <strong>Pensez au lecteur</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Quelles leçons peut-il en tirer ?</p>
                                    </div>
                                </div>
                            </li>
                            <li style="padding: 0.75rem 0;">
                                <div style="display: flex; align-items: center;">
                                    <i class="fas fa-edit" style="width: 20px; margin-right: 0.75rem; color: var(--dark-blue);"></i>
                                    <div>
                                        <strong>Structurez votre récit</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Début, développement, conclusion</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Popular Topics -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--bright-blue);">
                        <h3>Sujets populaires</h3>
                    </div>
                    <div class="widget-content">
                        <div class="topic-tags">
                            <a href="index.php?route=articles&tag=premier-emploi" class="topic-tag" style="background-color: rgba(80, 74, 151, 0.1); color: var(--purple);">Premier emploi</a>
                            <a href="index.php?route=articles&tag=reconversion" class="topic-tag" style="background-color: rgba(87, 184, 255, 0.1); color: var(--bright-blue);">Reconversion</a>
                            <a href="index.php?route=articles&tag=startup" class="topic-tag" style="background-color: rgba(80, 74, 151, 0.1); color: var(--purple);">Startup</a>
                            <a href="index.php?route=articles&tag=freelance" class="topic-tag" style="background-color: rgba(87, 184, 255, 0.1); color: var(--bright-blue);">Freelance</a>
                            <a href="index.php?route=articles&tag=echec" class="topic-tag" style="background-color: rgba(239, 68, 68, 0.1); color: var(--accent-red);">Échecs et rebond</a>
                            <a href="index.php?route=articles&tag=succes" class="topic-tag" style="background-color: rgba(34, 197, 94, 0.1); color: #22c55e;">Histoires de succès</a>
                        </div>
                    </div>
                </div>

                <!-- Community Stats -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--dark-blue);">
                        <h3>Statistiques communauté</h3>
                    </div>
                    <div class="widget-content">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                            <div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: var(--purple);">42</div>
                                <div style="font-size: 0.875rem; color: #666;">Chroniques publiées</div>
                            </div>
                            <div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: var(--bright-blue);">156</div>
                                <div style="font-size: 0.875rem; color: #666;">Lecteurs actifs</div>
                            </div>
                            <div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: var(--accent-red);">23</div>
                                <div style="font-size: 0.875rem; color: #666;">Auteurs contributeurs</div>
                            </div>
                            <div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: var(--dark-blue);">89</div>
                                <div style="font-size: 0.875rem; color: #666;">Commentaires</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>