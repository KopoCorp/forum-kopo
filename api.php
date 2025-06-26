<?php
require_once 'config.php';

class API {
    private $base_url;
    private $token;
    
    public function __construct() {
        $this->base_url = API_BASE_URL;
        $this->token = isset($_SESSION['token']) ? $_SESSION['token'] : null;
    }
    
    /**
     * Make API request
     * 
     * @param string $endpoint The API endpoint
     * @param string $method HTTP method (GET, POST, etc)
     * @param array $data Data to send with request
     * @param bool $auth Whether to include auth token
     * @return array Response data
     */
    public function request($endpoint, $method = 'GET', $data = [], $auth = false, $json = true) {
        $url = $this->base_url . $endpoint;
        $ch = curl_init();
        
        // Set method and data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        // Headers
        $headers = [$json ? 'Content-Type: application/json' : 'Content-Type: application/x-www-form-urlencoded'];
        if ($auth && $this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Post data if needed
        if (!empty($data) && $method !== 'GET') {
            if ($json) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        // Execute request
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check for errors
        if (curl_errno($ch)) {
            throw new Exception('API Request Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        // Process response
        $result = json_decode($response, true);
        if ($status >= 400) {
            // Handle error based on status code
            $error_message = 'API request failed';
            if (isset($result['detail'])) {
                $detail = $result['detail'];
                if (is_array($detail)) {
                    // FastAPI style validation errors or array messages
                    $msgs = [];
                    foreach ($detail as $d) {
                        if (is_array($d) && isset($d['msg'])) {
                            $msgs[] = $d['msg'];
                        } else {
                            $msgs[] = is_string($d) ? $d : json_encode($d);
                        }
                    }
                    $error_message = implode('; ', $msgs);
                } else {
                    $error_message = $detail;
                }
            }

            throw new Exception('API Error (' . $status . '): ' . $error_message);
        }
        
        return $result;
    }
    
    /**
     * Login user and store token
     * 
     * @param string $username Username
     * @param string $password Password
     * @return array User data
     */
    public function login($username, $password) {
        // The login endpoint expects form-encoded data
        $response = $this->request(
            '/login',
            'POST',
            [
                'username' => $username,
                'password' => $password
            ],
            false,
            false
        );
        
        if (isset($response['access_token'])) {
            $_SESSION['token'] = $response['access_token'];
            $_SESSION['user'] = $response['user'];
            $this->token = $response['access_token'];
        }
        
        return $response;
    }
    
    /**
     * Logout and clear session
     */
    public function logout() {
        if ($this->token) {
            try {
                $this->request('/logout', 'POST', [], true);
            } catch (Exception $e) {
                // Continue with logout even if API request fails
            }
        }
        
        // Clear session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }
    
    /**
     * Helper to check if user is logged in
     * 
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['token']) && isset($_SESSION['user']);
    }
    
    /**
     * Get current user data
     * 
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }
}

// Initialize API instance
$api = new API();
