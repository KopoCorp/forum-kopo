
<main class="container" style="max-width: 500px; margin: 2rem auto;">
    <section class="forum-container">
        <div class="forum-header">
            <h1 style="color: var(--white); margin: 0; text-align: center;">Connexion</h1>
        </div>
        <div style="padding: 2rem;">
            <?php if (!empty($error)): ?>
                <div class="notification notification-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="index.php?route=login" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="form-check">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="forgot-password.php" style="font-size: 0.875rem;">Mot de passe oublié?</a>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Connexion</button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 1rem;">
                <p>Vous n'avez pas de compte? <a href="index.php?route=register">Inscrivez-vous</a></p>
            </div>
        </div>
    </section>
</main>

