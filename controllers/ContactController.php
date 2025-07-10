<?php
class ContactController {
    private $api;
    public function __construct($api) {
        $this->api = $api;
    }
    public function index() {
        $page_title = "Contact";
        $page_description = "Contactez l'équipe Kopo Forum pour toute question, suggestion ou signalement";
        $success = false;
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';
            if (empty($name)) {
                $error = "Veuillez indiquer votre nom.";
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Veuillez fournir une adresse email valide.";
            } elseif (empty($subject)) {
                $error = "Veuillez indiquer un sujet.";
            } elseif (empty($message)) {
                $error = "Veuillez écrire un message.";
            } else {
                try {
                    $this->api->request('/contact', 'POST', [
                        'name' => $name,
                        'email' => $email,
                        'subject' => $subject,
                        'message' => $message,
                        'user_id' => $this->api->isLoggedIn() ? $_SESSION['user']['id'] : null
                    ]);
                    $success = true;
                } catch (Exception $e) {
                    $error = "Erreur lors de l'envoi du formulaire: " . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../views/templates/header.php';
        require __DIR__ . '/../views/contact.php';
        require __DIR__ . '/../views/templates/footer.php';
    }
}
