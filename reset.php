<?php
require 'db.php';
session_start();
// Make sure email and hash variables aren't empty
if( isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash']) )
{
    $email = $mysqli->escape_string($_GET['email']); 
    $hash = $mysqli->escape_string($_GET['hash']); 
    // Make sure user email with matching hash exist
    $result = $mysqli->query("SELECT * FROM users WHERE email='$email' AND hash='$hash'");
    if ( $result->num_rows == 0 )
    { 
        $_SESSION['message'] = "Zły adres do wyzerowania hasła!";
        echo "<script type='text/javascript'> document.location = '/error.php'; </script>";
    }
} else {
    $_SESSION['message'] = "Błąd weryfikacji, spróbuj ponownie!";
    echo "<script type='text/javascript'> document.location = '/error.php'; </script>";
}
?>

<!DOCTYPE html>
<html >
<head>
  <meta charset="UTF-8">
  <title>Wyzeruj Hasło</title>
  <?php include 'css/css.html'; ?>
</head>
<body>
    <div class="form">
          <h1>Podaj nowe hasło</h1>
          <form action="reset_password.php" method="post">
          <div class="field-wrap">
            <label>
              Nowe hasło<span class="req">*</span>
            </label>
            <input type="password"required name="newpassword" autocomplete="off"/>
          </div>
          <div class="field-wrap">
            <label>
              Potwierdź Nowe hasło<span class="req">*</span>
            </label>
            <input type="password"required name="confirmpassword" autocomplete="off"/>
          </div>
          <!-- This input field is needed, to get the email of the user from db, but we dont want user to change anything -->
          <input type="hidden" name="email" value="<?= $email ?>">    
          <input type="hidden" name="hash" value="<?= $hash ?>">    
          <button class="button button-block"/>Potwierdź</button>
          </form>
    </div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="js/index.js"></script>
</body>
</html>