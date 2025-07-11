<!-- Page Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; Membres
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Membres de la communauté</h1>
        <p style="color: #ccc; max-width: 700px;">Découvrez les membres actifs de notre communauté Kopo.</p>
    </div>
</div>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <!-- Search and Filters -->
        <div class="members-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <form action="index.php?route=members" method="get" style="flex-grow: 1; max-width: 500px;">
                <div style="display: flex;">
                    <input type="text" name="search" placeholder="Rechercher un membre..." class="form-control" value="<?php echo htmlspecialchars($search); ?>" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <button type="submit" class="btn btn-primary" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <div class="sort-options">
                <span style="margin-right: 0.5rem;">Trier par:</span>
                <div class="btn-group" style="display: inline-flex;">
                    <a href="?sort=newest<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo $sort === 'newest' ? 'btn-primary' : 'btn-outline'; ?>">Récent</a>
                    <a href="?sort=activity<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo $sort === 'activity' ? 'btn-primary' : 'btn-outline'; ?>">Activité</a>
                    <a href="?sort=a-z<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo $sort === 'a-z' ? 'btn-primary' : 'btn-outline'; ?>">A-Z</a>
                </div>
            </div>
        </div>
        
        <!-- Members List -->
        <?php if (!empty($members_data)): ?>
            <div class="grid grid-4" style="gap: 1.5rem;">
                <?php foreach ($members_data as $member): ?>
                    <div class="member-card" style="background-color: var(--white); border-radius: var(--border-radius); overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <div style="padding: 1.5rem; text-align: center; border-bottom: 1px solid var(--light-gray);">
                            <div class="member-avatar" style="position: relative; display: inline-block; margin-bottom: 1rem;">
                                <img src="<?php echo isset($member['avatar_url']) && !empty($member['avatar_url']) ? htmlspecialchars($member['avatar_url']) : DEFAULT_AVATAR_URL; ?>" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                                
                                <?php if (isset($member['is_online']) && $member['is_online']): ?>
                                    <div class="online-indicator" style="position: absolute; bottom: 0; right: 0; width: 16px; height: 16px; background-color: #28a745; border-radius: 50%; border: 2px solid white;"></div>
                                <?php endif; ?>
                                
                                <?php if (isset($member['is_verified']) && $member['is_verified']): ?>
                                    <div class="verified-badge" style="position: absolute; top: 0; right: 0; background-color: var(--bright-blue); color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h3 style="margin: 0; font-size: 1.2rem;">
                                <a href="profile.php?id=<?php echo $member['id']; ?>" style="color: var(--near-black);">
                                    <?php echo htmlspecialchars($member['username']); ?>
                                </a>
                            </h3>
                            
                            <?php if (isset($member['role']) && !empty($member['role'])): ?>
                                <div class="member-role" style="margin-top: 0.25rem;">
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.7rem; background-color: <?php echo $member['role'] === 'admin' ? 'var(--accent-red)' : 'var(--purple)'; ?>; color: white; border-radius: 10px;">
                                        <?php echo htmlspecialchars(ucfirst($member['role'])); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="padding: 1rem 1.5rem;">
                            <div class="member-stats" style="display: flex; justify-content: space-around; text-align: center; margin-bottom: 1rem;">
                                <div class="stat-item">
                                    <div style="font-weight: 600;"><?php echo $member['post_count'] ?? 0; ?></div>
                                    <div style="font-size: 0.75rem; color: #666;">Posts</div>
                                </div>
                                
                                
                                <div class="stat-item">
                                    <div style="font-weight: 600;"><?php echo isset($member['created_at']) ? date('m/Y', strtotime($member['created_at'])) : 'N/A'; ?></div>
                                    <div style="font-size: 0.75rem; color: #666;">Inscription</div>
                                </div>
                            </div>
                            
                            <?php if ($api->isLoggedIn() && $_SESSION['user']['id'] === $member['id']): ?>
                                <div class="member-actions">
                                    <a href="profile.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline" style="width: 100%;">
                                        <i class="fas fa-user-edit"></i> C'est vous
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination" style="margin-top: 2rem;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                            <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="forum-container" style="text-align: center; padding: 3rem 1.5rem;">
                <?php if (!empty($search)): ?>
                    <div style="margin-bottom: 1.5rem; color: #666;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h3>Aucun membre ne correspond à votre recherche</h3>
                        <p>Essayez avec d'autres termes ou parcourez tous les membres.</p>
                    </div>
                    <a href="index.php?route=members" class="btn btn-primary">Voir tous les membres</a>
                <?php else: ?>
                    <div style="margin-bottom: 1.5rem; color: #666;">
                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h3>Aucun membre trouvé</h3>
                        <p>Il n'y a actuellement aucun membre inscrit sur le forum.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Join Community CTA -->
        <?php if (!$api->isLoggedIn()): ?>
            <div class="cta-section" style="margin-top: 3rem; padding: 2.5rem; background-color: var(--purple); color: var(--white); border-radius: var(--border-radius); text-align: center;">
                <h2 style="color: var(--white); font-size: 1.75rem; margin-bottom: 1rem;">Rejoignez notre communauté</h2>
                <p style="max-width: 700px; margin: 0 auto 1.5rem;">Créez un compte pour interagir avec d'autres membres, participer aux discussions et partager vos connaissances.</p>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="index.php?route=register" class="btn btn-primary" style="min-width: 150px;">Créer un compte</a>
                    <a href="index.php?route=login" class="btn btn-outline" style="background-color: transparent; border-color: var(--white); color: var(--white); min-width: 150px;">Connexion</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* Additional styles for member cards on hover */
.member-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.member-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
</style>


