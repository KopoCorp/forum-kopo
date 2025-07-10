<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'api.php';

if (!$api->isLoggedIn()) {
    header('Location: index.php?route=login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$target_type = isset($_POST['target_type']) ? sanitize_string($_POST['target_type']) : '';
$target_id   = isset($_POST['target_id']) ? sanitize_int($_POST['target_id']) : 0;
$reason      = isset($_POST['reason']) ? sanitize_string($_POST['reason']) : '';
$details     = isset($_POST['details']) ? sanitize_string($_POST['details']) : '';

if ($target_type !== 'user' || !$target_id || empty($reason)) {
    $_SESSION['flash_message'] = "Données de signalement invalides.";
    $_SESSION['flash_type'] = 'error';
    header('Location: profile.php?id=' . $target_id);
    exit();
}

$reporter = $api->getCurrentUser();
try {
    $reported = $api->request('/users/' . $target_id);
} catch (Exception $e) {
    $reported = ['username' => 'Utilisateur #' . $target_id];
}

try {
    $config = $api->request('/reporting/config');
    $WEBHOOK = $config['discord_webhook'] ?? '';
    $ROLE_ID = $config['discord_role_id'] ?? '';
    $EMOJI   = $config['discord_emoji'] ?? '';
    $TOPIC   = $config['ntfy_topic'] ?? '';
} catch (Exception $e) {
    $WEBHOOK = $ROLE_ID = $EMOJI = $TOPIC = '';
}

$discord_content = "<@&$ROLE_ID> $EMOJI\n" .
    "Signalement par **" . ($reporter['username'] ?? 'Inconnu') . "**" .
    " contre **" . ($reported['username'] ?? 'Inconnu') . "**\n" .
    "Raison : $reason\n" .
    (!empty($details) ? "Détails : $details" : '');

if (!empty($WEBHOOK)) {
    $payload = json_encode([
        'content' => $discord_content,
        'allowed_mentions' => ['roles' => [$ROLE_ID]]
    ]);
    $ch = curl_init($WEBHOOK);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

if (!empty($TOPIC)) {
    $ntfy_message = "Signalement par " . ($reporter['username'] ?? 'Inconnu') .
        " contre " . ($reported['username'] ?? 'Inconnu') . "\n" .
        "Raison: $reason" . (!empty($details) ? "\nDétails: $details" : '');

    $ch = curl_init('https://ntfy.sh/' . $TOPIC);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Title: Nouveau signalement',
        'Icon: https://robertsspaceindustries.com/media/eyt3f0h14jzu0r/logo/KOPO-Logo.png',
        'Priority: high',
        'Tags: warning'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $ntfy_message);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

$_SESSION['flash_message'] = "Signalement envoyé.";
$_SESSION['flash_type'] = 'success';

header('Location: profile.php?id=' . $target_id);
exit();
?>
