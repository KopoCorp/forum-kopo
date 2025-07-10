
<main class="container" style="max-width: 500px; margin: 2rem auto;">
    <section class="forum-container">
        <div class="forum-header">
            <h1 style="color: var(--white); margin: 0; text-align: center;">Inscription</h1>
        </div>
        <div style="padding: 2rem;">
            <?php if ($success): ?>
                <div class="notification notification-success">
                    <i class="fas fa-check-circle"></i>
                    Votre compte a été créé avec succès! Vous pouvez maintenant <a href="index.php?route=login">vous connecter</a>.
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="notification notification-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
                <form method="post" action="index.php?route=register" data-validate>
                    <div class="form-group">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                        <div class="form-text">Ce nom sera visible par tous les utilisateurs.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                        <div class="form-text">Nous ne partagerons jamais votre email.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required data-check-strength>
                        <div class="form-text">Le mot de passe doit contenir au moins 8 caractères, des majuscules, minuscules, chiffres et caractères spéciaux.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                            <label for="terms">J'accepte les <a href="terms.php">conditions d'utilisation</a> et la <a href="privacy.php">politique de confidentialité</a>.</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Créer un compte</button>
                    </div>
                </form>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <p>Vous avez déjà un compte? <a href="index.php?route=login">Connectez-vous</a></p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

