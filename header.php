<?php
require_once 'config.php';
require_once 'api.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : SITE_DESCRIPTION; ?>">
    <!-- Favicon -->
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<pre style="color: #fff; background-color: #333; padding: 1rem;">
<?php
var_dump($_SESSION);
?>
</pre>

<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="index.php">
                    <img style="vertical-align: middle;" src="assets/kopologo.png" alt="KOPO Forum Logo">
                </a>
                <button class="mobile-nav-toggle" id="mobile-nav-toggle" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="main-nav" id="main-nav">
                <ul>
                    <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Accueil</a></li>
                    <li><a href="forums.php" <?php echo basename($_SERVER['PHP_SELF']) == 'forums.php' ? 'class="active"' : ''; ?>>Forums</a></li>
                    <li><a href="articles.php" <?php echo basename($_SERVER['PHP_SELF']) == 'articles.php' ? 'class="active"' : ''; ?>>Articles</a></li>
                    <li><a href="cyber-securite.php" <?php echo basename($_SERVER['PHP_SELF']) == 'cyber-securite.php' ? 'class="active"' : ''; ?>>Cyber-Sécurité</a></li>
                    <li><a href="dev.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dev.php' ? 'class="active"' : ''; ?>>Développement</a></li>
                    <?php if ($api->isLoggedIn()): ?>
                    <li><a href="membres.php" <?php echo basename($_SERVER['PHP_SELF']) == 'membres.php' ? 'class="active"' : ''; ?>>Membres</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="user-actions">
                <?php if ($api->isLoggedIn()): ?>
                    <?php $user = $api->getCurrentUser(); ?>
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="profile.php?id=<?php echo $user['id']; ?>">
                                <i class="fas fa-user"></i> Mon Profil
                            </a>
                            <a href="messages.php">
                                <i class="fas fa-envelope"></i> Messages
                                <?php
                                try {
                                    $unread_count = $api->request('/messages/unread-count', 'GET', [], true);
                                    if ($unread_count['count'] > 0) {
                                        echo '<span class="badge">' . $unread_count['count'] . '</span>';
                                    }
                                } catch (Exception $e) {
                                    // Silently fail if we can't get message count
                                }
                                ?>
                            </a>
                            <a href="settings.php">
                                <i class="fas fa-cog"></i> Paramètres
                            </a>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Connexion</a>
                    <a href="register.php" class="btn btn-primary">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <?php
    // Display flash messages if any
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'info';
        echo '<div class="notification notification-' . $type . '">';
        echo '<i class="fas fa-' . ($type == 'success' ? 'check-circle' : ($type == 'error' ? 'exclamation-circle' : 'info-circle')) . '"></i>';
        echo htmlspecialchars($message);
        echo '<button class="notification-close" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
        echo '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
    ?>
