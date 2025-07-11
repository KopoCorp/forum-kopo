<!-- Page Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="<?php echo BASE_URL; ?>index.php" style="color: #999;">Accueil</a> &raquo; Administration
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Tableau de bord</h1>
        <p style="color: #ccc; max-width: 700px;">Outils de modération et statistiques du forum.</p>
    </div>
</div>

<!-- Main Content -->
<main class="main-content section">
    <div class="container">
        <?php if (!empty($stats)): ?>
        <div class="grid grid-4" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['user_count']; ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['article_count']; ?></div>
                <div class="stat-label">Articles</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['thread_count']; ?></div>
                <div class="stat-label">Sujets</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['reply_count']; ?></div>
                <div class="stat-label">Réponses</div>
            </div>
        </div>
        <?php endif; ?>
        <div class="admin-links" style="margin-bottom:2rem;">
            <a href="../admin/users.php" class="btn btn-secondary">Gérer les utilisateurs</a>
            <a href="../admin/articles.php" class="btn btn-secondary">Gérer les articles</a>
            <a href="../admin/threads.php" class="btn btn-secondary">Gérer les sujets</a>
            <a href="../admin/categories.php" class="btn btn-secondary">Gérer les catégories</a>
        </div>

        <?php if (!empty($alert)): ?>
        <div class="security-alert" style="background:#fff4f4;border-left:4px solid var(--accent-red);padding:1rem;margin-bottom:2rem;">
            <strong>Dernière alerte sécurité&nbsp;:</strong>
            <a href="<?php echo htmlspecialchars($alert['link']); ?>" target="_blank">
                <?php echo htmlspecialchars($alert['title']); ?>
            </a>
            <?php if (!empty($alert['published'])): ?>
                <span style="color:#666;">(<?php echo date('d/m/Y', strtotime($alert['published'])); ?>)</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <h2 style="margin-top:2rem;">Utilisateurs récents</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>Pseudo</th><th>Email</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 style="margin-top:2rem;">Articles récents</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>Titre</th><th>Auteur</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $a): ?>
                    <tr>
                        <td><?php echo $a['id']; ?></td>
                        <td><a href="<?php echo BASE_URL; ?>index.php?route=article&amp;id=<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['title']); ?></a></td>
                        <td><?php echo get_username($a); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 style="margin-top:2rem;">Derniers sujets</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>Titre</th><th>Auteur</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($threads as $t): ?>
                    <tr>
                        <td><?php echo $t['id']; ?></td>
                        <td><a href="thread.php?id=<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['title']); ?></a></td>
                        <td><?php echo get_username($t); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 style="margin-top:2rem;">Commentaires récents</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>Auteur</th><th>Contenu</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $c): ?>
                    <tr>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo get_username($c); ?></td>
                        <td><?php echo substr(strip_tags($c['content'] ?? ''),0,80); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
