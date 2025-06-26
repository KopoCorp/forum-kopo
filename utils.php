<?php
require_once __DIR__ . '/session.php';

$API_BASE = 'http://192.168.1.180:8000';

function api_request($method, $endpoint, $data = null, $token = null) {
    global $API_BASE;
    $ch = curl_init();
    $url = $API_BASE . $endpoint;
    if ($method === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
    }
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CUSTOMREQUEST => $method,
    ]);
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if ($method !== 'GET' && $data) {
        $payload = json_encode($data);
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response ? json_decode($response, true) : null;
}

function fetch_json($endpoint, $params = [], $token = null) {
    return api_request('GET', $endpoint, $params, $token);
}

function post_json($endpoint, $data, $token = null) {
    return api_request('POST', $endpoint, $data, $token);
}
?>
