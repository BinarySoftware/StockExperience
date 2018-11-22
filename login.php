<?php
/* User login process, checks if user exists and password is correct */
// Escape email to protect against SQL injections
$email = $mysqli->escape_string($_POST['email']);
$result = $mysqli->query("SELECT * FROM users WHERE email='$email'");
if ( $result->num_rows == 0 ){ // User doesn't exist
     $_SESSION['message'] = "Użytkownik z takim adresem nie istnieje!";
    echo "<script type='text/javascript'> document.location = '/error.php'; </script>";
}
else { // User exists
    $user = $result->fetch_assoc();
    if ( password_verify($_POST['password'], $user['password']) ) {
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['active'] = $user['active'];
        $_SESSION['money'] = $user['money'];
        $_SESSION['action_qty_dict'] = $user['action_qty_dict'];
        // This is how we'll know the user is logged in
        $_SESSION['logged_in'] = true;
        echo "<script type='text/javascript'> document.location = '/profile.php'; </script>";
    }
    else {
        $_SESSION['message'] = "Błędne hasło!";
        echo "<script type='text/javascript'> document.location = '/error.php'; </script>";
    }
}
?>