<!-- 
  logout.php
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Logging out user, giving feedback
-->

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
          <h1>Dziękujemy za skorzystanie z aplikacji i zapraszamy ponownie</h1>
          <p><?= 'Wylogowano pomyślnie!'; ?></p>
          <a href="index.php"><button class="button button-block"/>Start</button></a>
    </div>
</body>
</html>