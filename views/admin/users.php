<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="../index.php" style="color: #999;">Accueil</a> &raquo;
            <a href="../index.php?route=admin" style="color: #999;">Administration</a> &raquo;
            Utilisateurs
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Gestion des utilisateurs</h1>
        <p style="color: #ccc; max-width: 700px;">Ajouter, éditer ou supprimer les comptes utilisateurs.</p>
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
                <h2 style="color:var(--white);margin:0;">Utilisateurs enregistrés</h2>
            </div>
            <div style="padding:2rem;">
                <?php if (!empty($users)): ?>
                <table style="width:100%;">
                    <thead>
                        <tr><th>ID</th><th>Pseudo</th><th>Email</th><th>Actif</th><th style="text-align:center;">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <?php if (isset($_GET['edit']) && intval($_GET['edit']) === $u['id']): ?>
                                <tr>
                                    <td colspan="5">
                                        <form method="post" action="users.php?edit=<?php echo $u['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <div class="form-group">
                                                <label class="form-label">Pseudo</label>
                                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($u['username']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u['email']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label"><input type="checkbox" name="is_active" <?php echo !empty($u['is_active']) ? 'checked' : ''; ?>> Actif</label>
                                            </div>
                                            <div style="margin-top:1rem;">
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                <a href="users.php" class="btn btn-outline">Annuler</a>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo !empty($u['is_active']) ? 'Oui' : 'Non'; ?></td>
                                    <td style="text-align:center;">
                                        <a href="users.php?edit=<?php echo $u['id']; ?>" class="btn btn-outline btn-sm">Éditer</a>
                                        <form method="post" action="users.php" style="display:inline-block;">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Supprimer cet utilisateur ?');" style="color:var(--accent-red);border-color:var(--accent-red);">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>Aucun utilisateur trouvé.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
