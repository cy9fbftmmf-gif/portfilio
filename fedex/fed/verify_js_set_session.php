<?php
session_start();

// Simple User-Agent validation
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$bad_agents = ['curl', 'wget', 'httpclient', 'python', 'scrapy', 'java', 'libwww'];
foreach ($bad_agents as $bad) {
    if (stripos($ua, $bad) !== false) {
        http_response_code(403);
        echo json_encode(["status" => "forbidden"]);
        exit;
    }
}

// Parse POST JSON
$input = json_decode(file_get_contents("php://input"), true);
$token = $input['token'] ?? '';

// Verify token matches the one stored in session
if (isset($_SESSION['js_token']) && hash_equals($_SESSION['js_token'], $token)) {
    $_SESSION['js_verified'] = true;
    unset($_SESSION['js_token']); // remove token after use
    echo json_encode(["status" => "ok"]);
} else {
    http_response_code(400);
    echo json_encode(["status" => "invalid"]);
}