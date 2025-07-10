<?php
$page_title = "Développement";
$page_description = "Ressources, articles et discussions sur le développement informatique, la programmation et les langages de code";
require_once 'functions.php';
require_once 'header.php';

// Retrieve tag id for "développement"
$dev_tag_id = null;
try {
    $tags_list = $api->request('/tags');
    foreach ($tags_list as $t) {
        if (strtolower($t['name']) === 'développement') {
            $dev_tag_id = $t['id'];
            break;
        }
    }
} catch (Exception $e) {
    $dev_tag_id = null;
}

try {
    // Load recent articles then filter by development keywords
    $all_articles = $api->request('/articles?limit=20');
    $dev_articles = [];
    foreach ($all_articles as $article) {
        $tags = array_column($article['tags'] ?? [], 'name');
        if (detect_topic($tags, ($article['title'] ?? '') . ' ' . ($article['content'] ?? '')) === 'dev') {
            $dev_articles[] = $article;
        }
        if (count($dev_articles) >= 6) {
            break;
        }
    }

    // Load forum threads and filter them as well
    $all_threads = $api->request('/forum/threads?limit=20');
    $dev_threads = [];
    foreach ($all_threads as $thread) {
        if (detect_topic([], ($thread['title'] ?? '') . ' ' . ($thread['content'] ?? '')) === 'dev') {
            $dev_threads[] = $thread;
        }
        if (count($dev_threads) >= 5) {
            break;
        }
    }

    // Get trending technologies
    $trending_techs = $api->request('/technologies/trending?limit=5');
    
} catch (Exception $e) {
    $_SESSION['flash_message'] = "Erreur lors du chargement des données: " . $e->getMessage();
    $_SESSION['flash_type'] = "error";
    
    $dev_articles = [];
    $dev_threads = [];
    $trending_techs = [];
}
?>

<!-- Hero Banner -->
<section class="hero" style="background-color: var(--near-black); color: var(--white); padding: 4rem 0; position: relative;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(25,25,32,0.85);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h1 style="color: var(--white); font-size: 2.5rem; margin-bottom: 1.5rem;">Développement</h1>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">Explorez les ressources, tutoriels et discussions sur la programmation, les frameworks et les meilleures pratiques de développement.</p>
            <div class="tech-stack-list" style="justify-content: center;">
                <div class="tech-badge" style="background-color: var(--purple);">WEB</div>
                <div class="tech-badge" style="background-color: var(--bright-blue);">MOBILE</div>
                <div class="tech-badge" style="background-color: var(--dark-blue);">IA</div>
                <div class="tech-badge" style="background-color: var(--accent-red);">DEVOPS</div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <!-- Code Snippet Section -->
        <section class="code-snippet-section" style="margin-bottom: 3rem;">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="display: flex; align-items: center; gap: 0.75rem;"><i class="fas fa-code" style="color: var(--purple);"></i> Astuce du jour</h2>
                <a href="#" class="view-all">Plus d'astuces <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="forum-container">
                <div class="snippet-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--light-gray); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 1.25rem;">Déboguer en JavaScript avec console.table()</h3>
                    <div class="tech-badge" style="background-color: var(--bright-blue);">JavaScript</div>
                </div>
                
                <div class="snippet-content" style="padding: 1.5rem;">
                    <p>Plutôt que d'utiliser <code>console.log()</code> pour afficher des objets ou tableaux complexes, utilisez <code>console.table()</code> pour une visualisation bien plus claire dans la console :</p>
                    
                    <div style="margin: 1.5rem 0;">
                        <div class="code-header">
                            <span class="code-language">javascript</span>
                            <button class="code-copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copier</button>
                        </div>
                        <pre><code class="language-javascript">// Des données d'exemple
const users = [
  { id: 1, name: 'Alice', role: 'admin', active: true },
  { id: 2, name: 'Bob', role: 'editor', active: false },
  { id: 3, name: 'Charlie', role: 'user', active: true }
];

// Affichage traditionnel
console.log('Utilisateurs:', users);

// Affichage amélioré avec console.table()
console.table(users);

// Vous pouvez aussi filtrer les colonnes
console.table(users, ['name', 'role']);</code></pre>
                    </div>
                    
                    <p>Cette méthode facilite grandement la lecture des données structurées et permet de trier les colonnes directement dans la console de votre navigateur.</p>
                </div>
            </div>
        </section>
        
        <div class="grid grid-sidebar">
            <!-- Main Content Column -->
            <div>
                <!-- Featured Articles -->
                <section style="margin-bottom: 3rem;">
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2>Articles sur le développement</h2>
                        <a href="articles.php?tag=<?php echo $dev_tag_id !== null ? $dev_tag_id : urlencode('développement'); ?>" class="view-all">Tous les articles <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if (!empty($dev_articles)): ?>
                        <div class="grid grid-2">
                            <?php foreach ($dev_articles as $article): ?>
                                <article class="article-card">
                                    <div class="article-card-image">
                                        <?php if (isset($article['image_url']) && !empty($article['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                        <?php endif; ?>
                                        <div class="article-card-label" style="background-color: var(--purple);">DÉVELOPPEMENT</div>
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
                    <?php else: ?>
                        <div class="forum-container" style="text-align: center; padding: 2rem;">
                            <p>Aucun article sur le développement disponible pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Forum Discussions -->
                <section>
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2>Discussions sur le développement</h2>
                        <a href="index.php?route=forums&category=dev" class="view-all">Toutes les discussions <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="forum-container">
                        <?php if (!empty($dev_threads)): ?>
                            <ul class="forum-topics">
                                <?php foreach ($dev_threads as $thread): ?>
                                    <li class="topic-item">
                                        <div class="topic-icon">
                                            <i class="<?php echo $thread['is_pinned'] ? 'fas fa-thumbtack' : ($thread['reply_count'] > 10 ? 'fas fa-fire' : 'fas fa-code'); ?>"></i>
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
                            </ul>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem;">
                                <p>Aucune discussion sur le développement disponible pour le moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar">
                <!-- Trending Technologies -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--purple);">
                        <h3>Technologies en tendance</h3>
                    </div>
                    <div class="widget-content">
                        <?php if (!empty($trending_techs)): ?>
                            <ul style="list-style: none;">
                                <?php foreach ($trending_techs as $tech): ?>
                                    <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray); display: flex; align-items: center; justify-content: space-between;">
                                        <div>
                                            <strong><?php echo htmlspecialchars($tech['name']); ?></strong>
                                            <div style="font-size: 0.875rem; color: #666;"><?php echo htmlspecialchars($tech['category']); ?></div>
                                        </div>
                                        <div class="trend-indicator <?php echo $tech['trend'] === 'up' ? 'up' : 'down'; ?>" style="color: <?php echo $tech['trend'] === 'up' ? '#28a745' : '#dc3545'; ?>; font-weight: bold;">
                                            <i class="fas fa-arrow-<?php echo $tech['trend'] === 'up' ? 'up' : 'down'; ?>"></i>
                                            <?php echo $tech['percentage']; ?>%
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="text-align: center;">Données non disponibles.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Dev Tools -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--bright-blue);">
                        <h3>Outils de développement</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://github.com/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
                                    <i class="fab fa-github" style="width: 20px; margin-right: 0.75rem;"></i>
                                    <div>
                                        <strong>GitHub</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Hébergement et collaboration de code</p>
                                    </div>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://code.visualstudio.com/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
                                    <i class="fas fa-code" style="width: 20px; margin-right: 0.75rem;"></i>
                                    <div>
                                        <strong>VS Code</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Éditeur de code polyvalent</p>
                                    </div>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://www.figma.com/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
                                    <i class="fab fa-figma" style="width: 20px; margin-right: 0.75rem;"></i>
                                    <div>
                                        <strong>Figma</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Design d'interface et prototypage</p>
                                    </div>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0;">
                                <a href="https://codesandbox.io/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center;">
                                    <i class="fas fa-cube" style="width: 20px; margin-right: 0.75rem;"></i>
                                    <div>
                                        <strong>CodeSandbox</strong>
                                        <p style="margin: 0; font-size: 0.875rem; color: #666;">Environnement de développement en ligne</p>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Learning Resources -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--dark-blue);">
                        <h3>Ressources d'apprentissage</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://www.freecodecamp.org/" target="_blank" rel="noopener noreferrer">
                                    <strong>FreeCodeCamp</strong>
                                    <p style="margin: 0; font-size: 0.875rem; color: #666;">Cours de programmation gratuits et projets pratiques</p>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://www.codecademy.com/" target="_blank" rel="noopener noreferrer">
                                    <strong>Codecademy</strong>
                                    <p style="margin: 0; font-size: 0.875rem; color: #666;">Apprentissage interactif de nombreux langages</p>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--light-gray);">
                                <a href="https://developer.mozilla.org/" target="_blank" rel="noopener noreferrer">
                                    <strong>MDN Web Docs</strong>
                                    <p style="margin: 0; font-size: 0.875rem; color: #666;">Documentation complète pour les technologies web</p>
                                </a>
                            </li>
                            <li style="padding: 0.75rem 0;">
                                <a href="https://www.theodinproject.com/" target="_blank" rel="noopener noreferrer">
                                    <strong>The Odin Project</strong>
                                    <p style="margin: 0; font-size: 0.875rem; color: #666;">Curriculum complet de développement web full stack</p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
        
        <!-- Code Playground -->
        <section style="margin-top: 3rem;">
            <div class="section-header" style="margin-bottom: 1.5rem;">
                <h2>Code Playground</h2>
                <p>Testez rapidement du code HTML, CSS et JavaScript directement dans votre navigateur.</p>
            </div>
            
            <div class="forum-container" style="overflow: hidden;">
                <div class="code-playground">
                    <div class="code-playground-tabs" style="display: flex; border-bottom: 1px solid var(--light-gray);">
                        <button class="playground-tab active" data-target="html" style="flex: 1; padding: 1rem; text-align: center; background: none; border: none; border-bottom: 2px solid var(--bright-blue); font-weight: 600; cursor: pointer;">HTML</button>
                        <button class="playground-tab" data-target="css" style="flex: 1; padding: 1rem; text-align: center; background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer;">CSS</button>
                        <button class="playground-tab" data-target="js" style="flex: 1; padding: 1rem; text-align: center; background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer;">JavaScript</button>
                        <button class="playground-tab" data-target="result" style="flex: 1; padding: 1rem; text-align: center; background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer;">Résultat</button>
                    </div>
                    
                    <div class="code-editors" style="display: flex; flex-direction: column; height: 400px;">
                        <div id="editor-html" class="editor active" style="height: 100%; display: block;">
                            <textarea style="width: 100%; height: 100%; border: none; padding: 1rem; font-family: var(--font-mono); font-size: 14px; resize: none;" placeholder="Entrez votre code HTML ici...">&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Mon Playground&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Bienvenue sur le playground!&lt;/h1&gt;
    &lt;p&gt;Modifiez le code pour voir le résultat en temps réel.&lt;/p&gt;
    &lt;button id="myButton"&gt;Cliquez-moi!&lt;/button&gt;
&lt;/body&gt;
&lt;/html&gt;</textarea>
                        </div>
                        <div id="editor-css" class="editor" style="height: 100%; display: none;">
                            <textarea style="width: 100%; height: 100%; border: none; padding: 1rem; font-family: var(--font-mono); font-size: 14px; resize: none;" placeholder="Entrez votre code CSS ici...">body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

h1 {
    color: #504A97;
}

button {
    background-color: #57B8FF;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

button:hover {
    background-color: #85BAFF;
}</textarea>
                        </div>
                        <div id="editor-js" class="editor" style="height: 100%; display: none;">
                            <textarea style="width: 100%; height: 100%; border: none; padding: 1rem; font-family: var(--font-mono); font-size: 14px; resize: none;" placeholder="Entrez votre code JavaScript ici...">document.getElementById('myButton').addEventListener('click', function() {
    alert('Bonjour depuis le code playground!');
});</textarea>
                        </div>
                        <div id="editor-result" class="editor" style="height: 100%; display: none; background-color: white; border: none; padding: 1rem;">
                            <iframe id="result-frame" style="width: 100%; height: 100%; border: none;"></iframe>
                        </div>
                    </div>
                    
                    <div class="playground-actions" style="padding: 1rem; display: flex; justify-content: space-between; border-top: 1px solid var(--light-gray);">
                        <button id="run-button" class="btn btn-primary">
                            <i class="fas fa-play"></i> Exécuter le code
                        </button>
                        <button id="reset-button" class="btn btn-outline">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- CTA Section -->
        <section class="cta-section" style="margin: 4rem 0; padding: 3rem; background-color: var(--purple); color: var(--white); border-radius: var(--border-radius); text-align: center;">
            <h2 style="color: var(--white); font-size: 2rem; margin-bottom: 1.5rem;">Partagez vos connaissances</h2>
            <p style="max-width: 700px; margin: 0 auto 2rem; font-size: 1.125rem;">Vous êtes développeur et vous souhaitez contribuer à notre communauté ? Partagez vos tutoriels, astuces ou retours d'expérience à travers des articles ou des discussions.</p>
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                <a href="new-article.php" class="btn btn-light" style="background-color: var(--white); color: var(--purple);">Rédiger un article</a>
                <a href="new-thread.php?category=dev" class="btn btn-outline" style="border-color: var(--white); color: var(--white);">Démarrer une discussion</a>
            </div>
        </section>
    </div>
</main>

<script>
// Code Playground functionality
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.playground-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            document.querySelectorAll('.playground-tab').forEach(t => {
                t.classList.remove('active');
                t.style.borderBottom = '2px solid transparent';
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            this.style.borderBottom = '2px solid var(--bright-blue)';
            
            // Hide all editors
            document.querySelectorAll('.editor').forEach(editor => {
                editor.style.display = 'none';
            });
            
            // Show selected editor
            const target = this.getAttribute('data-target');
            document.getElementById('editor-' + target).style.display = 'block';
            
            // Update result if result tab is clicked
            if (target === 'result') {
                updateResult();
            }
        });
    });
    
    // Run button
    document.getElementById('run-button').addEventListener('click', function() {
        updateResult();
        
        // Switch to result tab
        document.querySelector('.playground-tab[data-target="result"]').click();
    });
    
    // Reset button
    document.getElementById('reset-button').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser le code?')) {
            document.querySelectorAll('.editor textarea').forEach(textarea => {
                textarea.value = textarea.defaultValue;
            });
            updateResult();
        }
    });
    
    // Function to update result
    function updateResult() {
        const html = document.querySelector('#editor-html textarea').value;
        const css = document.querySelector('#editor-css textarea').value;
        const js = document.querySelector('#editor-js textarea').value;
        
        const iframe = document.getElementById('result-frame');
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        
        iframeDoc.open();
        iframeDoc.write(`
            ${html}
            <style>${css}</style>
            <script>${js}<\/script>
        `);
        iframeDoc.close();
    }
    
    // Copy code function
    window.copyCode = function(button) {
        const preEl = button.closest('.code-header').nextElementSibling;
        const codeEl = preEl.querySelector('code');
        
        if (codeEl) {
            const textArea = document.createElement('textarea');
            textArea.value = codeEl.textContent;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            // Show feedback
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Copié!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }
    };
});
</script>

<?php include 'footer.php'; ?>