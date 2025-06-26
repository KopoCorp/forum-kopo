<?php
require_once 'config.php';
require_once 'api.php';

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    if ($api->isLoggedIn()) {
        // If no ID is provided but user is logged in, show their own profile
        $user_id = $_SESSION['user']['id'];
    } else {
        header('Location: index.php');
        exit();
    }
} else {
    $user_id = (int)$_GET['id'];
}

try {
    // Get user profile
    $profile = $api->request('/users/' . $user_id . '/profile');
    
    // Get user's recent activity
    $recent_threads = $api->request('/users/' . $user_id . '/threads?limit=5');
    $recent_articles = $api->request('/users/' . $user_id . '/articles?limit=5');
    
    $page_title = $profile['username'] . " - Profil";
    $page_description = "Profil de " . $profile['username'] . " sur KOPO Forum";
} catch (Exception $e) {
    $_SESSION['flash_message'] = "Erreur: " . $e->getMessage();
    $_SESSION['flash_type'] = "error";
    
    header('Location: index.php');
    exit();
}

// Handle following/unfollowing
$follow_error = '';
$is_following = false;

if ($api->isLoggedIn() && $user_id != $_SESSION['user']['id']) {
    try {
        $follow_status = $api->request('/users/' . $_SESSION['user']['id'] . '/following/' . $user_id, 'GET', [], true);
        $is_following = $follow_status['is_following'] ?? false;
    } catch (Exception $e) {
        // Silently fail if we can't get follow status
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow_action'])) {
        try {
            if ($_POST['follow_action'] === 'follow') {
                $api->request('/users/' . $_SESSION['user']['id'] . '/following', 'POST', ['following_id' => $user_id], true);
                $is_following = true;
            } elseif ($_POST['follow_action'] === 'unfollow') {
                $api->request('/users/' . $_SESSION['user']['id'] . '/following/' . $user_id, 'DELETE', [], true);
                $is_following = false;
            }
        } catch (Exception $e) {
            $follow_error = $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; 
            <a href="membres.php" style="color: #999;">Membres</a> &raquo; 
            <?php echo htmlspecialchars($profile['username']); ?>
        </div>
    </div>
</div>

<main class="main-content section">
    <div class="container">
        <div class="grid grid-sidebar">
            <!-- Main Content -->
            <div class="profile-main">
                <!-- Profile Header -->
                <div class="forum-container" style="margin-bottom: 2rem;">
                    <div class="profile-header" style="padding: 2rem; display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                        <div class="profile-avatar" style="flex-shrink: 0;">
                            <img src="<?php echo isset($profile['avatar_url']) && !empty($profile['avatar_url']) ? htmlspecialchars($profile['avatar_url']) : 'assets/images/default-avatar.png'; ?>" alt="Avatar" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                        </div>
                        
                        <div class="profile-info" style="flex-grow: 1; min-width: 200px;">
                            <h1 style="margin-top: 0; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 1rem;">
                                <?php echo htmlspecialchars($profile['username']); ?>
                                <?php if (isset($profile['is_verified']) && $profile['is_verified']): ?>
                                    <span title="Compte vérifié" style="color: var(--bright-blue);"><i class="fas fa-check-circle"></i></span>
                                <?php endif; ?>
                            </h1>
                            
                            <?php if (isset($profile['role']) && !empty($profile['role'])): ?>
                                <div style="margin-bottom: 1rem;">
                                    <span class="badge" style="background-color: <?php echo $profile['role'] === 'admin' ? 'var(--accent-red)' : 'var(--bright-blue)'; ?>; font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 20px;">
                                        <?php echo htmlspecialchars($profile['role']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="profile-bio" style="margin-bottom: 1rem; max-width: 600px;">
                                <?php 
                                if (isset($profile['bio']) && !empty($profile['bio'])) {
                                    echo '<p>' . nl2br(htmlspecialchars($profile['bio'])) . '</p>';
                                } else {
                                    echo '<p style="color: #666; font-style: italic;">Aucune biographie disponible.</p>';
                                }
                                ?>
                            </div>
                            
                            <div class="profile-meta" style="display: flex; flex-wrap: wrap; gap: 1.5rem; color: #666; font-size: 0.875rem;">
                                <div><i class="far fa-calendar-alt"></i> Membre depuis <?php echo date('M Y', strtotime($profile['created_at'])); ?></div>
                                
                                <?php if (isset($profile['location']) && !empty($profile['location'])): ?>
                                    <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($profile['location']); ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($profile['website']) && !empty($profile['website'])): ?>
                                    <div><i class="fas fa-link"></i> <a href="<?php echo htmlspecialchars($profile['website']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($profile['website']); ?></a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="profile-actions" style="margin-left: auto; text-align: right;">
                            <?php if ($api->isLoggedIn() && $user_id == $_SESSION['user']['id']): ?>
                                <a href="settings.php" class="btn btn-outline">
                                    <i class="fas fa-cog"></i> Modifier le profil
                                </a>
                            <?php elseif ($api->isLoggedIn()): ?>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <form method="post">
                                        <?php if ($is_following): ?>
                                            <input type="hidden" name="follow_action" value="unfollow">
                                            <button type="submit" class="btn btn-outline">
                                                <i class="fas fa-user-minus"></i> Ne plus suivre
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="follow_action" value="follow">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-user-plus"></i> Suivre
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    
                                    <a href="messages.php?to=<?php echo $user_id; ?>" class="btn btn-outline">
                                        <i class="far fa-envelope"></i> Message
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Profile Stats -->
                    <div class="profile-stats" style="display: flex; border-top: 1px solid var(--light-gray); padding: 1rem 2rem;">
                        <div style="flex: 1; text-align: center; padding: 0.5rem 0;">
                            <div style="font-size: 1.5rem; font-weight: 600;"><?php echo $profile['thread_count'] ?? 0; ?></div>
                            <div style="color: #666; font-size: 0.875rem;">Discussions</div>
                        </div>
                        <div style="flex: 1; text-align: center; border-left: 1px solid var(--light-gray); padding: 0.5rem 0;">
                            <div style="font-size: 1.5rem; font-weight: 600;"><?php echo $profile['reply_count'] ?? 0; ?></div>
                            <div style="color: #666; font-size: 0.875rem;">Réponses</div>
                        </div>
                        <div style="flex: 1; text-align: center; border-left: 1px solid var(--light-gray); padding: 0.5rem 0;">
                            <div style="font-size: 1.5rem; font-weight: 600;"><?php echo $profile['article_count'] ?? 0; ?></div>
                            <div style="color: #666; font-size: 0.875rem;">Articles</div>
                        </div>
                        <div style="flex: 1; text-align: center; border-left: 1px solid var(--light-gray); padding: 0.5rem 0;">
                            <div style="font-size: 1.5rem; font-weight: 600;"><?php echo $profile['follower_count'] ?? 0; ?></div>
                            <div style="color: #666; font-size: 0.875rem;">Abonnés</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Tabs -->
                <div class="forum-container">
                    <div class="profile-tabs" style="display: flex; border-bottom: 1px solid var(--light-gray);">
                        <a href="#threads" class="profile-tab active" style="flex: 1; padding: 1rem; text-align: center; border-bottom: 2px solid var(--bright-blue); font-weight: 600;">
                            Discussions
                        </a>
                        <a href="#articles" class="profile-tab" style="flex: 1; padding: 1rem; text-align: center; border-bottom: 2px solid transparent;">
                            Articles
                        </a>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Threads Tab -->
                        <div id="threads-content" class="tab-pane active" style="padding: 1.5rem;">
                            <?php if (empty($recent_threads)): ?>
                                <p style="text-align: center; color: #666;">Cet utilisateur n'a pas encore créé de discussions.</p>
                            <?php else: ?>
                                <div class="thread-list">
                                    <?php foreach ($recent_threads as $thread): ?>
                                        <div class="thread-item" style="padding: 1rem 0; border-bottom: 1px solid var(--light-gray);">
                                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                                <div>
                                                    <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem;">
                                                        <a href="thread.php?id=<?php echo $thread['id']; ?>"><?php echo htmlspecialchars($thread['title']); ?></a>
                                                    </h3>
                                                    <div style="color: #666; font-size: 0.875rem;">
                                                        <?php if (isset($thread['category'])): ?>
                                                            <span>dans <a href="category.php?id=<?php echo $thread['category']['id']; ?>"><?php echo htmlspecialchars($thread['category']['name']); ?></a></span> • 
                                                        <?php endif; ?>
                                                        <span><?php echo date('d/m/Y à H:i', strtotime($thread['created_at'])); ?></span>
                                                    </div>
                                                </div>
                                                <div style="display: flex; gap: 1rem; color: #666; font-size: 0.875rem;">
                                                    <div><i class="fas fa-comment"></i> <?php echo $thread['reply_count'] ?? 0; ?></div>
                                                    <div><i class="fas fa-eye"></i> <?php echo $thread['view_count'] ?? 0; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (count($recent_threads) >= 5): ?>
                                    <div style="text-align: center; margin-top: 1.5rem;">
                                        <a href="user-threads.php?id=<?php echo $user_id; ?>" class="btn btn-outline">
                                            Voir toutes les discussions
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Articles Tab -->
                        <div id="articles-content" class="tab-pane" style="display: none; padding: 1.5rem;">
                            <?php if (empty($recent_articles)): ?>
                                <p style="text-align: center; color: #666;">Cet utilisateur n'a pas encore publié d'articles.</p>
                            <?php else: ?>
                                <div class="article-list">
                                    <?php foreach ($recent_articles as $article): ?>
                                        <div class="article-item" style="padding: 1rem 0; border-bottom: 1px solid var(--light-gray);">
                                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                                <div>
                                                    <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem;">
                                                        <a href="article.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a>
                                                    </h3>
                                                    <div style="color: #666; font-size: 0.875rem;">
                                                        <span><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
                                                        
                                                        <?php if (isset($article['tags']) && !empty($article['tags'])): ?>
                                                            • 
                                                            <?php foreach ($article['tags'] as $index => $tag): ?>
                                                                <a href="articles.php?tag=<?php echo urlencode($tag['name']); ?>" style="color: var(--bright-blue);">
                                                                    #<?php echo htmlspecialchars($tag['name']); ?>
                                                                </a>
                                                                <?php if ($index < count($article['tags']) - 1) echo ', '; ?>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div style="display: flex; gap: 1rem; color: #666; font-size: 0.875rem;">
                                                    <div><i class="far fa-comment"></i> <?php echo $article['comment_count'] ?? 0; ?></div>
                                                    <div><i class="far fa-eye"></i> <?php echo $article['view_count'] ?? 0; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (count($recent_articles) >= 5): ?>
                                    <div style="text-align: center; margin-top: 1.5rem;">
                                        <a href="user-articles.php?id=<?php echo $user_id; ?>" class="btn btn-outline">
                                            Voir tous les articles
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar">
                <!-- User Info Card -->
                <div class="widget">
                    <div class="widget-header" style="background-color: var(--dark-blue);">
                        <h3>À propos</h3>
                    </div>
                    <div class="widget-content">
                        <ul style="list-style: none;">
                            <?php if (isset($profile['speciality']) && !empty($profile['speciality'])): ?>
                                <li style="margin-bottom: 0.75rem;">
                                    <strong><i class="fas fa-laptop-code"></i> Spécialité:</strong> 
                                    <?php echo htmlspecialchars($profile['speciality']); ?>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($profile['interests']) && !empty($profile['interests'])): ?>
                                <li style="margin-bottom: 0.75rem;">
                                    <strong><i class="fas fa-star"></i> Intérêts:</strong> 
                                    <?php echo htmlspecialchars($profile['interests']); ?>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($profile['github']) && !empty($profile['github'])): ?>
                                <li style="margin-bottom: 0.75rem;">
                                    <strong><i class="fab fa-github"></i> GitHub:</strong> 
                                    <a href="https://github.com/<?php echo htmlspecialchars($profile['github']); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo htmlspecialchars($profile['github']); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($profile['twitter']) && !empty($profile['twitter'])): ?>
                                <li style="margin-bottom: 0.75rem;">
                                    <strong><i class="fab fa-twitter"></i> Twitter:</strong> 
                                    <a href="https://twitter.com/<?php echo htmlspecialchars($profile['twitter']); ?>" target="_blank" rel="noopener noreferrer">
                                        @<?php echo htmlspecialchars($profile['twitter']); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($profile['linkedin']) && !empty($profile['linkedin'])): ?>
                                <li>
                                    <strong><i class="fab fa-linkedin"></i> LinkedIn:</strong> 
                                    <a href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank" rel="noopener noreferrer">Profil</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Badges/Achievements -->
                <?php if (isset($profile['badges']) && !empty($profile['badges'])): ?>
                    <div class="widget">
                        <div class="widget-header" style="background-color: var(--purple);">
                            <h3>Badges</h3>
                        </div>
                        <div class="widget-content">
                            <div class="badges-list" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <?php foreach ($profile['badges'] as $badge): ?>
                                    <div class="badge-item" title="<?php echo htmlspecialchars($badge['description']); ?>" style="background-color: <?php echo $badge['color'] ?? '#f0f0f0'; ?>; color: <?php echo $badge['text_color'] ?? '#333'; ?>; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                        <?php if (!empty($badge['icon'])): ?>
                                            <i class="<?php echo htmlspecialchars($badge['icon']); ?>"></i> 
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($badge['name']); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Report User (only if logged in and not viewing own profile) -->
                <?php if ($api->isLoggedIn() && $user_id != $_SESSION['user']['id']): ?>
                    <div class="widget">
                        <div class="widget-content" style="text-align: center;">
                            <button type="button" class="btn btn-outline" style="color: var(--accent-red); border-color: var(--accent-red);" onclick="showReportModal()">
                                <i class="fas fa-flag"></i> Signaler cet utilisateur
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</main>

<!-- Report Modal -->
<div id="report-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background-color: var(--white); border-radius: var(--border-radius); max-width: 500px; width: 100%; padding: 2rem;">
        <h3 style="margin-top: 0;">Signaler l'utilisateur</h3>
        <p>Veuillez indiquer la raison de votre signalement concernant <strong><?php echo htmlspecialchars($profile['username']); ?></strong>.</p>
        
        <form id="report-form" action="report.php" method="post">
            <input type="hidden" name="target_type" value="user">
            <input type="hidden" name="target_id" value="<?php echo $user_id; ?>">
            
            <div class="form-group">
                <label for="reason" class="form-label">Raison</label>
                <select id="reason" name="reason" class="form-control" required>
                    <option value="">Sélectionnez une raison</option>
                    <option value="spam">Spam / Publicité</option>
                    <option value="inappropriate">Contenu inapproprié</option>
                    <option value="harassment">Harcèlement</option>
                    <option value="fake">Faux compte</option>
                    <option value="other">Autre raison</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="details" class="form-label">Détails</label>
                <textarea id="details" name="details" class="form-control" rows="3" placeholder="Veuillez fournir des détails supplémentaires si nécessaire..."></textarea>
            </div>
            
            <div class="form-group" style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeReportModal()">Annuler</button>
                <button type="submit" class="btn btn-accent">Signaler</button>
            </div>
        </form>
    </div>
</div>

<script>
// Tab switching
document.querySelectorAll('.profile-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs and content
        document.querySelectorAll('.profile-tab').forEach(t => {
            t.classList.remove('active');
            t.style.borderBottom = '2px solid transparent';
        });
        document.querySelectorAll('.tab-pane').forEach(p => {
            p.classList.remove('active');
            p.style.display = 'none';
        });
        
        // Add active class to clicked tab
        this.classList.add('active');
        this.style.borderBottom = '2px solid var(--bright-blue)';
        
        // Show corresponding content
        const tabId = this.getAttribute('href').substring(1);
        const content = document.getElementById(tabId + '-content');
        content.classList.add('active');
        content.style.display = 'block';
    });
});

// Report modal functions
function showReportModal() {
    document.getElementById('report-modal').style.display = 'flex';
}

function closeReportModal() {
    document.getElementById('report-modal').style.display = 'none';
}

// Close modal if clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('report-modal');
    if (event.target === modal) {
        closeReportModal();
    }
});
</script>

<?php include 'footer.php'; ?>