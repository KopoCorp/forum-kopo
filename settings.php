<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

// Only logged in users can access settings
if (!$api->isLoggedIn()) {
    header('Location: index.php?route=login');
    exit();
}

$page_title = "Paramètres du compte";
$page_description = "Gestion du compte utilisateur";

include 'header.php';
?>
<div class="page-header" style="background-color: var(--near-black); padding: 2rem 0;">
    <div class="container">
        <div class="breadcrumb" style="margin-bottom: 0.5rem; color: #999;">
            <a href="index.php" style="color: #999;">Accueil</a> &raquo;
            Paramètres
        </div>
        <h1 style="color: var(--white); margin-bottom: 0.5rem;">Paramètres du compte</h1>
    </div>
</div>

<main class="main-content section">
    <div class="container" style="max-width: 600px;">
        <div class="forum-container">
            <div style="padding: 2rem;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 1rem;"><a href="edit-profile.php" class="btn btn-outline" style="width: 100%;">Modifier mon profil</a></li>
                    <li style="margin-bottom: 1rem;"><a href="my-content.php" class="btn btn-outline" style="width: 100%;">Mes publications</a></li>
                    <li style="margin-top: 2rem;"><a href="delete-account.php" class="btn btn-accent" style="width: 100%; background-color: var(--accent-red);">Supprimer mon compte</a></li>
                </ul>
            </div>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
