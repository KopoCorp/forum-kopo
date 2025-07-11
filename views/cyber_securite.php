
<!-- Hero Banner -->
<section class="hero" style="background-color: var(--near-black); color: var(--white); padding: 4rem 0; position: relative;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(25,25,32,0.85);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h1 style="color: var(--white); font-size: 2.5rem; margin-bottom: 1.5rem;">Cybersécurité</h1>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">Découvrez les dernières actualités, analyses et conseils sur la sécurité informatique, les vulnérabilités et la protection des données numériques.</p>
            <div class="tech-stack-list" style="justify-content: center;">
                <div class="tech-badge" style="background-color: var(--accent-red);">SÉCURITÉ INFORMATIQUE</div>
                <div class="tech-badge" style="background-color: var(--purple);">PENTESTING</div>
                <div class="tech-badge" style="background-color: var(--bright-blue);">CRYPTOGRAPHIE</div>
                <div class="tech-badge" style="background-color: var(--dark-blue);">PROTECTION DES DONNÉES</div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <!-- Security Alerts Section -->
        <section class="security-alerts-section" style="margin-bottom: 3rem;">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="display: flex; align-items: center; gap: 0.75rem;"><i class="fas fa-shield-alt" style="color: var(--accent-red);"></i> Alertes de Sécurité</h2>
                <a href="https://www.cert.ssi.gouv.fr/alerte/" target="_blank" rel="noopener" class="view-all">Toutes les alertes <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <?php if (!empty($security_alerts)): ?>
                <div class="alerts-container">
                    <?php foreach ($security_alerts as $alert): ?>
                        <div class="security-alert" style="border-left-color: <?php echo $alert['severity'] === 'high' ? 'var(--accent-red)' : ($alert['severity'] === 'medium' ? 'var(--bright-blue)' : 'var(--purple)'); ?>;">
                            <h4>
                                <i class="fas fa-exclamation-triangle" style="color: <?php echo $alert['severity'] === 'high' ? 'var(--accent-red)' : ($alert['severity'] === 'medium' ? 'var(--bright-blue)' : 'var(--purple)'); ?>;"></i>
                                <?php echo htmlspecialchars($alert['title']); ?>
                                
                                <span class="badge" style="font-size: 0.7rem; background-color: <?php echo $alert['severity'] === 'high' ? 'var(--accent-red)' : ($alert['severity'] === 'medium' ? 'var(--bright-blue)' : 'var(--purple)'); ?>; float: right;">
                                    <?php echo $alert['severity'] === 'high' ? 'CRITIQUE' : ($alert['severity'] === 'medium' ? 'MODÉRÉ' : 'FAIBLE'); ?>
                                </span>
                            </h4>
                            
                            <p><?php echo htmlspecialchars($alert['description']); ?></p>
                            
                            <div style="margin-top: 0.75rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                                <span>Publié le <?php echo date('d/m/Y', strtotime($alert['published'] ?? $alert['created_at'])); ?></span>
                                <a href="<?php echo htmlspecialchars($alert['link']); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                                    Voir sur CERT-FR
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="forum-container" style="text-align: center; padding: 2rem;">
                    <p>Aucune alerte de sécurité disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </section>
        
        <div class="grid grid-sidebar">
            <!-- Main Content Column -->
            <div>
                <!-- Featured Articles -->
                <section style="margin-bottom: 3rem;">
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2>Articles sur la Cybersécurité</h2>
                        <a href="articles.php?tag=<?php echo $security_tag_id !== null ? $security_tag_id : urlencode('cybersécurité'); ?>" class="view-all">Tous les articles <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if (!empty($security_articles)): ?>
                        <div class="grid grid-2">
                            <?php foreach ($security_articles as $article): ?>
                                <article class="article-card">
                                    <div class="article-card-image">
                                        <?php if (isset($article['image_url']) && !empty($article['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                        <?php endif; ?>
                                        <div class="article-card-label" style="background-color: var(--accent-red);">CYBERSÉCURITÉ</div>
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
                        </div>
                    <?php else: ?>
                        <div class="forum-container" style="text-align: center; padding: 2rem;">
                            <p>Aucun article sur la cybersécurité disponible pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Forum Discussions -->
                <section>
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2>Discussions sur la Sécurité</h2>
                        <a href="index.php?route=forums&category=security" class="view-all">Toutes les discussions <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="forum-container">
                        <?php if (!empty($security_threads)): ?>
                            <ul class="forum-topics">
                                <?php foreach ($security_threads as $thread): ?>
                                    <li class="topic-item">
                                        <div class="topic-icon">
                                            <i class="<?php echo $thread['is_pinned'] ? 'fas fa-thumbtack' : ($thread['reply_count'] > 10 ? 'fas fa-fire' : 'fas fa-shield-alt'); ?>"></i>
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
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem;">
                                <p>Aucune discussion sur la sécurité disponible pour le moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar">
                <!-- Security Tools -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--accent-red);">
                        <h3>Outils de Sécurité</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://haveibeenpwned.com/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
                                    <i class="fas fa-search" style="width: 20px; margin-right: 0.75rem;"></i>
                                    <div>
                                        <strong>Have I Been Pwned</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Vérifiez si vos comptes ont été compromis</p>
                                    </div>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://www.virustotal.com/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
                                    <i class="fas fa-virus-slash" style="width: 20px; margin-right: 0.75rem;"></i>
                                    <div>
                                        <strong>VirusTotal</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Analysez des fichiers suspects</p>
                                    </div>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://www.ssllabs.com/ssltest/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
                                    <i class="fas fa-lock" style="width: 20px; margin-right: 0.75rem;"></i>
                                    <div>
                                        <strong>SSL Labs</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Testez la configuration SSL/TLS d'un site</p>
                                    </div>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0;">
                                <a href="https://www.shodan.io/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
                                    <i class="fas fa-globe" style="width: 20px; margin-right: 0.75rem;"></i>
                                    <div>
                                        <strong>Shodan</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Explorez les appareils connectés à Internet</p>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Resources -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--dark-blue);">
                        <h3>Ressources</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://www.cert.ssi.gouv.fr/" target="_blank" rel="noopener noreferrer">
                                    <strong>ANSSI - CERT-FR</strong>
                                    <p style="margin: 0; font-size: 0.875rem; color: #666;">Centre gouvernemental de veille, d'alerte et de réponse aux attaques informatiques</p>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://www.cisa.gov/" target="_blank" rel="noopener noreferrer">
                                    <strong>CISA</strong>
                                    <p style="margin: 0; font-size: 0.875rem; color: #666;">Cybersecurity & Infrastructure Security Agency (US)</p>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://owasp.org/" target="_blank" rel="noopener noreferrer">
                                    <strong>OWASP</strong>
                                    <p style="margin: 0; font-size: 0.875rem; color: #666;">Open Web Application Security Project</p>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0;">
                                <a href="https://www.mitre.org/attack" target="_blank" rel="noopener noreferrer">
                                    <strong>MITRE ATT&CK</strong>
                                    <p style="margin: 0; font-size: 0.875rem; color: #666;">Base de connaissances sur les tactiques et techniques d'attaque</p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Password Generator -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--purple);">
                        <h3>Générateur de mot de passe</h3>
                    </div>
                    <div class="widget-content">
                        <div id="password-generator">
                            <div class="form-group">
                                <label for="password-length" class="form-label">Longueur</label>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <input type="range" id="password-length" min="8" max="32" value="16" class="form-control">
                                    <span id="length-value">16</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" id="include-uppercase" class="form-check-input" checked>
                                    <label for="include-uppercase">Majuscules (A-Z)</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="include-numbers" class="form-check-input" checked>
                                    <label for="include-numbers">Chiffres (0-9)</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="include-symbols" class="form-check-input" checked>
                                    <label for="include-symbols">Symboles (!@#$%^&*)</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="password-display" style="position: relative;">
                                    <input type="text" id="generated-password" class="form-control" readonly>
                                    <button type="button" id="copy-password" class="btn btn-sm" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--bright-blue);">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" id="generate-password" class="btn btn-primary" style="width: 100%;">Générer</button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
        
        <!-- CTA Section -->
        <section class="cta-section" style="margin: 4rem 0; padding: 3rem; background-color: var(--accent-red); color: var(--white); border-radius: var(--border-radius); text-align: center;">
            <h2 style="color: var(--white); font-size: 2rem; margin-bottom: 1.5rem;">Restez informé des dernières menaces</h2>
            <p style="max-width: 700px; margin: 0 auto 2rem; font-size: 1.125rem;">Les cybermenaces évoluent constamment. Inscrivez-vous à notre newsletter pour recevoir les dernières alertes de sécurité et conseils de protection.</p>
            <form class="newsletter-form" style="max-width: 500px; margin: 0 auto; display: flex;">
                <input type="email" placeholder="Votre adresse email" class="form-control" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none; flex: 1;">
                <button type="submit" class="btn btn-secondary" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">S'abonner</button>
            </form>
        </section>
    </div>
</main>

<script>
// Password Generator
document.addEventListener('DOMContentLoaded', function() {
    const lengthSlider = document.getElementById('password-length');
    const lengthValue = document.getElementById('length-value');
    const includeUppercase = document.getElementById('include-uppercase');
    const includeNumbers = document.getElementById('include-numbers');
    const includeSymbols = document.getElementById('include-symbols');
    const generatedPassword = document.getElementById('generated-password');
    const generateButton = document.getElementById('generate-password');
    const copyButton = document.getElementById('copy-password');
    
    // Update length value display when slider moves
    lengthSlider.addEventListener('input', function() {
        lengthValue.textContent = this.value;
    });
    
    // Generate password
    function generatePassword() {
        const length = parseInt(lengthSlider.value);
        const hasUppercase = includeUppercase.checked;
        const hasNumbers = includeNumbers.checked;
        const hasSymbols = includeSymbols.checked;
        
        let chars = 'abcdefghijklmnopqrstuvwxyz';
        if (hasUppercase) chars += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (hasNumbers) chars += '0123456789';
        if (hasSymbols) chars += '!@#$%^&*()_+[]{}|;:,.<>?';
        
        let password = '';
        for (let i = 0; i < length; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        generatedPassword.value = password;
    }
    
    // Copy password to clipboard
    copyButton.addEventListener('click', function() {
        if (generatedPassword.value) {
            generatedPassword.select();
            document.execCommand('copy');
            
            // Show feedback
            const originalIcon = copyButton.innerHTML;
            copyButton.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                copyButton.innerHTML = originalIcon;
            }, 1500);
        }
    });
    
    // Generate initial password
    generateButton.addEventListener('click', generatePassword);
    generatePassword();
});
</script>

