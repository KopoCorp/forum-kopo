
<!-- Page Header -->
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo; Contact
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Contactez-nous</h1>
        <p style="color: #ccc; max-width: 700px;">Une question, suggestion ou signalement? N'hésitez pas à nous écrire.</p>
    </div>
</div>

<!-- Main Content -->
<main class="main-content section">
    <div class="container" style="max-width: 900px;">
        <div class="grid grid-2" style="gap: 2rem;">
            <!-- Contact Form -->
            <div class="contact-form-container">
                <?php if ($success): ?>
                    <div class="notification notification-success">
                        <i class="fas fa-check-circle"></i>
                        Votre message a été envoyé avec succès! Nous vous répondrons dans les meilleurs délais.
                    </div>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
                    </div>
                <?php else: ?>
                    <div class="forum-container">
                        <div class="forum-header">
                            <h2 style="color: var(--white); margin: 0; font-size: 1.5rem;">Formulaire de contact</h2>
                        </div>
                        
                        <div style="padding: 2rem;">
                            <?php if (!empty($error)): ?>
                                <div class="notification notification-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" action="index.php?route=contact" data-validate>
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nom</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ($api->isLoggedIn() ? htmlspecialchars($_SESSION['user']['username']) : ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ($api->isLoggedIn() && isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject" class="form-label">Sujet</label>
                                    <select id="subject" name="subject" class="form-control" required>
                                        <option value="" disabled <?php echo !isset($subject) ? 'selected' : ''; ?>>Sélectionnez un sujet</option>
                                        <option value="question" <?php echo isset($subject) && $subject === 'question' ? 'selected' : ''; ?>>Question générale</option>
                                        <option value="technical" <?php echo isset($subject) && $subject === 'technical' ? 'selected' : ''; ?>>Problème technique</option>
                                        <option value="feedback" <?php echo isset($subject) && $subject === 'feedback' ? 'selected' : ''; ?>>Suggestion / Commentaire</option>
                                        <option value="account" <?php echo isset($subject) && $subject === 'account' ? 'selected' : ''; ?>>Problème de compte</option>
                                        <option value="report" <?php echo isset($subject) && $subject === 'report' ? 'selected' : ''; ?>>Signalement de contenu</option>
                                        <option value="partnership" <?php echo isset($subject) && $subject === 'partnership' ? 'selected' : ''; ?>>Proposition de partenariat</option>
                                        <option value="other" <?php echo isset($subject) && $subject === 'other' ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea id="message" name="message" class="form-control" rows="8" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" id="privacy" name="privacy" class="form-check-input" required>
                                        <label for="privacy">J'ai lu et j'accepte la <a href="index.php?route=privacy">politique de confidentialité</a>.</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Envoyer le message</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Contact Info -->
            <div>
                <div class="forum-container" style="margin-bottom: 2rem;">
                    <div class="forum-header" style="background-color: var(--dark-blue);">
                        <h2 style="color: var(--white); margin: 0; font-size: 1.5rem;">Informations de contact</h2>
                    </div>
                    
                    <div style="padding: 2rem;">
                        <ul class="contact-info" style="list-style: none; margin: 0; padding: 0;">
                            <li style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                                <div style="color: var(--bright-blue); font-size: 1.5rem; width: 24px;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <strong>Email</strong>
                                    <p style="margin-top: 0.25rem;">forum@kopo.systems</p>
                                </div>
                            </li>
                            
                            <li style="display: flex; gap: 1rem;">
                                <div style="color: var(--bright-blue); font-size: 1.5rem; width: 24px;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <strong>Heures de support</strong>
                                    <p style="margin-top: 0.25rem;">Du lundi au vendredi<br>9h00 - 18h00</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="forum-container">
                    <div class="forum-header" style="background-color: var(--purple);">
                        <h2 style="color: var(--white); margin: 0; font-size: 1.5rem;">Nous suivre</h2>
                    </div>
                    
                    <div style="padding: 2rem;">
                        <div class="social-links" style="display: flex; flex-wrap: wrap; gap: 1rem;">
                            
                            <a href="https://github.com/KopoCorp" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background-color: #333333; color: white; border-radius: var(--border-radius); text-decoration: none;">
                                <i class="fab fa-github"></i> GitHub
                            </a>
                            
                            <a href="http://discord.kopo.systems/" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background-color: #7289DA; color: white; border-radius: var(--border-radius); text-decoration: none;">
                                <i class="fab fa-discord"></i> Discord
                            </a>
                        </div>
                        
                        <div style="margin-top: 1.5rem;">
                            <p>Rejoignez notre communauté pour rester informé des dernières actualités et participer à nos événements en ligne.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <section style="margin-top: 3rem;">
            <h2>Foire Aux Questions</h2>
            
            <div class="forum-container">
                <div class="faq-list">
                    <div class="faq-item" style="border-bottom: 1px solid var(--light-gray); padding: 1.5rem;">
                        <div class="faq-question" style="cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                            <div>Comment changer mon mot de passe ?</div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--light-gray);">
                            <p>Le mot de passe peut se changer dans le profil. Il y a un bouton « Modifier le profil » : en cliquant dessus, vous pourrez modifier votre mot de passe.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item" style="border-bottom: 1px solid var(--light-gray); padding: 1.5rem;">
                        <div class="faq-question" style="cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                            <div>Comment puis-je devenir modérateur ?</div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--light-gray);">
                            <p>Pour devenir modérateur, il faut être actif sur la plateforme et faire une demande sur notre Discord ou via le formulaire de contact.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item" style="padding: 1.5rem;">
                        <div class="faq-question" style="cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                            <div>Comment contacter le support ?</div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--light-gray);">
                            <p>Vous pouvez nous contacter via le formulaire disponible sur la page de contact ou nous écrire directement par email à forum@kopo.systems</p>
                        </div>
                    </div>

                    <div class="faq-item" style="padding: 1.5rem;">
                        <div class="faq-question" style="cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                            <div>Puis-je changer mon nom d’utilisateur ?</div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--light-gray);">
                            <p>Oui, il est possible de changer votre nom d’utilisateur dans les paramètres de votre profil.</p>
                        </div>
                    </div>

                    <div class="faq-item" style="padding: 1.5rem;">
                        <div class="faq-question" style="cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                            <div>Mon compte est bloqué, que faire ?</div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--light-gray);">
                            <p>Si votre compte est bloqué, contactez le support via le formulaire de contact en précisant votre nom d’utilisateur et la raison du blocage si vous la connaissez.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ accordion functionality
    document.querySelectorAll('.faq-question').forEach(function(question) {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const icon = this.querySelector('i');
            
            // Toggle answer visibility
            if (answer.style.display === 'none' || !answer.style.display) {
                answer.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                answer.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });
});
</script>
