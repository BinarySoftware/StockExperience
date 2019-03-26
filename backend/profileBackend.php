<!--
  profileBackend.html
  StockExperience

  Edited by BinarySoftware on 07/03/2019.
  Copyright ©2019 BinarySoftware/Maciej Mikołajek. All rights reserved.

  Purpose: Backend part for setting up profile page
-->

<?php
error_reporting(0);
require 'db.php';
session_start();

// Check if user is logged in using the session variable
if ( $_SESSION['logged_in'] != 1 ) {
    $_SESSION['message'] = "Nie wyświetlimy danych bez zalogowania!";
    echo "<!DOCTYPE html><script type='text/javascript'> document.location = '../error.php'; </script>";
} else { //correct check, parse data
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
    foreach($Header as $NodeHeader) {
      $aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
    }

    $totalMoneyInStocks = 0;
    foreach($formattedActionsArray as &$index) {
      //Checking current price for the given key
      $key = array_search($index, $formattedActionsArray);
      $price = explode(PHP_EOL, $aDataTableHeaderHTML[$key])[1];

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
                  createModal("Pomyślnie zakupiono akcje");
                } else {
                  createModal("Problem z serwerem, transakcja odrzucona");
                }
            } else {
              createModal("Za mało środków na koncie, transakcja odrzucona");
                }
        } else {
          createModal("Brak wartości podanej w okienku");
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
                  createModal("Pomyślnie sprzedano posiadane akcje");
                } else {
                  createModal("Problem z serwerem, transakcja odrzucona");
                }
            } else {
              createModal("Za mało posiadanych akcji, transakcja odrzucona");
                }
        } else {
          createModal("Brak wartości podanej w okienku");
      }
    }
    //recalculate wallet
    $totalMoneyInStocks += floatval($price)*floatval($index[1]);
  }
}

function createModal( $message ) {
  echo '<!DOCTYPE html><div id="ActionSendWindow", class="modal">   
                            <div class="modal-content">
                              <span class="close">x</span>
                              <h3 style="color:#010101">'.$message.'</h3>
                            </div>
                          </div>';
}

?>