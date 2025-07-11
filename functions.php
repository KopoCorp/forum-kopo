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
        'article_count' => 0,
        'most_active_user' => null
    ];

    try {
        $count_res = $api->request('/users/count');
        $stats['user_count'] = $count_res['count'] ?? 0;
    } catch (Exception $e) {
        // ignore errors
    }

    try {
        $articles_res = $api->request('/articles/count');
        $stats['article_count'] = $articles_res['count'] ?? 0;
    } catch (Exception $e) {
        // ignore errors
    }

    try {
        $categories = $api->request('/forum/categories');
        foreach ($categories as $cat) {
            if (isset($cat['thread_count']) && isset($cat['reply_count'])) {
                $stats['thread_count'] += $cat['thread_count'];
                $stats['reply_count'] += $cat['reply_count'];
            } else {
                $threads = $api->request('/forum/threads?category_id=' . $cat['id']);
                $stats['thread_count'] += is_array($threads) ? count($threads) : 0;
                if (is_array($threads)) {
                    foreach ($threads as $t) {
                        $stats['reply_count'] += $t['reply_count'] ?? 0;
                    }
                }
            }
        }
    } catch (Exception $e) {
        // ignore errors
    }

    if ($stats['user_count'] > 0) {
        try {
            $users = $api->request('/users?skip=0&limit=' . $stats['user_count']);
            $bestUser = null;
            $maxArticles = -1;
            foreach ($users as $user) {
                $articleCount = $user['article_count'] ?? null;
                if ($articleCount === null) {
                    try {
                        $arts = $api->request('/users/' . $user['id'] . '/articles');
                        $articleCount = is_array($arts) ? count($arts) : 0;
                    } catch (Exception $e) {
                        $articleCount = 0;
                    }
                }
                if ($articleCount > $maxArticles) {
                    $maxArticles = $articleCount;
                    $bestUser = $user;
                    $bestUser['article_count'] = $articleCount;
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

    $user_id = null;
    if (!empty($data['user_id'])) {
        $user_id = $data['user_id'];
    } elseif (!empty($data['user']['id'])) {
        $user_id = $data['user']['id'];
    } elseif (!empty($data['author_id'])) {
        $user_id = $data['author_id'];
    } elseif (!empty($data['author']['id'])) {
        $user_id = $data['author']['id'];
    }

    if ($user_id) {
        static $cache = [];
        if (isset($cache[$user_id])) {
            return $cache[$user_id];
        }

        global $api;
        if ($api) {
            try {
                $user = $api->request('/users/' . $user_id);
                if (isset($user['username'])) {
                    $cache[$user_id] = $user['username'];
                    return $cache[$user_id];
                }
            } catch (Exception $e) {
                $cache[$user_id] = null;
            }
        }
    }

    return null;
}

/**
 * Retrieve the banner image URL for an article, generating a random one
 * when none is provided.
 *
 * @param array $article Article data
 * @return string URL of the banner image
 */
function get_article_banner_url(array $article) {
    if (!empty($article['image_url'])) {
        return $article['image_url'];
    }
    $seed = mt_rand(0, 1000000);
    return RANDOM_BANNER_BASE . $seed . '/' . RANDOM_BANNER_SIZE;
}

/**
 * Filter an array of items to only keep published content when available.
 *
 * Items without an `is_pub` flag are left untouched.
 *
 * @param array $items List of items returned by the API
 * @return array Filtered list containing only published items
 */
function filter_published(array $items) {
    return array_values(array_filter($items, function ($it) {
        return !isset($it['is_pub']) || $it['is_pub'];
    }));
}
?>
