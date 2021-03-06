<?php
require 'backend/profileBackend.php';
?>

<!-- 
  profile.php
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: The main page of the app, displaying profile, user data, container of stockKit
-->

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Witaj <?= $first_name.' '.$last_name ?></title>
  <?php include 'css/css.html'; ?>
</head>
<body>
  <div class="form">
    <table width=100%>
      <tr>
          <td width=110px><img src="icon.png"/ style="width:100px;height:100px;"></td>
        <td><h1>Witamy, <?php echo $first_name.' '.$last_name; ?></h1></td>
      <tr>
    </table>
    <table width=100%>
      <tr>
          <td width=27%><p style="margin:0">Twój portfel: <?= floor($money * 100) / 100 ?> Zł</p></td> 
          <th style="width:1%"></th>
          <td><p style="margin:0">Wartość twoich akcji: <?= floor($totalMoneyInStocks * 100) / 100?> Zł</p></td> 
          <th width=16% ><a href="logout.php"><button class="button-logout" name="logout"/>Wyloguj</button></a></th>
          <th width=9% ><a href="info.php"><button class="button-logout" name="info"/>Info</button></a></th>
          <th width=14% ><a href="profile.php"><button class="button-logout" name="odswiez"/>Odśwież</button></a></th>
      </tr>
    </table>
        <?php 
        // Display message about account verification 
        // Keep reminding the user this account is not active, until they activate
        if ( isset($_SESSION['message']) )
        {
            echo $_SESSION['message'];
            unset( $_SESSION['message'] );
        }
        if ( !$active ){
            echo
            '<div class="info">
            Konto nie zweryfikowane, prosimy o kliknięcie linku w wiadomości weryfikacyjnej!
            </div>';
            die;
        } 
        ?>
  </div>
  <div class="form">
    <table id="tableOfAllStocks" style="width:100%">
      <?php
        foreach($formattedActionsArray as &$index) {
          $key = array_search($index, $formattedActionsArray);
          $length = count($formattedActionsArray);

          $name = explode(PHP_EOL, $aDataTableHeaderHTML[$key])[0];
          $price = explode(PHP_EOL, $aDataTableHeaderHTML[$key])[1];
          $change = explode(PHP_EOL, $aDataTableHeaderHTML[$key])[2];
          
          $color = "red";
          if ($change >= 0) {
            $color = "lightgreen";
          }
          $lastUpdate = explode(PHP_EOL, $aDataTableHeaderHTML[$key])[3];

          $quantityOFIndexes = $index[1];

          if ($quantityOFIndexes != 0) {
            $valueInIndex = floor($quantityOFIndexes*$price * 100) / 100;
            $quantityAndMoneyInIndex = 'Masz: '.$quantityOFIndexes.' ('.$valueInIndex.' zł)';
          } else {
            $quantityAndMoneyInIndex = 'Masz: '.$quantityOFIndexes;
          }
          
          // Frontend part - prepare and display data as it should be displayed

          echo '<tr>
            <td style="color:#fafafa; width:8%"><a href="https://www.biznesradar.pl/notowania/'.$name.'">'.$name.'</a></td>';
          if ($key == 0) {
            echo '<th rowspan="'.$length.'" style="width:1%"></th>';
          }
          echo '<td style="color:#fafafa; width:9%">'.$price.'</td>';
          if ($key == 0) {
            echo '<th rowspan="'.$length.'" style="width:1%"></th>';
          }
          echo '<td style="width:8%; color:'.$color.'">'.$change.'</td>';
          if ($key == 0) {
            echo '<th rowspan="'.$length.'" style="width:1%"></th>';
          }
          echo '<td style="color:#fafafa; width:4%; font-size:9px; font-weight:100">'.$lastUpdate.'</td>
            <form>';
          if ($key == 0) {
            echo '<th rowspan="'.$length.'" style="width:1%"></th>';
          }
          echo '<td style="color:#fafafa; width:30%">'.$quantityAndMoneyInIndex.'</td>
            <th style="width:15%"><input type="text" name="'.$name.'" style="padding: 1px 7px;" placeholder="Ilosc"></th>';
          if ($key == 0) {
            echo '<th rowspan="'.$length.'" style="width:1%"></th>';
          }
          echo '<td><button class="button-buy" name="'.$name.'k">Kup</button></td>';
          if ($key == 0) {
            echo '<th rowspan="'.$length.'" style="width:1%"></th>';
          }
          echo '<td><button class="button-sell" name="'.$name.'s">Sprzedaj</button></td>
            </form>
            </tr>';
        }
      ?>
    </table>
  </div>
    <div class="form">
      <h1>Podsumowanie postępów od początku gry</h1>
      <?php
        echo '<p style="margin-bottom: 20px">Na początku miałeś 100 000 zł</p>';
        $totalMoney = (floor($money * 100) / 100) + (floor($totalMoneyInStocks * 100) / 100);
        if ($totalMoney > 100000) {
          echo '<p style="margin-bottom: 20px">Dotychczas zarobiłeś '.(floor(($totalMoney-100000) * 100) / 100).', co przekłada się na wzrost o '.((floor(($totalMoney-100000) * 100) / 100)/1000).'%</p>
          <h3>Graj tak dalej!</h3>';
        } else {
          echo '<p style="margin-bottom: 20px">Dotychczas straciłeś '.(floor(($totalMoney-100000) * -100) / 100).', co przekłada się na spadek o '.((floor(($totalMoney-100000) * 100) / 100)/1000).'%</p>
          <h3>Zmień taktykę, badaj rynek</h3>';
        }
      ?>
    </div>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script>
    // Script for running the pop-up informing about buying/selling
    var modal = document.getElementById('ActionSendWindow');
    var span = document.getElementsByClassName("close")[0];

    if (modal != null) {
      modal.style.display = "block";
      span.onclick = function() {
          modal.style.display = "none";
      }

      window.onclick = function(event) {
          if (event.target == modal) {
              modal.style.display = "none";
          }
      }
   }
    </script>
    <script src="js/index.js"></script>
    <h5 style="color:rgba(19, 35, 47, 0.9)">StockExperience <a href="https://github.com/BinarySoftware" style="color:rgba(19, 35, 47, 0.9)">Ⓒ2019 BinarySoftware</a> Wszelkie prawa zastrzeżone.</h5>
  </body>
</html>