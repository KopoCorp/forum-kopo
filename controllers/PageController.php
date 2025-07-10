<?php
class PageController {
    private $api;
    private $pages = [
        'about' => [
            'title' => 'À propos de Kopo',
            'description' => "En savoir plus sur la plateforme et son fonctionnement"
        ],
        'charte' => [
            'title' => 'Charte du forum',
            'description' => "Les règles de bonne conduite sur Kopo"
        ],
        'terms' => [
            'title' => "Conditions d'utilisation",
            'description' => "Conditions générales de la plateforme Kopo"
        ],
        'privacy' => [
            'title' => 'Politique de confidentialité',
            'description' => 'Comment nous protégeons vos données personnelles'
        ]
    ];

    public function __construct($api) {
        $this->api = $api;
    }

    public function show($slug) {
        if (!isset($this->pages[$slug])) {
            http_response_code(404);
            require '404.html';
            return;
        }
        $page_title = $this->pages[$slug]['title'];
        $page_description = $this->pages[$slug]['description'];

        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/pages/' . $slug . '.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
?>
