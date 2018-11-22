<?php
//connection variables
$host = '_';
$user = '_';
$password = '_';
//create mysql connection
$mysqli = new mysqli($host,$user,$password);
if ($mysqli->connect_errno) {
    printf("Blad laczenia do bazy: %s\n", $mysqli->connect_error);
    die();
}
//create the database
if ( !$mysqli->query('CREATE DATABASE accounts') ) {
    printf("Errormessage: %s\n", $mysqli->error);
}
//create users table with all the fields
$mysqli->query('
CREATE TABLE `table_name_here`.`users` 
(
    `id` INT NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `hash` VARCHAR(32) NOT NULL,
    `active` TINYINT NOT NULL DEFAULT 0,
    `money` VARCHAR(32) NOT NULL DEFAULT 100000,
    `action_qty_dict` VARCHAR(512) DEFAULT NULL,
PRIMARY KEY (`id`) 
);') or die($mysqli->error);
?>