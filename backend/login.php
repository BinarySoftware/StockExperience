<?php
// Escape email to protect against SQL injections
$email = $mysqli->escape_string($_POST['email']);
$result = $mysqli->query("SELECT * FROM users WHERE email='$email'");

if ( $result->num_rows == 0 ) { // result has no rows, hence user doesn't exist
     $_SESSION['message'] = "Użytkownik z takim adresem nie istnieje!";
    echo "<script type='text/javascript'> document.location = '../error.php'; </script>";
} else { // User exists
    $user = $result->fetch_assoc();
    if ( password_verify($_POST['password'], $user['password']) ) {
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['active'] = $user['active'];
        $_SESSION['money'] = $user['money'];
        $_SESSION['action_qty_dict'] = $user['action_qty_dict'];
        // flag to check if user is logged in, for later use
        $_SESSION['logged_in'] = true;
        echo "<script type='text/javascript'> document.location = '../profile.php'; </script>";
    } else { // wrong password
        $_SESSION['message'] = "Błędne hasło!";
        echo "<script type='text/javascript'> document.location = '../error.php'; </script>";
    }
}
?>