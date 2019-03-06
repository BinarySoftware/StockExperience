<?php
/* Database connection settings - you need to set them according to specs of
your server, then rename file to db.php */
$host = 'your_host';
$user = 'your_username';
$pass = 'your_password';
$db = 'your_db';
$mysqli = new mysqli($host,$user,$pass,$db) or die($mysqli->error);
?>