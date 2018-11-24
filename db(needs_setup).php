<?php
/* Database connection settings */
$host = 'your_host';
$user = 'your_username';
$pass = 'your_password';
$db = 'your_db';
$mysqli = new mysqli($host,$user,$pass,$db) or die($mysqli->error);
?>