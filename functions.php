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

function markdown_to_html($markdown) {
    require_once __DIR__ . "/libs/Parsedown.php";
    $parser = new Parsedown();
    $html = $parser->text($markdown);
    $allowed = "<p><br><strong><em><u><s><ul><ol><li><pre><code><a><img><blockquote><h1><h2><h3><h4><h5><h6>";
    return strip_tags($html, $allowed);
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
    // Expanded keyword lists for more reliable matching
    $dev_keywords = [
        'dev', 'développement', 'developpement', 'development', 'développeur',
        'developpeur', 'developer', 'programmation', 'coding', 'code',
        'framework', 'web', 'mobile', 'ia', 'devops'
    ];
    $security_keywords = [
        'cyber', 'cybersécurité', 'cybersecurite', 'cyber sécurité',
        'cybersecurity', 'sécurité', 'securite', 'hacking', 'pentest', 'vuln',
        'vulnérabilité', 'vulnerabilite', 'cryptographie', 'malware',
        'protection des données', 'attaque', 'intrusion', 'ransomware'
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
/**
 * Calculate forum statistics client-side.
 *
 * @param API $api
 * @return array
 */
function calculate_forum_stats($api) {
    $stats = [
        'user_count' => 0,
        'thread_count' => 0,
        'reply_count' => 0,
        'most_active_user' => null
    ];

    try {
        $stats['user_count'] = (int)$api->request('/users/count');
    } catch (Exception $e) {
        // ignore errors
    }

    try {
        $categories = $api->request('/forum/categories');
        foreach ($categories as $cat) {
            $stats['thread_count'] += $cat['thread_count'] ?? 0;
            $stats['reply_count'] += $cat['reply_count'] ?? 0;
        }
    } catch (Exception $e) {
        // ignore errors
    }

    if ($stats['user_count'] > 0) {
        try {
            $users = $api->request('/users?skip=0&limit=' . $stats['user_count']);
            $bestUser = null;
            $bestScore = -1;
            foreach ($users as $user) {
                $score = ($user['thread_count'] ?? 0)
                       + ($user['reply_count'] ?? 0)
                       + ($user['article_count'] ?? 0);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestUser = $user;
                }
            }
            if ($bestUser) {
                $stats['most_active_user'] = $bestUser;
            }
        } catch (Exception $e) {
            // ignore errors
        }
    }

    return $stats;
}

/**
 * Extract a username from an API entity array.
 *
 * Many API responses include a nested `user` or `author` object. This helper
 * attempts to retrieve the username from the various possible keys.
 *
 * @param array $data Entity returned by the API
 * @return string|null Username if available, otherwise null
 */
function get_username(array $data) {
    if (!empty($data['username'])) {
        return $data['username'];
    }
    if (!empty($data['author']['username'])) {
        return $data['author']['username'];
    }
    if (!empty($data['user']['username'])) {
        return $data['user']['username'];
    }
    return null;
}

/**
 * Fetch latest security alerts from CERT-FR RSS feed.
 *
 * @param int $limit Number of alerts to retrieve
 * @return array[] List of alerts with title, link, description and pubDate
 */
function fetch_cert_alerts($limit = 1) {
    $feed_url = 'https://www.cert.ssi.gouv.fr/alerte/feed/';
    $alerts = [];

    $context = stream_context_create([
        'http' => [
            'user_agent' => 'KopoForumBot/1.0',
            'timeout' => 5
        ]
    ]);

    $feed = @file_get_contents($feed_url, false, $context);
    if ($feed === false) {
        return $alerts;
    }

    $xml = @simplexml_load_string($feed);
    if ($xml === false || empty($xml->channel->item)) {
        return $alerts;
    }

    foreach ($xml->channel->item as $item) {
        $alerts[] = [
            'title' => (string)$item->title,
            'link' => (string)$item->link,
            'description' => strip_tags((string)$item->description),
            'pubDate' => (string)$item->pubDate
        ];
        if (count($alerts) >= $limit) {
            break;
        }
    }

    return $alerts;
}
?>
