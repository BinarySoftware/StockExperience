<?php
require 'db.php';
session_start();
// Make sure email and hash variables aren't empty
if( isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash']) ) {
    $email = $mysqli->escape_string($_GET['email']); 
    $hash = $mysqli->escape_string($_GET['hash']); 
    // Make sure user email with matching hash exist in db
    $result = $mysqli->query("SELECT * FROM users WHERE email='$email' AND hash='$hash'");
    if ( $result->num_rows == 0 ) { 
        $_SESSION['message'] = "Zły adres do wyzerowania hasła!";
        echo "<script type='text/javascript'> document.location = '../error.php'; </script>";
    }
} else {
    $_SESSION['message'] = "Błąd weryfikacji, spróbuj ponownie!";
    echo "<script type='text/javascript'> document.location = '../error.php'; </script>";
}
?>