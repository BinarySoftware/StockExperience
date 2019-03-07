<!--
  reset_password.html
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Backend part of reset.php
-->

<?php
require 'db.php';
session_start();
// Make sure the form is being submitted with method="post"
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    // Make sure the two passwords match
    if ( $_POST['newpassword'] == $_POST['confirmpassword'] ) { 
        $new_password = password_hash($_POST['newpassword'], PASSWORD_BCRYPT);
        // We get $_POST['email'] and $_POST['hash'] from the hidden input field of reset.php form
        $email = $mysqli->escape_string($_POST['email']);
        $hash = $mysqli->escape_string($_POST['hash']);
        $sql = "UPDATE users SET password='$new_password', hash='$hash' WHERE email='$email'";
        if ( $mysqli->query($sql) ) {
        $_SESSION['message'] = "Hasło pomyślnie wyzerowane!";
        echo "<script type='text/javascript'> document.location = '../success.php'; </script>";
        }
    } else {
        $_SESSION['message'] = "Hasła się nie zgadzają!";
        echo "<script type='text/javascript'> document.location = '../error.php'; </script>";
    }
}
?>