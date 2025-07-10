<?php
/** Utility security functions */
function sanitize_string($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function sanitize_int($value) {
    return filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['default' => 0]
    ]);
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Detect the topic of a piece of content based on tags/keywords.
 * Returns 'dev' for development, 'security' for cybersecurity or null if no
 * match is found.
 *
 * @param array $tags    List of tag names
 * @param string $content Content or title to analyse
 * @return string|null
 */
function detect_topic(array $tags = [], string $content = '') {
    $dev_keywords = [
        'dev', 'développement', 'programmation', 'code', 'framework', 'web',
        'mobile', 'ia', 'devops'
    ];
    $security_keywords = [
        'cyber', 'sécurité', 'securite', 'hacking', 'pentest', 'vuln',
        'vulnérabilité', 'vulnerabilite', 'cryptographie', 'protection des données'
    ];

    $text = strtolower($content . ' ' . implode(' ', $tags));

    foreach ($dev_keywords as $kw) {
        if (strpos($text, $kw) !== false) {
            return 'dev';
        }
    }
    foreach ($security_keywords as $kw) {
        if (strpos($text, $kw) !== false) {
            return 'security';
        }
    }
    return null;
}
?>
