<?php
/* Reset your password form, sends reset.php password link */
require 'db.php';
session_start();
// Check if form submitted with method="post"
if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) 
{   
    $email = $mysqli->escape_string($_POST['email']);
    $result = $mysqli->query("SELECT * FROM users WHERE email='$email'");
    if ( $result->num_rows == 0 ) // User doesn't exist
    { 
        $_SESSION['message'] = "Użytkownik z takim adresem nie istnieje!";
        echo "<script type='text/javascript'> document.location = '/error.php'; </script>";
    }
    else { // User exists (num_rows != 0)
        $user = $result->fetch_assoc(); // $user becomes array with user data
        $email = $user['email'];
        $hash = $user['hash'];
        $first_name = $user['first_name'];
        // Session message to display on success.php
        $_SESSION['message'] = "<p>Proszę sprawdzić mail <span>$email</span>"
        . " gdzie został wysłany link do ukończenia zerowania hasła!</p>";
        // Send registration confirmation link (reset.php)
        $to      = $email;
        $subject = 'Zerowanie hasła ( StockExperience )';
        $message_body = '
        Witaj '.$first_name.',
        Prosiłeś o możliwość wyzerowania hasła, oto twój link:
        http://stockexperiencepl.000webhostapp.com/reset.php?email='.$email.'&hash='.$hash;  
        mail($to, $subject, $message_body);
        echo "<script type='text/javascript'> document.location = '/success.php'; </script>";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
  <title>Wyzeruj hasło</title>
  <?php include 'css/css.html'; ?>
</head>
<body>
  <div class="form">
    <h1>Wyzeruj hasło</h1>
    <form action="forgot.php" method="post">
     <div class="field-wrap">
      <label>
        Adres email<span class="req">*</span>
      </label>
      <input type="email"required autocomplete="off" name="email"/>
    </div>
    <button class="button button-block"/>Zeruj hasło</button>
    </form>
  </div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="js/index.js"></script>
</body>
</html>