<!-- 
  forgot.php
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Give user possibility to reset forgotten password provided correct e-mail
-->

<?php
require 'backend/forgotBackend.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
  <title>Wyzeruj hasło</title>
  <?php include 'css/css.html'; ?>
</head>
<body>
  <div class="form">
    <h1>Wyzeruj hasło</h1>
    <form action="forgot.php" method="post">
     <div class="field-wrap">
      <label>
        Adres email<span class="req">*</span>
      </label>
      <input type="email"required autocomplete="off" name="email"/>
    </div>
    <button class="button button-block"/>Zeruj hasło</button>
    </form>
  </div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="js/index.js"></script>
</body>
</html>