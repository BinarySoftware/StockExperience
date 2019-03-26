<?php
require 'backend/resetBackend.php';
?>

<!-- 
  reset.php
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Form for resetting forgotten password, after clicking on appropriate link with UUID
-->

<!DOCTYPE html>
<html >
<head>
  <meta charset="UTF-8">
  <title>Wyzeruj Hasło</title>
  <?php include 'css/css.html'; ?>
</head>
<body>
    <div class="form">
          <h1>Podaj nowe hasło</h1>
          <form action="backend/reset_password.php" method="post">
          <div class="field-wrap">
            <label>Nowe hasło<span class="req">*</span></label>
            <input type="password"required name="newpassword" autocomplete="off"/>
          </div>
          <div class="field-wrap">
            <label>Potwierdź Nowe hasło<span class="req">*</span></label>
            <input type="password"required name="confirmpassword" autocomplete="off"/>
          </div>
          <!-- This input field is needed, to get the email and hash of the user from db, but we dont want user to change and break anything -->
          <input type="hidden" name="email" value="<?= $email ?>">    
          <input type="hidden" name="hash" value="<?= $hash ?>">    
          <button class="button button-block"/>Potwierdź</button>
          </form>
    </div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="js/index.js"></script>
</body>
</html>