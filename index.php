<!-- <?php
require 'backend/db.php';
session_start();
?> -->

<!-- 
  index.php
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Start page, providing users with possibility to login and register
-->

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Zaloguj</title>
  <?php include 'css/css.html'; ?>
</head>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) { 
        require 'backend/login.php';
    } elseif (isset($_POST['register'])) {
        require 'backend/register.php';
    }
}
?>
<body>
  <div class="form">
    <table width=100%>
      <tr>
          <td width=110px><img src="icon.png"/ style="width:100px;height:100px;"></td>
          <td><h1>StockExperience Poland</h1></td> 
      </tr>
    </table>
    <h2 style="margin:10px 0">Witamy pasjonatów ekonomii!</h2>
    <h3 style="margin:10px 0">StockExperience to program edukacyjny, uczący inwestowania w papiery wartościowe. Jest też pierwszą aplikacją obsługującą indeksy GPW. Zapraszam do gry!</h3>
  </div>
  <div class="form">
      <ul class="tab-group">
        <li class="tab"><a href="#signup">Zarejestruj</a></li>
        <li class="tab active"><a href="#login">Zaloguj</a></li>
      </ul>
      <div class="tab-content">
         <div id="login">   
          <form action="index.php" method="post" autocomplete="off">
            <div class="field-wrap">
            <label>
              Email:<span class="req">*</span>
            </label>
            <input type="email" required autocomplete="off" name="email"/>
          </div>
          <div class="field-wrap">
            <label>
              Hasło:<span class="req">*</span>
            </label>
            <input type="password" required autocomplete="off" name="password"/>
          </div>
          <p class="forgot"><a href="forgot.php">Zapomniałeś hasła?</a></p>
          <button class="button button-block" name="login" />Zaloguj</button>
          </form>
        </div>
        <div id="signup">   
          <form action="index.php" method="post" autocomplete="off">
          <div class="top-row">
            <div class="field-wrap">
              <label>
                Imię<span class="req">*</span>
              </label>
              <input type="text" required autocomplete="off" name='firstname' />
            </div>
            <div class="field-wrap">
              <label>
                Nazwisko<span class="req">*</span>
              </label>
              <input type="text"required autocomplete="off" name='lastname' />
            </div>
          </div>
          <div class="field-wrap">
            <label>
              Email<span class="req">*</span>
            </label>
            <input type="email"required autocomplete="off" name='email' />
          </div>
          <div class="field-wrap">
            <label>
              Hasło<span class="req">*</span>
            </label>
            <input type="password"required autocomplete="off" name='password'/>
          </div>
          <p>Klikając "Zarejestruj" zgadzasz się na przetwarzanie Twoich danych osobowych oraz akceptujesz <a href="terms.php">regulamin</a></p>
          <button type="submit" class="button button-block" name="register" />Zarejestruj</button>
          </form>
        </div>  
      </div>
  </div> 
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
  <script src="js/index.js"></script>
  <h5 style="color:rgba(19, 35, 47, 0.9)">StockExperience <a href="https://github.com/BinarySoftware" style="color:rgba(19, 35, 47, 0.9)">Ⓒ2018 BinarySoftware</a> Wszelkie prawa zastrzeżone.</h5>
</body>
</html>