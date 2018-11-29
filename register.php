<?php
// Set session variables to be used on profile.php page
$_SESSION['email'] = $_POST['email'];
$_SESSION['first_name'] = $_POST['firstname'];
$_SESSION['last_name'] = $_POST['lastname'];

// Escape all $_POST variables to protect against SQL injections
$first_name = $mysqli->escape_string($_POST['firstname']);
$last_name = $mysqli->escape_string($_POST['lastname']);
$email = $mysqli->escape_string($_POST['email']);
$password = $mysqli->escape_string(password_hash($_POST['password'], PASSWORD_BCRYPT));
$hash = $mysqli->escape_string( md5( rand(0,1000) ) );

// Check if user with that email already exists
// We know user email exists if the rows returned are more than 0
$result = $mysqli->query("SELECT * FROM users WHERE email='$email'") or die($mysqli->error());
if ( $result->num_rows > 0 ) {
    $_SESSION['message'] = 'Uzytkownik z takim mailem już istnieje!';
    echo "<script type='text/javascript'> document.location = '/error.php'; </script>";
}
else { 
    // active is 0 by DEFAULT
    //Small forloop to make mainteneance easier in case of changes in stock indexes
    $indexes = ["KGH","PKO","PKN","PZU","JSW","CCC","DNP","CDR","LTS","ALR","TPE","PEO","SAN","PGN","GNB","ENG","PGE","ENA","EUR","KRU","PKP","LPP","PLY","MIL","CPS","OPL","MBK","EAT","BMC","VST","GTC","BFT","MRB","11B","MAB","EURPLN","CHFPLN","USDPLN","GBPPLN"];
    $listIndexValue = "";
    $lastElement = end($indexes);
    foreach ($indexes as &$index) {
        $listIndexValue .= $index;
        if($index == $lastElement) {
            $listIndexValue .= "-0";
        } else {
            $listIndexValue .= "-0,";
        }
    }
    $sql = "INSERT INTO users (first_name, last_name, email, password, hash, money, action_qty_dict) " 
    . "VALUES ('$first_name','$last_name','$email','$password','$hash','100000','$listIndexValue')";

    // Add user to the database
    if ( $mysqli->query($sql) ){
        $_SESSION['active'] = 0; //0 until user activates their account with verify.php
        $_SESSION['logged_in'] = true; // So we know the user has logged in
        $_SESSION['message'] = "Link weryfikacyjny wysłany na: $email, prosimy o weryfikacje przez kliknięcie w link!";
        // Send registration confirmation link (verify.php)
        $to = $email;
        $subject = 'Weryfikacja konta ( StockExperience )';
        $message_body = '
        Witaj '.$first_name.',
        Dziękujemy za rejestracje!
        Kliknij w link aby aktywować konto:
        https://stockexperiencepl.000webhostapp.com/verify.php?email='.$email.'&hash='.$hash;  
        mail( $to, $subject, $message_body );
        echo "<script type='text/javascript'> document.location = '/profile.php'; </script>";
    } else {
        $_SESSION['message'] = 'Błąd rejestracji!';
        echo "<script type='text/javascript'> document.location = '/error.php'; </script>";
    }
}
?>