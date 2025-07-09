    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>À propos</h4>
                    <p>Kopo est une plateforme d'échange et d'analyse dédiée à l'informatique, la cybersécurité et les technologies du numérique.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="forums.php">Forums</a></li>
                        <li><a href="articles.php">Articles</a></li>
                        <li><a href="cyber-securite.php">Cybersécurité</a></li>
                        <li><a href="dev.php">Développement</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Informations</h4>
                    <ul class="footer-links">
                        <li><a href="about.php">À propos de Kopo</a></li>
                        <li><a href="charte.php">Charte du forum</a></li>
                        <li><a href="terms.php">Conditions d'utilisation</a></li>
                        <li><a href="privacy.php">Politique de confidentialité</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Newsletter</h4>
                    <p>Recevez nos derniers articles et analyses dans votre boîte mail.</p>
                    <form class="newsletter-form" method="post" action="subscribe.php">
                        <input type="email" name="email" placeholder="Votre adresse email" class="form-control" required>
                        <button type="submit" class="btn btn-primary">OK</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Kopo Forum. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>