<?php
/* Database connection settings */
$host = 'your_host';
$user = 'your_un';
$pass = 'your_pass';
$db = 'your_db';
$mysqli = new mysqli($host,$user,$pass,$db) or die($mysqli->error);
?>