<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
  <title>Błąd</title>
  <?php include 'css/css.html'; ?>
</head>
<body>
<div class="form">
    <h1>Błąd</h1>
    <p>
    <?php 
    //If there is possibility to write message, do it, else get back to start page
    if( isset($_SESSION['message']) AND !empty($_SESSION['message']) ): 
        echo $_SESSION['message'];    
    else:
        echo "<script type='text/javascript'> document.location = '/index.php'; </script>";
    endif;
    ?>
    </p>     
    <a href="index.php"><button class="button button-block"/>Start</button></a>
</div>
</body>
</html>