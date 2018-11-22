<?php
/* Displays all error messages */
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