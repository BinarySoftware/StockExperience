<?php
/* Displays user information and some useful messages - debugging only
function debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) )
        $output = implode( ',', $output);

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}
*/
error_reporting(0);
require 'db.php';
session_start();

// Check if user is logged in using the session variable
if ( $_SESSION['logged_in'] != 1 ) {
    $_SESSION['message'] = "Nie wyświetlimy danych bez zalogowania!";
    echo "<!DOCTYPE html><script type='text/javascript'> document.location = '../error.php'; </script>";
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