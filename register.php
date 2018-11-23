<?php
/* Registration process, inserts user info into the database 
   and sends account confirmation email message
 */
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
$result = $mysqli->query("SELECT * FROM users WHERE email='$email'") or die($mysqli->error());
// We know user email exists if the rows returned are more than 0
if ( $result->num_rows > 0 ) {
    $_SESSION['message'] = 'Uzytkownik z takim mailem już istnieje!';
    echo "<script type='text/javascript'> document.location = '/error.php'; </script>";
}
else { // Email doesn't already exist in a database, proceed...
    // active is 0 by DEFAULT (no need to include it here)
    $sql = "INSERT INTO users (first_name, last_name, email, password, hash, money, action_qty_dict) " 
            . "VALUES ('$first_name','$last_name','$email','$password','$hash','100000','KGH-0,PKO-0,PKN-0,PZU-0,JSW-0,CCC-0,DNP-0,CDR-0,LTS-0,ALR-0,TPE-0,PEO-0,BZW-0,PGN-0,GBK-0,ENG-0,PGE-0,ENA-0,EUR-0,KRU-0,PKP-0,LPP-0,PLY-0,MIL-0,CPS-0,OPL-0,MBK-0,EAT-0,BMC-0,VST-0,GTC-0,BFT-0,MRB-0,11B-0,MAB-0,EURPLN-0,CHFPLN-0,USDPLN-0,GBPPLN-0')";
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