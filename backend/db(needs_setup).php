<!--
  db.html
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose:  Database connection settings - you need to set them according to specs of your server, then rename file to db.php
-->

<?php
$host = 'your_host';
$user = 'your_username';
$pass = 'your_password';
$db = 'your_db';
$mysqli = new mysqli($host,$user,$pass,$db) or die($mysqli->error);
?>