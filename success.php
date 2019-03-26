<?php
// open session, fetch data
session_start();
?>

<!-- 
  success.php
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Model for giving positive feedback to user
-->

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
  <title>Sukces</title>
  <?php include 'css/css.html'; ?>
</head>
<body>
<div class="form">
    <h1><?= 'Super!'; ?></h1>
    <p>
    <?php 
    if( isset($_SESSION['message']) AND !empty($_SESSION['message']) ):
        echo $_SESSION['message'];    
    else:
        echo "<script type='text/javascript'> document.location = '/index.php'; </script>";
    endif;
    ?>
    </p>
    <a href="index.php"><button class="button button-block"/>Home</button></a>
</div>
</body>
</html>