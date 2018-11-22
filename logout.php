<?php
/* Log out process, unsets and destroys session variables */
session_start();
session_unset();
session_destroy(); 
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Wylogowano</title>
  <?php include 'css/css.html'; ?>
</head>
<body>
    <div class="form">
          <h1>Dziękujemy za korzystanie</h1>
          <p><?= 'Wylogowano pomyślnie!'; ?></p>
          <a href="index.php"><button class="button button-block"/>Start</button></a>
    </div>
</body>
</html>