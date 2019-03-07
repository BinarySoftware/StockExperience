<!--
  forgotBackend.html
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Backend part for sending data to user if user requested to change password
-->

<?php
require 'db.php';
session_start();

// this page is used in order to help users in case they have forgotten their password
if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {   
    $email = $mysqli->escape_string($_POST['email']);
    $result = $mysqli->query("SELECT * FROM users WHERE email='$email'");
    if ( $result->num_rows == 0 ) {// result has no rows, hence user doesn't exist 
        $_SESSION['message'] = "Użytkownik z takim adresem nie istnieje!";
        echo "<script type='text/javascript'> document.location = '../error.php'; </script>";
    } else { // User exists (num_rows != 0)
        $user = $result->fetch_assoc(); // $user - array containing all user data
        $email = $user['email'];
        $hash = $user['hash'];
        $first_name = $user['first_name'];

        //message informing user to check their inbox
        $_SESSION['message'] = "<p>Proszę sprawdzić mail <span>$email</span>"
        . " gdzie został wysłany link do ukończenia zerowania hasła!</p>";
        $to      = $email;
        $subject = 'Zerowanie hasła ( StockExperience )';
        $message_body = '
        Witaj '.$first_name.',
        Prosiłeś o możliwość wyzerowania hasła, oto twój link:
        http://stockexperiencepl.000webhostapp.com/reset.php?email='.$email.'&hash='.$hash;  
        mail($to, $subject, $message_body);

        echo "<script type='text/javascript'> document.location = '../success.php'; </script>";
  }
}
?>