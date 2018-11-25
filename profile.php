<?php
require 'db.php';
function debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}
/* Displays user information and some useful messages */
session_start();
// Check if user is logged in using the session variable
if ( $_SESSION['logged_in'] != 1 ) {
    $_SESSION['message'] = "Nie wyświetlimy danych bez zalogowania!";
    echo "<!DOCTYPE html><script type='text/javascript'> document.location = '/error.php'; </script>";
}
else {
    $email = $mysqli->escape_string($_SESSION['email']);
    $result = $mysqli->query("SELECT * FROM users WHERE email='$email'");
    $user = $result->fetch_assoc();
        $email = $user['email'];
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        $active = $user['active'];
        $money = $user['money'];
        $actions_dict = $user['action_qty_dict'];
    $actions_ar = explode(',', $actions_dict); //Array of actions
    
    //This small chunk will get all data from "biznesradar", because there is no direct API for getting data from WSE/GPW :(
    $address = "https://widgets.biznesradar.pl/grid/";
    foreach ($actions_ar as &$value) {
      $address .=  explode('-', $value)[0]; //Index name
      $address .= "_t-"; //This is how "biznesradar" handles every query with URL
    }
    $htmlContent = file_get_contents($address);
    libxml_use_internal_errors(true);
    $DOM = new DOMDocument();
    $DOM->loadHTML($htmlContent);
    libxml_use_internal_errors(false);
    
    //This piece will loop through every single index and prepare how it's shown on the website
    $formattedActionsArray = [];
    foreach ($actions_ar as &$value) {
      array_push($formattedActionsArray, explode('-', $value)); // Array(Array([0]index, [1]quantity)) etc.
    }

    $Header = $DOM->getElementsByTagName('tr');
    //#Get header name of the table
    foreach($Header as $NodeHeader) 
    {
      $aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
    }
  
    $totalMoneyInStocks = 0;
    foreach($formattedActionsArray as &$index) {
      //Checking current price for the given key
      $key = array_search($index, $formattedActionsArray);
      if (strlen($index[0]) == 3) {
        $price = substr($aDataTableHeaderHTML[$key], 7, 5);
      } else {
        $price = substr($aDataTableHeaderHTML[$key], 10, 5);
      }

      //Buy index
      if ( isset( $_GET[$index[0].'k'] ) ) { // retrieve the form data by using the element's name attributes value as key 
        $ilosc = $_GET[$index[0]]; // display the results 
        if (floatval($ilosc) > 0) { //if the qty given is higher than 0
            if (floatval($price)*floatval($ilosc) <= floatval($money)) { //if person has enough money to buy it
                $money = floatval($money) - floatval($price)*floatval($ilosc);
                $index[1] = floatval($index[1]) + floatval($ilosc);
                $actions_ar[$key] = implode("-", $index);
                $actions_dict = implode(",", $actions_ar);
                $sql = "UPDATE users SET money='$money', action_qty_dict='$actions_dict' WHERE email='$email'";
                if ( $mysqli->query($sql) ) {
                    echo "<!DOCTYPE html><div id=\"myModal\", class=\"modal\">
                            <!-- Modal content -->
                            <div class=\"modal-content\">
                              <span class=\"close\">x</span>
                              <h3 style='color:#000000'>Pomyslnie zakupiono akcje</h3>
                            </div>
                          </div>";
                } else {
                    echo "<!DOCTYPE html><div id=\"myModal\", class=\"modal\">
                            <!-- Modal content -->
                            <div class=\"modal-content\">
                              <span class=\"close\">x</span>
                              <h3 style='color:#000000'>Problem z serwerem, transakcja odrzucona</h3>
                            </div>
                          </div>";
                }
            } else {
                    echo "<!DOCTYPE html><div id=\"myModal\", class=\"modal\">
                            <!-- Modal content -->
                            <div class=\"modal-content\">
                              <span class=\"close\">x</span>
                              <h3 style='color:#000000'>Za mało środków na koncie, transakcja odrzucona</h3>
                            </div>
                          </div>";
                }
        } else {
            echo "<!DOCTYPE html><div id=\"myModal\", class=\"modal\">
                    <!-- Modal content -->
                    <div class=\"modal-content\">
                      <span class=\"close\">x</span>
                      <h3 style='color:#000000'>Brak wartosci podanej w okienku</h3>
                    </div>
                  </div>";
        }
      //sell index
      } elseif ( isset( $_GET[$index[0].'s'] ) ) {
        $ilosc = $_GET[$index[0]];
        if (floatval($ilosc) > 0) {
            if (floatval($ilosc) <= floatval($index[1])) {
                $money = floatval($money) + floatval($price)*floatval($ilosc);
                $index[1] = floatval($index[1]) - floatval($ilosc);
                $actions_ar[$key] = implode("-", $index);
                $actions_dict = implode(",", $actions_ar);
                $sql = "UPDATE users SET money='$money', action_qty_dict='$actions_dict' WHERE email='$email'";
                if ( $mysqli->query($sql) ) {
                    echo "<!DOCTYPE html><div id=\"myModal\", class=\"modal\">
                            <!-- Modal content -->
                            <div class=\"modal-content\">
                              <span class=\"close\">x</span>
                              <h3 style='color:#000000'>Pomyslnie sprzedano akcje</h3>
                            </div>
                          </div>";
                } else {
                    echo "<!DOCTYPE html><div id=\"myModal\", class=\"modal\">
                            <!-- Modal content -->
                            <div class=\"modal-content\">
                              <span class=\"close\">x</span>
                              <h3 style='color:#000000'>Problem z serwerem, transakcja odrzucona</h3>
                            </div>
                          </div>";
                }
            } else {
                    echo "<!DOCTYPE html><div id=\"myModal\", class=\"modal\">
                            <!-- Modal content -->
                            <div class=\"modal-content\">
                              <span class=\"close\">x</span>
                              <h3 style='color:#000000'>Za mało akcji, transakcja odrzucona</h3>
                            </div>
                          </div>";
                }
        } else {
            echo "<!DOCTYPE html><div id=\"myModal\", class=\"modal\">
                    <!-- Modal content -->
                    <div class=\"modal-content\">
                      <span class=\"close\">x</span>
                      <h3 style='color:#000000'>Brak wartosci podanej w okienku</h3>
                    </div>
                  </div>";
      }
    }
    $totalMoneyInStocks += floatval($price)*floatval($index[1]);
  }
}
?>



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
                <th width=25%><h4>Twój portfel: <?= $money ?> Zł</h4></th> 
                <th><h4>Wartość twoich akcji: <?= $totalMoneyInStocks?> Zł</h4></th> 
                <th width=16% ><a href="logout.php"><button class="button-logout" name="logout"/>Wyloguj</button></a></th>
                <th width=9% ><a href="info.php"><button class="button-logout" name="info"/>Info</button></a></th>
                <th width=14% ><a href="profile.php"><button class="button-logout" name="odswiez"/>Odśwież</button></a></th>
            </tr>
          </table>
          <p>
          <?php 
          // Display message about account verification link only once
          if ( isset($_SESSION['message']) )
          {
              echo $_SESSION['message'];
              // Don't annoy the user with more messages upon page refresh
              unset( $_SESSION['message'] );
          }
          ?>
          </p>
          <?php
          // Keep reminding the user this account is not active, until they activate
          if ( !$active ){
              echo
              '<div class="info">
              Konto nie zweryfikowane, prosimy o kliknięcie linku w wiadomości weryfikacyjnej!
              </div>';
              die;
          } 
          ?>
          <table id="tableOfAllStocks" style="width:100%">
            <?php
              foreach($formattedActionsArray as &$index) {
                $key = array_search($index, $formattedActionsArray);
                $length = count($formattedActionsArray) + 1;

                if ($key == 0) {
                  echo '<tr>
                  <th style="width:30%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/'.$index[0].'_t"></iframe></th>
                    <form>
                      <th rowspan="'.$length.'" style="width:5%"></th>
                      <td style="color:#ffffff; width:20%">Masz: '.$index[1].'</td>
                      <th style="width:15%"><input type="text" name="'.$index[0].'" style="padding: 1px 7px;" placeholder="Ilosc"></th>
                      <th rowspan="'.$length.'" style="width:5%"></th>
                      <td><button class="button-buy" name="'.$index[0].'k">Kup</button></td>
                      <th rowspan="'.$length.'" style="width:5%"></th>
                      <td><button class="button-sell" name="'.$index[0].'s">Sprzedaj</button></td>
                    </form>
                  </tr>';
                } else {
                  echo '<tr>
                  <th style="width:30%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/'.$index[0].'_t"></iframe></th>
                    <form>
                      <td style="color:#ffffff; width:20%">Masz: '.$index[1].'</td>
                      <th style="width:15%"><input type="text" style="padding: 1px 7px;" name="'.$index[0].'" placeholder="Ilosc"></th>
                      <td><button class="button-buy" name="'.$index[0].'k">Kup</button></td>
                      <td><button class="button-sell" name="'.$index[0].'s">Sprzedaj</button></td>
                    </form>
                  </tr>';
                }
              }
            ?>
          </table>
    </div>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script>
    // Get the modal
    var modal = document.getElementById('myModal');
    modal.style.display = "block";

    // Get the <span> element that closes the modal. When the user clicks on <span> (x), close the modal
    var span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
        modal.style.display = "none";
    }

    // And when the user clicks anywhere outside of the modal, close it too
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
    <script src="js/index.js"></script>
    <h5 style="color:rgba(19, 35, 47, 0.9)">Ⓒ 2018 Maciej Mikołajek. Wszelkie prawa zastrzeżone. Wersja 2.0 Beta2</h5>
  </body>
</html>