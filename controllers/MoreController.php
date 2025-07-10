<?php
class HomeController {
    private $api;

    public function __construct($api) {
        $this->api = $api;
    }

    public function index() {
        $page_title = "Conditions d'utilisations";
        $page_description = "Forum de discussion et actualités sur l'informatique, la cybersécurité et les technologies";

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/home.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}