<?php
class MembersController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Membres";
        $page_description = "Découvrez les membres de la communauté Kopo Forum";

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 24; // members per page
        $skip = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
        $sort_param = '';
        switch ($sort) {
            case 'activity':
                $sort_param = '&sort=activity';
                break;
            case 'a-z':
                $sort_param = '&sort=username';
                break;
            case 'newest':
            default:
                $sort_param = '&sort=newest';
                break;
        }

        $filtered_count = 0;
        try {
            if (!empty($search)) {
                $count_res = $this->api->request('/users/count');
                $all_count = $count_res['count'] ?? 0;
                $all_users = $this->api->request('/users?skip=0&limit=' . $all_count . $sort_param);
                $filtered = array_filter($all_users, function ($u) use ($search) {
                    return stripos($u['username'] ?? '', $search) !== false;
                });
                $filtered_count = count($filtered);
                $members_data = array_slice(array_values($filtered), $skip, $limit);
            } else {
                $query = '/users?skip=' . $skip . '&limit=' . $limit . $sort_param;
                $members_data = $this->api->request($query);
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors du chargement des membres: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            $members_data = [];
        }

        // Ensure post_count equals total threads + articles for each member
        foreach ($members_data as &$member) {
            $threads = [];
            $articles = [];
            try {
                $threads = $this->api->request('/users/' . $member['id'] . '/threads');
            } catch (Exception $e) {
                $threads = [];
            }
            try {
                $articles = $this->api->request('/users/' . $member['id'] . '/articles');
            } catch (Exception $e) {
                $articles = [];
            }
            $thread_count = is_array($threads) ? count($threads) : 0;
            $article_count = is_array($articles) ? count($articles) : 0;
            $member['post_count'] = $thread_count + $article_count;
        }
        unset($member);

        try {
            if (!empty($search)) {
                $total_pages = max(1, ceil($filtered_count / $limit));
            } else {
                $total_count = $this->api->request('/users/count');
                $total_pages = ceil(($total_count['count'] ?? 24) / $limit);
            }
        } catch (Exception $e) {
            if (!isset($_SESSION['flash_message'])) {
                $_SESSION['flash_message'] = "Erreur lors du chargement du nombre de membres: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            $total_pages = 1;
        }

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/members.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
