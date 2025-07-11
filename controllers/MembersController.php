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
            case 'reputation':
                $sort_param = '&sort=reputation';
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
