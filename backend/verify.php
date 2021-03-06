<?php
require 'db.php';
session_start();
?>

<!--
  verify.html
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Verifies registered user email, the link to this page is included in the email from register.php 
-->

<?php 
// Make sure email and hash variables aren't empty
if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash'])) {
    $email = $mysqli->escape_string($_GET['email']); 
    $hash = $mysqli->escape_string($_GET['hash']); 
    // Select user with matching email and hash, who hasn't verified their account yet (active = 0)
    $result = $mysqli->query("SELECT * FROM users WHERE email='$email' AND hash='$hash' AND active='0'");
    if ( $result->num_rows == 0 ) { 
        $_SESSION['message'] = "Konto już zostało aktywowane lub błędny link";
        echo "<script type='text/javascript'> document.location = '../error.php'; </script>";
    } else {
        $_SESSION['message'] = "Konto aktywne!";
        // Set the user status to active (active = 1)
        $mysqli->query("UPDATE users SET active='1' WHERE email='$email'") or die($mysqli->error);
        $_SESSION['active'] = 1;
        echo "<script type='text/javascript'> document.location = '../success.php'; </script>";
    }
} else { // if user entered wrong data
    $_SESSION['message'] = "Nieprawidłowe informacje podane do utworzenia konta";
    echo "<script type='text/javascript'> document.location = '../error.php'; </script>";
}     
?>