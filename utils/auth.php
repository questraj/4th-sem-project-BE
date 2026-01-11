<?php
require_once 'response.php';

define('SECRET_KEY', 'my_super_secret_key_change_this');

function generateToken($userId) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode(['user_id' => $userId, 'exp' => time() + (86400 * 7)]); // 7 Days
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, SECRET_KEY, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function authenticate() {
    $headers = apache_request_headers();
    $authHeader = null;

    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    }

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        sendResponse(false, "Unauthorized: No token provided");
    }

    $token = $matches[1];
    $tokenParts = explode('.', $token);

    if (count($tokenParts) != 3) {
        http_response_code(401);
        sendResponse(false, "Unauthorized: Invalid token format");
    }

    $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
    $signatureProvided = $tokenParts[2];

    // Verify Signature
    $signature = hash_hmac('sha256', $tokenParts[0] . "." . $tokenParts[1], SECRET_KEY, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    if (!hash_equals($base64UrlSignature, $signatureProvided)) {
        http_response_code(401);
        sendResponse(false, "Unauthorized: Invalid signature");
    }

    $data = json_decode($payload);
    
    if ($data->exp < time()) {
        http_response_code(401);
        sendResponse(false, "Unauthorized: Token expired");
    }

    return (int)$data->user_id;
}
?>