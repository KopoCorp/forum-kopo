<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo;
            <a href="index.php?route=admin" style="color: #999;">Administration</a> &raquo;
            Catégories
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Gestion des catégories</h1>
        <p style="color: #ccc; max-width: 700px;">Ajouter, éditer ou supprimer les catégories du forum.</p>
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

        <div class="forum-container" style="margin-bottom:2rem;">
            <div class="forum-header">
                <h2 style="color:var(--white);margin:0;">Ajouter une catégorie</h2>
            </div>
            <div style="padding:2rem;">
                <form method="post" action="categories.php">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>

        <div class="forum-container">
            <div class="forum-header">
                <h2 style="color:var(--white);margin:0;">Catégories existantes</h2>
            </div>
            <div style="padding:2rem;">
                <?php if (!empty($categories)): ?>
                <table style="width:100%;">
                    <thead>
                        <tr><th>Nom</th><th>Description</th><th style="text-align:center;">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                            <?php if (isset($_GET['edit']) && intval($_GET['edit']) === $c['id']): ?>
                                <tr>
                                    <td colspan="3">
                                        <form method="post" action="categories.php?edit=<?php echo $c['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <div class="form-group">
                                                <label class="form-label">Nom</label>
                                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($c['name']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($c['description'] ?? ''); ?></textarea>
                                            </div>
                                            <div style="margin-top:1rem;">
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                <a href="categories.php" class="btn btn-outline">Annuler</a>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['name']); ?></td>
                                    <td><?php echo htmlspecialchars($c['description'] ?? ''); ?></td>
                                    <td style="text-align:center;">
                                        <a href="categories.php?edit=<?php echo $c['id']; ?>" class="btn btn-outline btn-sm">Éditer</a>
                                        <form method="post" action="categories.php" style="display:inline-block;">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Supprimer cette catégorie ?');" style="color:var(--accent-red);border-color:var(--accent-red);">
                                                Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>Aucune catégorie disponible.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
