<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="../index.php" style="color: #999;">Accueil</a> &raquo;
            <a href="../index.php?route=admin" style="color: #999;">Administration</a> &raquo;
            Articles
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Gestion des articles</h1>
        <p style="color: #ccc; max-width: 700px;">Modifier ou supprimer les articles publiés.</p>
    </div>
</div>

<main class="main-content section">
    <div class="container" style="max-width: 900px;">
        <?php if (!empty($success)): ?>
            <div class="notification notification-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="notification notification-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="forum-container">
            <div class="forum-header">
                <h2 style="color:var(--white);margin:0;">Articles</h2>
            </div>
            <div style="padding:2rem;">
                <?php if (!empty($articles)): ?>
                <table style="width:100%;">
                    <thead>
                        <tr><th>ID</th><th>Titre</th><th>Auteur</th><th>Publié</th><th style="text-align:center;">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $a): ?>
                            <?php if (isset($_GET['edit']) && intval($_GET['edit']) === $a['id']): ?>
                                <tr>
                                    <td colspan="5">
                                        <form method="post" action="articles.php?edit=<?php echo $a['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                            <div class="form-group">
                                                <label class="form-label">Titre</label>
                                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($a['title']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Contenu</label>
                                                <textarea name="content" class="form-control" rows="6" required><?php echo htmlspecialchars($a['content']); ?></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label"><input type="checkbox" name="is_pub" <?php echo !empty($a['is_pub']) ? 'checked' : ''; ?>> Publié</label>
                                            </div>
                                            <div style="margin-top:1rem;">
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                <a href="articles.php" class="btn btn-outline">Annuler</a>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td><?php echo $a['id']; ?></td>
                                    <td><?php echo htmlspecialchars($a['title']); ?></td>
                                    <td><?php echo get_username($a); ?></td>
                                    <td><?php echo !empty($a['is_pub']) ? 'Oui' : 'Non'; ?></td>
                                    <td style="text-align:center;">
                                        <a href="articles.php?edit=<?php echo $a['id']; ?>" class="btn btn-outline btn-sm">Éditer</a>
                                        <form method="post" action="articles.php" style="display:inline-block;">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Supprimer cet article ?');" style="color:var(--accent-red);border-color:var(--accent-red);">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>Aucun article disponible.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
