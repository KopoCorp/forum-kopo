<?php
$page_title = "Recherche";
$page_description = "Rechercher du contenu sur le forum Kopo";
require_once 'header.php';

// Get search parameters
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$skip = ($page - 1) * $limit;

// Determine which types of content to search
$search_articles = $type === 'all' || $type === 'articles';
$search_threads = $type === 'all' || $type === 'threads';
$search_users = $type === 'all' || $type === 'users';

// Search results
$articles = [];
$threads = [];
$users = [];
$total_results = 0;
$total_pages = 1;

// Execute search if query provided
if (!empty($q)) {
    try {
        // Build search query
        $search_query = '/search?q=' . urlencode($q) . '&skip=' . $skip . '&limit=' . $limit;
        
        if ($type !== 'all') {
            $search_query .= '&type=' . urlencode($type);
        }
        
        // Execute search
        $search_results = $api->request($search_query);
        
        // Extract results based on type
        if ($search_articles && isset($search_results['articles'])) {
            $articles = $search_results['articles'];
        }
        
        if ($search_threads && isset($search_results['threads'])) {
            $threads = $search_results['threads'];
        }
        
        if ($search_users && isset($search_results['users'])) {
            $users = $search_results['users'];
        }
        
        // Get total count for pagination
        $total_count_query = '/search/count?q=' . urlencode($q);
        if ($type !== 'all') {
            $total_count_query .= '&type=' . urlencode($type);
        }
        
        $count_result = $api->request($total_count_query);
        $total_results = $count_result['count'] ?? 0;
        $total_pages = ceil($total_results / $limit);
        
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "Erreur lors de la recherche: " . $e->getMessage();
        $_SESSION['flash_type'] = "error";
    }
}

// Function to highlight search terms
function highlightSearchTerms($text, $search) {
    if (empty($search)) return $text;
    
    $terms = explode(' ', $search);
    foreach ($terms as $term) {
        if (strlen($term) >= 3) { // Only highlight terms with 3+ characters
            $text = preg_replace('/\b(' . preg_quote($term, '/') . ')\b/i', '<mark>$1</mark>', $text);
        }
    }
    
    return $text;
}
?>

<!-- Page Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; Recherche
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Recherche</h1>
        <p style="color: #ccc; max-width: 700px;">Recherchez des articles, discussions et membres</p>
    </div>
</div>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <!-- Search Form -->
        <div class="search-form-container" style="margin-bottom: 2.5rem;">
            <form action="search.php" method="get" class="search-form">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                    <div style="flex: 1; min-width: 250px;">
                        <div class="form-group" style="margin: 0;">
                            <div class="input-group" style="display: flex;">
                                <div style="background-color: var(--light-gray); padding: 0.75rem; border-top-left-radius: var(--border-radius); border-bottom-left-radius: var(--border-radius); border: 1px solid var(--light-gray); border-right: none;">
                                    <i class="fas fa-search"></i>
                                </div>
                                <input type="text" name="q" placeholder="Rechercher..." class="form-control" value="<?php echo htmlspecialchars($q); ?>" style="border-top-left-radius: 0; border-bottom-left-radius: 0; flex: 1;">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <select name="type" class="form-control">
                            <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>Tout le contenu</option>
                            <option value="articles" <?php echo $type === 'articles' ? 'selected' : ''; ?>>Articles</option>
                            <option value="threads" <?php echo $type === 'threads' ? 'selected' : ''; ?>>Discussions</option>
                            <option value="users" <?php echo $type === 'users' ? 'selected' : ''; ?>>Membres</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (!empty($q)): ?>
            <!-- Search Results -->
            <div class="search-results">
                <div class="search-stats" style="margin-bottom: 1.5rem; color: #666;">
                    <?php if ($total_results > 0): ?>
                        <p>
                            <strong><?php echo $total_results; ?></strong> résultat(s) pour 
                            <strong>"<?php echo htmlspecialchars($q); ?>"</strong>
                            <?php if ($type !== 'all'): ?>
                                dans <strong><?php echo $type === 'articles' ? 'Articles' : ($type === 'threads' ? 'Discussions' : 'Membres'); ?></strong>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p>Aucun résultat trouvé pour <strong>"<?php echo htmlspecialchars($q); ?>"</strong></p>
                    <?php endif; ?>
                </div>
                
                <!-- Display Articles -->
                <?php if ($search_articles && !empty($articles)): ?>
                    <div class="search-section">
                        <h2>Articles</h2>
                        
                        <div class="search-list">
                            <?php foreach ($articles as $article): ?>
                                <div class="search-item" style="padding: 1.5rem; margin-bottom: 1rem; background-color: var(--white); border-radius: var(--border-radius); box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                    <h3 style="margin-top: 0; margin-bottom: 0.5rem;">
                                        <a href="article.php?id=<?php echo $article['id']; ?>">
                                            <?php echo highlightSearchTerms(htmlspecialchars($article['title']), $q); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="search-meta" style="margin-bottom: 1rem; font-size: 0.875rem; color: #666;">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['username'] ?? 'Auteur'); ?></span> &bull;
                                        <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span> &bull;
                                        <span><i class="far fa-eye"></i> <?php echo $article['view_count'] ?? 0; ?> vues</span>
                                        
                                        <?php if (isset($article['tags']) && !empty($article['tags'])): ?>
                                            &bull;
                                            <span>
                                                <?php foreach ($article['tags'] as $index => $tag): ?>
                                                    <a href="articles.php?tag=<?php echo urlencode($tag['name']); ?>" class="article-tag" style="font-size: 0.75rem; background-color: var(--light-gray); padding: 0.1rem 0.5rem; border-radius: 10px;"><?php echo htmlspecialchars($tag['name']); ?></a>
                                                    <?php if ($index < count($article['tags']) - 1) echo ' '; ?>
                                                <?php endforeach; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="search-excerpt">
                                        <?php
                                            $excerpt = strip_tags($article['content']);
                                            $excerpt = substr($excerpt, 0, 250) . (strlen($excerpt) > 250 ? '...' : '');
                                            echo highlightSearchTerms($excerpt, $q);
                                        ?>
                                    </div>
                                    
                                    <div class="search-actions" style="margin-top: 1rem; text-align: right;">
                                        <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline">Lire l'article</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($type !== 'articles' && count($articles) >= 5): ?>
                            <div style="text-align: center; margin-top: 1.5rem;">
                                <a href="search.php?q=<?php echo urlencode($q); ?>&type=articles" class="btn btn-outline">
                                    Voir tous les articles correspondant
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Display Threads -->
                <?php if ($search_threads && !empty($threads)): ?>
                    <div class="search-section" style="margin-top: 2.5rem;">
                        <h2>Discussions</h2>
                        
                        <div class="search-list">
                            <?php foreach ($threads as $thread): ?>
                                <div class="search-item" style="padding: 1.5rem; margin-bottom: 1rem; background-color: var(--white); border-radius: var(--border-radius); box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                    <h3 style="margin-top: 0; margin-bottom: 0.5rem;">
                                        <a href="thread.php?id=<?php echo $thread['id']; ?>">
                                            <?php echo highlightSearchTerms(htmlspecialchars($thread['title']), $q); ?>
                                        </a>
                                    </h3>
                                    
                                    <div class="search-meta" style="margin-bottom: 1rem; font-size: 0.875rem; color: #666;">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($thread['username'] ?? 'Utilisateur'); ?></span> &bull;
                                        <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($thread['created_at'])); ?></span> &bull;
                                        <span><i class="far fa-comment"></i> <?php echo $thread['reply_count'] ?? 0; ?> réponses</span> &bull;
                                        <span><i class="far fa-eye"></i> <?php echo $thread['view_count'] ?? 0; ?> vues</span>
                                        
                                        <?php if (isset($thread['category']) && !empty($thread['category'])): ?>
                                            &bull;
                                            <span>
                                                <a href="category.php?id=<?php echo $thread['category']['id']; ?>" style="color: var(--purple);">
                                                    <?php echo htmlspecialchars($thread['category']['name']); ?>
                                                </a>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="search-excerpt">
                                        <?php
                                            $excerpt = strip_tags($thread['content']);
                                            $excerpt = substr($excerpt, 0, 250) . (strlen($excerpt) > 250 ? '...' : '');
                                            echo highlightSearchTerms($excerpt, $q);
                                        ?>
                                    </div>
                                    
                                    <div class="search-actions" style="margin-top: 1rem; text-align: right;">
                                        <a href="thread.php?id=<?php echo $thread['id']; ?>" class="btn btn-sm btn-outline">Voir la discussion</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($type !== 'threads' && count($threads) >= 5): ?>
                            <div style="text-align: center; margin-top: 1.5rem;">
                                <a href="search.php?q=<?php echo urlencode($q); ?>&type=threads" class="btn btn-outline">
                                    Voir toutes les discussions correspondantes
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Display Users -->
                <?php if ($search_users && !empty($users)): ?>
                    <div class="search-section" style="margin-top: 2.5rem;">
                        <h2>Membres</h2>
                        
                        <div class="search-list grid grid-3" style="gap: 1rem;">
                            <?php foreach ($users as $user): ?>
                                <div class="search-item" style="padding: 1.5rem; background-color: var(--white); border-radius: var(--border-radius); box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center;">
                                    <img src="<?php echo isset($user['avatar_url']) && !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url']) : 'assets/images/default-avatar.png'; ?>" alt="Avatar" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 1rem;">
                                    
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 0.25rem;">
                                            <a href="profile.php?id=<?php echo $user['id']; ?>">
                                                <?php echo highlightSearchTerms(htmlspecialchars($user['username']), $q); ?>
                                            </a>
                                        </h4>
                                        
                                        <div style="font-size: 0.875rem; color: #666;">
                                            <span>Membre depuis <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                                            
                                            <?php if (isset($user['post_count'])): ?>
                                                &bull; <span><?php echo $user['post_count']; ?> contributions</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline" style="white-space: nowrap;">
                                        Voir profil
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($type !== 'users' && count($users) >= 6): ?>
                            <div style="text-align: center; margin-top: 1.5rem;">
                                <a href="search.php?q=<?php echo urlencode($q); ?>&type=users" class="btn btn-outline">
                                    Voir tous les membres correspondants
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- No Results Message -->
                <?php if ($total_results === 0): ?>
                    <div class="no-results" style="text-align: center; padding: 3rem 0; background-color: var(--white); border-radius: var(--border-radius); margin-bottom: 2rem;">
                        <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>Aucun résultat trouvé</h3>
                        <p style="max-width: 500px; margin: 0 auto 1.5rem; color: #666;">Aucun contenu ne correspond à votre recherche. Essayez d'utiliser des termes différents ou vérifiez l'orthographe.</p>
                    </div>
                    
                    <div class="search-tips">
                        <h3>Conseils de recherche</h3>
                        <ul>
                            <li>Vérifiez l'orthographe des mots-clés</li>
                            <li>Utilisez des mots plus généraux</li>
                            <li>Essayez moins de mots-clés</li>
                            <li>Explorez les catégories et les tags pour trouver du contenu similaire</li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="margin-top: 2rem;">
                        <?php if ($page > 1): ?>
                            <a href="?q=<?php echo urlencode($q); ?>&type=<?php echo $type; ?>&page=<?php echo $page - 1; ?>">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        $start_page = max(1, $end_page - 4);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?q=<?php echo urlencode($q); ?>&type=<?php echo $type; ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?q=<?php echo urlencode($q); ?>&type=<?php echo $type; ?>&page=<?php echo $page + 1; ?>">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Search Initial State -->
            <div class="search-initial" style="text-align: center; padding: 3rem 0;">
                <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h2>Rechercher sur Kopo Forum</h2>
                <p style="max-width: 600px; margin: 0 auto 2rem; color: #666;">Utilisez la barre de recherche ci-dessus pour trouver des articles, discussions, et membres.</p>
                
                <div class="popular-searches" style="margin-top: 2rem;">
                    <h3>Recherches populaires</h3>
                    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; max-width: 600px; margin: 0 auto;">
                        <a href="search.php?q=cybersécurité" class="btn btn-sm btn-outline">cybersécurité</a>
                        <a href="search.php?q=javascript" class="btn btn-sm btn-outline">javascript</a>
                        <a href="search.php?q=python" class="btn btn-sm btn-outline">python</a>
                        <a href="search.php?q=linux" class="btn btn-sm btn-outline">linux</a>
                        <a href="search.php?q=docker" class="btn btn-sm btn-outline">docker</a>
                        <a href="search.php?q=pentesting" class="btn btn-sm btn-outline">pentesting</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
mark {
    background-color: rgba(87, 184, 255, 0.2);
    color: inherit;
    padding: 0 2px;
    border-radius: 2px;
}
</style>

<?php include 'footer.php'; ?>