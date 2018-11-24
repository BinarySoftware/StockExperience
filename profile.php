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
    //print_r($formattedActionsArray);
    
    // TODO : Replace with foreach loop
    /*$KGH = explode('-', $actions_ar[0]);
    $PKO = explode('-', $actions_ar[1]);
    $PKN = explode('-', $actions_ar[2]);
    $PZU = explode('-', $actions_ar[3]);
    $JSW = explode('-', $actions_ar[4]);
    $CCC = explode('-', $actions_ar[5]);
    $DNP = explode('-', $actions_ar[6]);
    $CDR = explode('-', $actions_ar[7]);
    $LTS = explode('-', $actions_ar[8]);
    $ALR = explode('-', $actions_ar[9]);
    $TPE = explode('-', $actions_ar[10]);
    $PEO = explode('-', $actions_ar[11]);
    $BZW = explode('-', $actions_ar[12]);
    $PGN = explode('-', $actions_ar[13]);
    $GBK = explode('-', $actions_ar[14]);
    $ENG = explode('-', $actions_ar[15]);
    $PGE = explode('-', $actions_ar[16]);
    $ENA = explode('-', $actions_ar[17]);
    $EUR = explode('-', $actions_ar[18]);
    $KRU = explode('-', $actions_ar[19]);
    $PKP = explode('-', $actions_ar[20]);
    $LPP = explode('-', $actions_ar[21]);
    $PLY = explode('-', $actions_ar[22]);
    $MIL = explode('-', $actions_ar[23]);
    $CPS = explode('-', $actions_ar[24]);
    $OPL = explode('-', $actions_ar[25]);
    $MBK = explode('-', $actions_ar[26]);
    $EAT = explode('-', $actions_ar[27]);
    $BMC = explode('-', $actions_ar[28]);
    $VST = explode('-', $actions_ar[29]);
    $GTC = explode('-', $actions_ar[30]);
    $BFT = explode('-', $actions_ar[31]);
    $MRB = explode('-', $actions_ar[32]);
    $llB = explode('-', $actions_ar[33]);
    $MAB = explode('-', $actions_ar[34]);
    $EURPLN = explode('-', $actions_ar[35]);
    $CHFPLN = explode('-', $actions_ar[36]);
    $USDPLN = explode('-', $actions_ar[37]);
    $GBPPLN = explode('-', $actions_ar[38]);

	  $KGHp = substr($aDataTableHeaderHTML[0], 7, 5);
    $PKOp = substr($aDataTableHeaderHTML[1], 7, 5);
    $PKNp = substr($aDataTableHeaderHTML[2], 7, 5);
    $PZUp = substr($aDataTableHeaderHTML[3], 7, 5);
    $JSWp = substr($aDataTableHeaderHTML[4], 7, 5);
    $CCCp = substr($aDataTableHeaderHTML[5], 7, 5);
    $DNPp = substr($aDataTableHeaderHTML[6], 7, 5);
    $CDRp = substr($aDataTableHeaderHTML[7], 7, 5);
    $LTSp = substr($aDataTableHeaderHTML[8], 7, 5);
    $ALRp = substr($aDataTableHeaderHTML[9], 7, 5);
    $TPEp = substr($aDataTableHeaderHTML[10], 7, 5);
    $PEOp = substr($aDataTableHeaderHTML[11], 7, 5);
    $BZWp = substr($aDataTableHeaderHTML[12], 7, 5);
    $PGNp = substr($aDataTableHeaderHTML[13], 7, 5);
    $GBKp = substr($aDataTableHeaderHTML[14], 7, 5);
    $ENGp = substr($aDataTableHeaderHTML[15], 7, 5);
    $PGEp = substr($aDataTableHeaderHTML[16], 7, 5);
    $ENAp = substr($aDataTableHeaderHTML[17], 7, 5);
    $EURp = substr($aDataTableHeaderHTML[18], 7, 5);
    $KRUp = substr($aDataTableHeaderHTML[19], 7, 5);
    $PKPp = substr($aDataTableHeaderHTML[20], 7, 5);
    $LPPp = substr($aDataTableHeaderHTML[21], 7, 5);
    $PLYp = substr($aDataTableHeaderHTML[22], 7, 5);
    $MILp = substr($aDataTableHeaderHTML[23], 7, 5);
    $CPSp = substr($aDataTableHeaderHTML[24], 7, 5);
    $OPLp = substr($aDataTableHeaderHTML[25], 7, 5);
    $MBKp = substr($aDataTableHeaderHTML[26], 7, 5);
    $EATp = substr($aDataTableHeaderHTML[27], 7, 5);
    $BMCp = substr($aDataTableHeaderHTML[28], 7, 5);
    $VSTp = substr($aDataTableHeaderHTML[29], 7, 5);
    $GTCp = substr($aDataTableHeaderHTML[30], 7, 5);
    $BFTp = substr($aDataTableHeaderHTML[31], 7, 5);
    $MRBp = substr($aDataTableHeaderHTML[32], 7, 5);
    $llBp = substr($aDataTableHeaderHTML[33], 7, 5);
    $MABp = substr($aDataTableHeaderHTML[34], 7, 5);
    $EURPLNp = substr($aDataTableHeaderHTML[35], 10, 5);
    $CHFPLNp = substr($aDataTableHeaderHTML[36], 10, 5);
    $USDPLNp = substr($aDataTableHeaderHTML[37], 10, 5);
    $GBPPLNp = substr($aDataTableHeaderHTML[38], 10, 5);
    
    if ( isset( $_GET['KGHk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['KGH']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($KGHp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($KGHp)*floatval($ilosc);
            $KGH[1] = floatval($KGH[1]) + floatval($ilosc);
            $actions_ar[0] = implode("-", $KGH);
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
    } elseif ( isset( $_GET['KGHs'] ) ) {
        $ilosc = $_GET['KGH'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($KGH[1])) {
            $money = floatval($money) + floatval($KGHp)*floatval($ilosc);
            $KGH[1] = floatval($KGH[1]) - floatval($ilosc);
            $actions_ar[0] = implode("-", $KGH);
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
    } elseif ( isset( $_GET['PKOk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['PKO']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($PKOp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($PKOp)*floatval($ilosc);
            $PKO[1] = floatval($PKO[1]) + floatval($ilosc);
            $actions_ar[1] = implode("-", $PKO);
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
    } elseif ( isset( $_GET['PKOs'] ) ) {
        $ilosc = $_GET['PKO'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($PKO[1])) {
            $money = floatval($money) + floatval($PKOp)*floatval($ilosc);
            $PKO[1] = floatval($PKO[1]) - floatval($ilosc);
            $actions_ar[1] = implode("-", $PKO);
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
    } elseif ( isset( $_GET['PKNk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['PKN']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($PKNp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($PKNp)*floatval($ilosc);
            $PKN[1] = floatval($PKN[1]) + floatval($ilosc);
            $actions_ar[2] = implode("-", $PKN);
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
    } elseif ( isset( $_GET['PKNs'] ) ) {
        $ilosc = $_GET['PKN'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($PKN[1])) {
            $money = floatval($money) + floatval($PKNp)*floatval($ilosc);
            $PKN[1] = floatval($PKN[1]) - floatval($ilosc);
            $actions_ar[2] = implode("-", $PKN);
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
    } elseif ( isset( $_GET['PZUk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['PZU']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($PZUp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($PZUp)*floatval($ilosc);
            $PZU[1] = floatval($PZU[1]) + floatval($ilosc);
            $actions_ar[3] = implode("-", $PZU);
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
    } elseif ( isset( $_GET['PZUs'] ) ) {
        $ilosc = $_GET['PZU'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($PZU[1])) {
            $money = floatval($money) + floatval($PZUp)*floatval($ilosc);
            $PZU[1] = floatval($PZU[1]) - floatval($ilosc);
            $actions_ar[3] = implode("-", $PZU);
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
    } elseif ( isset( $_GET['JSWk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['JSW']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($JSWp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($JSWp)*floatval($ilosc);
            $JSW[1] = floatval($JSW[1]) + floatval($ilosc);
            $actions_ar[4] = implode("-", $JSW);
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
    } elseif ( isset( $_GET['JSWs'] ) ) {
        $ilosc = $_GET['JSW'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($JSW[1])) {
            $money = floatval($money) + floatval($JSWp)*floatval($ilosc);
            $JSW[1] = floatval($JSW[1]) - floatval($ilosc);
            $actions_ar[4] = implode("-", $JSW);
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
    } elseif ( isset( $_GET['CCCk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['CCC']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($CCCp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($CCCp)*floatval($ilosc);
            $CCC[1] = floatval($CCC[1]) + floatval($ilosc);
            $actions_ar[5] = implode("-", $CCC);
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
    } elseif ( isset( $_GET['CCCs'] ) ) {
        $ilosc = $_GET['CCC'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($CCC[1])) {
            $money = floatval($money) + floatval($CCCp)*floatval($ilosc);
            $CCC[1] = floatval($CCC[1]) - floatval($ilosc);
            $actions_ar[5] = implode("-", $CCC);
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
    } elseif ( isset( $_GET['DNPk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['DNP']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($DNPp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($DNPp)*floatval($ilosc);
            $DNP[1] = floatval($DNP[1]) + floatval($ilosc);
            $actions_ar[6] = implode("-", $DNP);
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
    } elseif ( isset( $_GET['DNPs'] ) ) {
        $ilosc = $_GET['DNP'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($DNP[1])) {
            $money = floatval($money) + floatval($DNPp)*floatval($ilosc);
            $DNP[1] = floatval($DNP[1]) - floatval($ilosc);
            $actions_ar[6] = implode("-", $DNP);
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
    } elseif ( isset( $_GET['CDRk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['CDR']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($CDRp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($CDRp)*floatval($ilosc);
            $CDR[1] = floatval($CDR[1]) + floatval($ilosc);
            $actions_ar[7] = implode("-", $CDR);
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
    } elseif ( isset( $_GET['CDRs'] ) ) {
        $ilosc = $_GET['CDR'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($CDR[1])) {
            $money = floatval($money) + floatval($CDRp)*floatval($ilosc);
            $CDR[1] = floatval($CDR[1]) - floatval($ilosc);
            $actions_ar[7] = implode("-", $CDR);
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
    } elseif ( isset( $_GET['LTSk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['LTS']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($LTSp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($LTSp)*floatval($ilosc);
            $LTS[1] = floatval($LTS[1]) + floatval($ilosc);
            $actions_ar[8] = implode("-", $LTS);
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
    } elseif ( isset( $_GET['LTSs'] ) ) {
        $ilosc = $_GET['LTS'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($LTS[1])) {
            $money = floatval($money) + floatval($LTSp)*floatval($ilosc);
            $LTS[1] = floatval($LTS[1]) - floatval($ilosc);
            $actions_ar[8] = implode("-", $LTS);
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
    } elseif ( isset( $_GET['ALRk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['ALR']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($ALRp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($ALRp)*floatval($ilosc);
            $ALR[1] = floatval($ALR[1]) + floatval($ilosc);
            $actions_ar[9] = implode("-", $ALR);
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
    } elseif ( isset( $_GET['ALRs'] ) ) {
        $ilosc = $_GET['ALR'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($ALR[1])) {
            $money = floatval($money) + floatval($ALRp)*floatval($ilosc);
            $ALR[1] = floatval($ALR[1]) - floatval($ilosc);
            $actions_ar[9] = implode("-", $ALR);
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
    } elseif ( isset( $_GET['TPEk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['TPE']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($TPEp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($TPEp)*floatval($ilosc);
            $TPE[1] = floatval($TPE[1]) + floatval($ilosc);
            $actions_ar[10] = implode("-", $TPE);
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
    } elseif ( isset( $_GET['TPEs'] ) ) {
        $ilosc = $_GET['TPE'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($TPE[1])) {
            $money = floatval($money) + floatval($TPEp)*floatval($ilosc);
            $TPE[1] = floatval($TPE[1]) - floatval($ilosc);
            $actions_ar[10] = implode("-", $TPE);
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
    } elseif ( isset( $_GET['PEOk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['PEO']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($PEOp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($PEOp)*floatval($ilosc);
            $PEO[1] = floatval($PEO[1]) + floatval($ilosc);
            $actions_ar[11] = implode("-", $PEO);
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
    } elseif ( isset( $_GET['PEOs'] ) ) {
        $ilosc = $_GET['PEO'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($PEO[1])) {
            $money = floatval($money) + floatval($PEOp)*floatval($ilosc);
            $PEO[1] = floatval($PEO[1]) - floatval($ilosc);
            $actions_ar[11] = implode("-", $PEO);
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
    } elseif ( isset( $_GET['BZWk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['BZW']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($BZWp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($BZWp)*floatval($ilosc);
            $BZW[1] = floatval($BZW[1]) + floatval($ilosc);
            $actions_ar[12] = implode("-", $BZW);
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
    } elseif ( isset( $_GET['BZWs'] ) ) {
        $ilosc = $_GET['BZW'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($BZW[1])) {
            $money = floatval($money) + floatval($BZWp)*floatval($ilosc);
            $BZW[1] = floatval($BZW[1]) - floatval($ilosc);
            $actions_ar[12] = implode("-", $BZW);
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
    } elseif ( isset( $_GET['PGNk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['PGN']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($PGNp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($PGNp)*floatval($ilosc);
            $PGN[1] = floatval($PGN[1]) + floatval($ilosc);
            $actions_ar[13] = implode("-", $PGN);
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
    } elseif ( isset( $_GET['PGNs'] ) ) {
        $ilosc = $_GET['PGN'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($PGN[1])) {
            $money = floatval($money) + floatval($PGNp)*floatval($ilosc);
            $PGN[1] = floatval($PGN[1]) - floatval($ilosc);
            $actions_ar[13] = implode("-", $PGN);
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
    } elseif ( isset( $_GET['GBKk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['GBK']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($GBKp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($GBKp)*floatval($ilosc);
            $GBK[1] = floatval($GBK[1]) + floatval($ilosc);
            $actions_ar[14] = implode("-", $GBK);
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
    } elseif ( isset( $_GET['GBKs'] ) ) {
        $ilosc = $_GET['GBK'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($GBK[1])) {
            $money = floatval($money) + floatval($GBKp)*floatval($ilosc);
            $GBK[1] = floatval($GBK[1]) - floatval($ilosc);
            $actions_ar[14] = implode("-", $GBK);
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
    } elseif ( isset( $_GET['ENGk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['ENG']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($ENGp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($ENGp)*floatval($ilosc);
            $ENG[1] = floatval($ENG[1]) + floatval($ilosc);
            $actions_ar[15] = implode("-", $ENG);
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
    } elseif ( isset( $_GET['ENGs'] ) ) {
        $ilosc = $_GET['ENG'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($ENG[1])) {
            $money = floatval($money) + floatval($ENGp)*floatval($ilosc);
            $ENG[1] = floatval($ENG[1]) - floatval($ilosc);
            $actions_ar[15] = implode("-", $ENG);
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
    } elseif ( isset( $_GET['PGEk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['PGE']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($PGEp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($PGEp)*floatval($ilosc);
            $PGE[1] = floatval($PGE[1]) + floatval($ilosc);
            $actions_ar[16] = implode("-", $PGE);
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
    } elseif ( isset( $_GET['PGEs'] ) ) {
        $ilosc = $_GET['PGE'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($PGE[1])) {
            $money = floatval($money) + floatval($PGEp)*floatval($ilosc);
            $PGE[1] = floatval($PGE[1]) - floatval($ilosc);
            $actions_ar[16] = implode("-", $PGE);
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
    } elseif ( isset( $_GET['ENAk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['ENA']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($ALRp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($ENAp)*floatval($ilosc);
            $ENA[1] = floatval($ENA[1]) + floatval($ilosc);
            $actions_ar[17] = implode("-", $ENA);
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
    } elseif ( isset( $_GET['ENAs'] ) ) {
        $ilosc = $_GET['ENA'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($ENA[1])) {
            $money = floatval($money) + floatval($ENAp)*floatval($ilosc);
            $ENA[1] = floatval($ENA[1]) - floatval($ilosc);
            $actions_ar[17] = implode("-", $ENA);
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
    } elseif ( isset( $_GET['EURk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['EUR']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($EURp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($EURp)*floatval($ilosc);
            $EUR[1] = floatval($EUR[1]) + floatval($ilosc);
            $actions_ar[18] = implode("-", $EUR);
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
    } elseif ( isset( $_GET['EURs'] ) ) {
        $ilosc = $_GET['EUR'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($EUR[1])) {
            $money = floatval($money) + floatval($EURp)*floatval($ilosc);
            $EUR[1] = floatval($EUR[1]) - floatval($ilosc);
            $actions_ar[18] = implode("-", $EUR);
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
    } elseif ( isset( $_GET['KRUk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['KRU']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($KRUp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($KRUp)*floatval($ilosc);
            $KRU[1] = floatval($KRU[1]) + floatval($ilosc);
            $actions_ar[19] = implode("-", $KRU);
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
    } elseif ( isset( $_GET['KRUs'] ) ) {
        $ilosc = $_GET['KRU'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($KRU[1])) {
            $money = floatval($money) + floatval($KRUp)*floatval($ilosc);
            $KRU[1] = floatval($KRU[1]) - floatval($ilosc);
            $actions_ar[19] = implode("-", $KRU);
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
    } if ( isset( $_GET['PKPk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['PKP']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($PKPp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($PKPp)*floatval($ilosc);
            $PKP[1] = floatval($PKP[1]) + floatval($ilosc);
            $actions_ar[20] = implode("-", $PKP);
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
    } elseif ( isset( $_GET['PKPs'] ) ) {
        $ilosc = $_GET['PKP'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($PKP[1])) {
            $money = floatval($money) + floatval($PKPp)*floatval($ilosc);
            $PKP[1] = floatval($PKP[1]) - floatval($ilosc);
            $actions_ar[20] = implode("-", $PKP);
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
    } elseif ( isset( $_GET['LPPk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['LPP']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($LPPp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($LPPp)*floatval($ilosc);
            $LPP[1] = floatval($LPP[1]) + floatval($ilosc);
            $actions_ar[21] = implode("-", $LPP);
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
    } elseif ( isset( $_GET['LPPs'] ) ) {
        $ilosc = $_GET['LPP'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($LPP[1])) {
            $money = floatval($money) + floatval($LPPp)*floatval($ilosc);
            $LPP[1] = floatval($LPP[1]) - floatval($ilosc);
            $actions_ar[21] = implode("-", $LPP);
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
    } elseif ( isset( $_GET['PLYk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['PLY']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($PLYp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($PLYp)*floatval($ilosc);
            $PLY[1] = floatval($PLY[1]) + floatval($ilosc);
            $actions_ar[22] = implode("-", $PLY);
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
    } elseif ( isset( $_GET['PLYs'] ) ) {
        $ilosc = $_GET['PLY'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($PLY[1])) {
            $money = floatval($money) + floatval($PLYp)*floatval($ilosc);
            $PLY[1] = floatval($PLY[1]) - floatval($ilosc);
            $actions_ar[22] = implode("-", $PLY);
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
    } elseif ( isset( $_GET['MILk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['MIL']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($MILp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($MILp)*floatval($ilosc);
            $MIL[1] = floatval($MIL[1]) + floatval($ilosc);
            $actions_ar[23] = implode("-", $MIL);
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
    } elseif ( isset( $_GET['MILs'] ) ) {
        $ilosc = $_GET['MIL'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($MIL[1])) {
            $money = floatval($money) + floatval($MILp)*floatval($ilosc);
            $MIL[1] = floatval($MIL[1]) - floatval($ilosc);
            $actions_ar[23] = implode("-", $MIL);
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
    } elseif ( isset( $_GET['CPSk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['CPS']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($CPSp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($CPSp)*floatval($ilosc);
            $CPS[1] = floatval($CPS[1]) + floatval($ilosc);
            $actions_ar[24] = implode("-", $CPS);
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
    } elseif ( isset( $_GET['CPSs'] ) ) {
        $ilosc = $_GET['CPS'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($CPS[1])) {
            $money = floatval($money) + floatval($CPSp)*floatval($ilosc);
            $CPS[1] = floatval($CPS[1]) - floatval($ilosc);
            $actions_ar[24] = implode("-", $CPS);
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
    } elseif ( isset( $_GET['OPLk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['OPL']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($OPLp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($OPLp)*floatval($ilosc);
            $OPL[1] = floatval($OPL[1]) + floatval($ilosc);
            $actions_ar[25] = implode("-", $OPL);
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
    } elseif ( isset( $_GET['OPLs'] ) ) {
        $ilosc = $_GET['OPL'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($OPL[1])) {
            $money = floatval($money) + floatval($OPLp)*floatval($ilosc);
            $OPL[1] = floatval($OPL[1]) - floatval($ilosc);
            $actions_ar[25] = implode("-", $OPL);
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
    } elseif ( isset( $_GET['MBKk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['MBK']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($MBKp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($MBKp)*floatval($ilosc);
            $MBK[1] = floatval($MBK[1]) + floatval($ilosc);
            $actions_ar[26] = implode("-", $MBK);
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
    } elseif ( isset( $_GET['MBKs'] ) ) {
        $ilosc = $_GET['MBK'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($MBK[1])) {
            $money = floatval($money) + floatval($MBKp)*floatval($ilosc);
            $MBK[1] = floatval($MBK[1]) - floatval($ilosc);
            $actions_ar[26] = implode("-", $MBK);
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
    } elseif ( isset( $_GET['EATk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['EAT']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($EATp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($EATp)*floatval($ilosc);
            $EAT[1] = floatval($EAT[1]) + floatval($ilosc);
            $actions_ar[27] = implode("-", $EAT);
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
    } elseif ( isset( $_GET['EATs'] ) ) {
        $ilosc = $_GET['EAT'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($EAT[1])) {
            $money = floatval($money) + floatval($EATp)*floatval($ilosc);
            $EAT[1] = floatval($EAT[1]) - floatval($ilosc);
            $actions_ar[27] = implode("-", $EAT);
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
    } elseif ( isset( $_GET['BMCk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['BMC']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($BMCp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($BMCp)*floatval($ilosc);
            $BMC[1] = floatval($BMC[1]) + floatval($ilosc);
            $actions_ar[28] = implode("-", $BMC);
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
    } elseif ( isset( $_GET['BMCs'] ) ) {
        $ilosc = $_GET['BMC'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($BMC[1])) {
            $money = floatval($money) + floatval($BMCp)*floatval($ilosc);
            $BMC[1] = floatval($BMC[1]) - floatval($ilosc);
            $actions_ar[28] = implode("-", $BMC);
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
    } elseif ( isset( $_GET['VSTk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['VST']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($VSTp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($VSTp)*floatval($ilosc);
            $VST[1] = floatval($VST[1]) + floatval($ilosc);
            $actions_ar[29] = implode("-", $VST);
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
    } elseif ( isset( $_GET['VSTs'] ) ) {
        $ilosc = $_GET['VST'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($VST[1])) {
            $money = floatval($money) + floatval($VSTp)*floatval($ilosc);
            $VST[1] = floatval($VST[1]) - floatval($ilosc);
            $actions_ar[29] = implode("-", $VST);
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
    } elseif ( isset( $_GET['GTCk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['GTC']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($GTCp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($GTCp)*floatval($ilosc);
            $GTC[1] = floatval($GTC[1]) + floatval($ilosc);
            $actions_ar[30] = implode("-", $GTC);
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
    } elseif ( isset( $_GET['GTCs'] ) ) {
        $ilosc = $_GET['GTC'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($GTC[1])) {
            $money = floatval($money) + floatval($GTCp)*floatval($ilosc);
            $GTC[1] = floatval($GTC[1]) - floatval($ilosc);
            $actions_ar[30] = implode("-", $GTC);
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
    } elseif ( isset( $_GET['BFTk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['BFT']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($BFTp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($BFTp)*floatval($ilosc);
            $BFT[1] = floatval($BFT[1]) + floatval($ilosc);
            $actions_ar[31] = implode("-", $BFT);
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
    } elseif ( isset( $_GET['BFTs'] ) ) {
        $ilosc = $_GET['BFT'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($BFT[1])) {
            $money = floatval($money) + floatval($BFTp)*floatval($ilosc);
            $BFT[1] = floatval($BFT[1]) - floatval($ilosc);
            $actions_ar[31] = implode("-", $BFT);
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
    } elseif ( isset( $_GET['MRBk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['MRB']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($MRBp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($MRBp)*floatval($ilosc);
            $MRB[1] = floatval($MRB[1]) + floatval($ilosc);
            $actions_ar[32] = implode("-", $MRB);
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
    } elseif ( isset( $_GET['MRBs'] ) ) {
        $ilosc = $_GET['MRB'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($MRB[1])) {
            $money = floatval($money) + floatval($MRBp)*floatval($ilosc);
            $MRB[1] = floatval($MRB[1]) - floatval($ilosc);
            $actions_ar[32] = implode("-", $MRB);
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
    } elseif ( isset( $_GET['llBk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['llB']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($llBp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($llBp)*floatval($ilosc);
            $llB[1] = floatval($llB[1]) + floatval($ilosc);
            $actions_ar[33] = implode("-", $llB);
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
    } elseif ( isset( $_GET['llBs'] ) ) {
        $ilosc = $_GET['llB'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($llB[1])) {
            $money = floatval($money) + floatval($llBp)*floatval($ilosc);
            $llB[1] = floatval($llB[1]) - floatval($ilosc);
            $actions_ar[33] = implode("-", $llB);
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
    } elseif ( isset( $_GET['MABk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['MAB']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($MABp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($MABp)*floatval($ilosc);
            $MAB[1] = floatval($MAB[1]) + floatval($ilosc);
            $actions_ar[34] = implode("-", $MAB);
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
    } elseif ( isset( $_GET['MABs'] ) ) {
        $ilosc = $_GET['MAB'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($MAB[1])) {
            $money = floatval($money) + floatval($MABp)*floatval($ilosc);
            $MAB[1] = floatval($MAB[1]) - floatval($ilosc);
            $actions_ar[34] = implode("-", $MAB);
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
    } elseif ( isset( $_GET['EURPLNk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['EURPLN']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($EURPLNp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($EURPLNp)*floatval($ilosc);
            $EURPLN[1] = floatval($EURPLN[1]) + floatval($ilosc);
            $actions_ar[35] = implode("-", $EURPLN);
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
    } elseif ( isset( $_GET['EURPLNs'] ) ) {
        $ilosc = $_GET['EURPLN'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($EURPLN[1])) {
            $money = floatval($money) + floatval($EURPLNp)*floatval($ilosc);
            $EURPLN[1] = floatval($EURPLN[1]) - floatval($ilosc);
            $actions_ar[35] = implode("-", $EURPLN);
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
    } elseif ( isset( $_GET['CHFPLNk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['CHFPLN']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($CHFPLNp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($CHFPLNp)*floatval($ilosc);
            $CHFPLN[1] = floatval($CHFPLN[1]) + floatval($ilosc);
            $actions_ar[36] = implode("-", $CHFPLN);
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
    } elseif ( isset( $_GET['CHFPLNs'] ) ) {
        $ilosc = $_GET['CHFPLN'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($CHFPLN[1])) {
            $money = floatval($money) + floatval($CHFPLNp)*floatval($ilosc);
            $CHFPLN[1] = floatval($CHFPLN[1]) - floatval($ilosc);
            $actions_ar[36] = implode("-", $CHFPLN);
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
    } elseif ( isset( $_GET['USDPLNk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['USDPLN']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($USDPLNp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($USDPLNp)*floatval($ilosc);
            $USDPLN[1] = floatval($USDPLN[1]) + floatval($ilosc);
            $actions_ar[37] = implode("-", $USDPLN);
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
    } elseif ( isset( $_GET['USDPLNs'] ) ) {
        $ilosc = $_GET['USDPLN'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($USDPLN[1])) {
            $money = floatval($money) + floatval($USDPLNp)*floatval($ilosc);
            $USDPLN[1] = floatval($USDPLN[1]) - floatval($ilosc);
            $actions_ar[37] = implode("-", $USDPLN);
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
    } elseif ( isset( $_GET['GBPPLNk'] ) ) { // retrieve the form data by using the element's name attributes value as key 
    $ilosc = $_GET['GBPPLN']; // display the results 
    if (floatval($ilosc) > 0) {
        if (floatval($GBPPLNp)*floatval($ilosc) <= floatval($money)) {
            $money = floatval($money) - floatval($GBPPLNp)*floatval($ilosc);
            $GBPPLN[1] = floatval($GBPPLN[1]) + floatval($ilosc);
            $actions_ar[38] = implode("-", $GBPPLN);
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
    } elseif ( isset( $_GET['GBPPLNs'] ) ) {
        $ilosc = $_GET['GBPPLN'];
    if (floatval($ilosc) > 0) {
        if (floatval($ilosc) <= floatval($GBPPLN[1])) {
            $money = floatval($money) + floatval($GBPPLNp)*floatval($ilosc);
            $GBPPLN[1] = floatval($GBPPLN[1]) - floatval($ilosc);
            $actions_ar[38] = implode("-", $GBPPLN);
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
    */
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
                <tr>
                <th style="width:25%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/KGH_t"></iframe></th>
                <form>
                    <th rowspan="39" style="width:5%"></th>
                    <td style="color:#ffffff; width:20%">Masz: <?php echo $KGH[1]; ?></td>
                <th style="width:15%"><input type="text" name="KGH" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                <th rowspan="39" style="width:5%"></th>
                    <td><button class="button-buy" name="KGHk">Kup</button></td>
                <th rowspan="39" style="width:5%"></th>
                    <td><button class="button-sell" name="KGHs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/PKO_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $PKO[1] ?></td>
                    <th><input type="text" name="PKO" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="PKOk">Kup</button></td>
                    <td><button class="button-sell" name="PKOs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/PKN_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $PKN[1] ?></td>
                    <th><input type="text" name="PKN" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="PKNk">Kup</button></td>
                    <td><button class="button-sell" name="PKNs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/PZU_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $PZU[1] ?></td>
                    <th><input type="text" name="PZU" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="PZUk">Kup</button></td>
                    <td><button class="button-sell" name="PZUs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/JSW_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $JSW[1] ?></td>
                    <th><input type="text" name="JSW" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="JSWk">Kup</button></td>
                    <td><button class="button-sell" name="JSWs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/CCC_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $CCC[1] ?></td>
                    <th><input type="text" name="CCC" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="CCCk">Kup</button></td>
                    <td><button class="button-sell" name="CCCs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/DNP_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $DNP[1] ?></td>
                    <th><input type="text" name="DNP" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="DNPk">Kup</button></td>
                    <td><button class="button-sell" name="DNPs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/CDR_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $CDR[1] ?></td>
                    <th><input type="text" name="CDR" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="CDRk">Kup</button></td>
                    <td><button class="button-sell" name="CDRs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/LTS_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $LTS[1] ?></td>
                    <th><input type="text" name="LTS" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="LTSk">Kup</button></td>
                    <td><button class="button-sell" name="LTSs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/ALR_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $ALR[1] ?></td>
                    <th><input type="text" name="ALR" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="ALRk">Kup</button></td>
                    <td><button class="button-sell" name="ALRs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/TPE_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $TPE[1] ?></td>
                    <th><input type="text" name="TPE" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="TPEk">Kup</button></td>
                    <td><button class="button-sell" name="TPEs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/PEO_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $PEO[1] ?></td>
                    <th><input type="text" name="PEO" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="PEOk">Kup</button></td>
                    <td><button class="button-sell" name="PEOs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/SAN_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $BZW[1] ?></td>
                    <th><input type="text" name="SAN" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="SANk">Kup</button></td>
                    <td><button class="button-sell" name="SANs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/PGN_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $PGN[1] ?></td>
                    <th><input type="text" name="PGN" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="PGNk">Kup</button></td>
                    <td><button class="button-sell" name="PGNs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/NTU_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $GBK[1] ?></td>
                    <th><input type="text" name="GNB" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="GNBk">Kup</button></td>
                    <td><button class="button-sell" name="GNBs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/ENG_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $ENG[1] ?></td>
                    <th><input type="text" name="ENG" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="ENGk">Kup</button></td>
                    <td><button class="button-sell" name="ENGs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/PGE_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $PGE[1] ?></td>
                    <th><input type="text" name="PGE" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="PGEk">Kup</button></td>
                    <td><button class="button-sell" name="PGEs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/ENA_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $ENA[1] ?></td>
                    <th><input type="text" name="ENA" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="ENAk">Kup</button></td>
                    <td><button class="button-sell" name="ENAs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/EUR_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $EUR[1] ?></td>
                    <th><input type="text" name="EUR" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="EURk">Kup</button></td>
                    <td><button class="button-sell" name="EURs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/KRU_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $KRU[1] ?></td>
                    <th><input type="text" name="KRU" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="KRUk">Kup</button></td>
                    <td><button class="button-sell" name="KRUs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/PKP_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $PKP[1] ?></td>
                    <th><input type="text" name="PKP" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="PKPk">Kup</button></td>
                    <td><button class="button-sell" name="PKPs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/LPP_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $LPP[1] ?></td>
                    <th><input type="text" name="LPP" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="LPPk">Kup</button></td>
                    <td><button class="button-sell" name="LPPs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/PLY_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $PLY[1] ?></td>
                    <th><input type="text" name="PLY" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="PLYk">Kup</button></td>
                    <td><button class="button-sell" name="PLYs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/MIL_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $MIL[1] ?></td>
                    <th><input type="text" name="MIL" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="MILk">Kup</button></td>
                    <td><button class="button-sell" name="MILs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/CPS_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $CPS[1] ?></td>
                    <th><input type="text" name="CPS" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="CPSk">Kup</button></td>
                    <td><button class="button-sell" name="CPSs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/OPL_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $OPL[1] ?></td>
                    <th><input type="text" name="OPL" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="OPLk">Kup</button></td>
                    <td><button class="button-sell" name="OPLs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/MBK_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $MBK[1] ?></td>
                    <th><input type="text" name="MBK" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="MBKk">Kup</button></td>
                    <td><button class="button-sell" name="MBKs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/EAT_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $EAT[1] ?></td>
                    <th><input type="text" name="EAT" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="EATk">Kup</button></td>
                    <td><button class="button-sell" name="EATs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/BMC_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $BMC[1] ?></td>
                    <th><input type="text" name="BMC" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="BMCk">Kup</button></td>
                    <td><button class="button-sell" name="BMCs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/VST_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $VST[1] ?></td>
                    <th><input type="text" name="VST" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="VSTk">Kup</button></td>
                    <td><button class="button-sell" name="VSTs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/GTC_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $GTC[1] ?></td>
                    <th><input type="text" name="GTC" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="GTCk">Kup</button></td>
                    <td><button class="button-sell" name="GTCs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/BFT_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $BFT[1] ?></td>
                    <th><input type="text" name="BFT" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="BFTk">Kup</button></td>
                    <td><button class="button-sell" name="BFTs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/MRB_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $MRB[1] ?></td>
                    <th><input type="text" name="MRB" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="MRBk">Kup</button></td>
                    <td><button class="button-sell" name="MRBs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/11B_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $llB[1] ?></td>
                    <th><input type="text" name="llB" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="llBk">Kup</button></td>
                    <td><button class="button-sell" name="llBs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/MAB_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $MAB[1] ?></td>
                    <th><input type="text" name="MAB" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="MABk">Kup</button></td>
                    <td><button class="button-sell" name="MABs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/EURPLN_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $EURPLN[1] ?></td>
                    <th><input type="text" name="EURPLN" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="EURPLNk">Kup</button></td>
                    <td><button class="button-sell" name="EURPLNs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/CHFPLN_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $CHFPLN[1] ?></td>
                    <th><input type="text" name="CHFPLN" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="CHFPLNk">Kup</button></td>
                    <td><button class="button-sell" name="CHFPLNs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/USDPLN_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $USDPLN[1] ?></td>
                    <th><input type="text" name="USDPLN" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="USDPLNk">Kup</button></td>
                    <td><button class="button-sell" name="USDPLNs">Sprzedaj</button></td>
                    </form>
                </tr>
                <tr>
                    <th style="width:50%"><iframe scrolling="no" style="width:100%" height=25 frameborder="0" src="https://widgets.biznesradar.pl/grid/GBPPLN_t"></iframe></th>
                    <form>
                        <td style="color:#ffffff">Masz: <?= $GBPPLN[1] ?></td>
                    <th><input type="text" name="GBPPLN" style="height:18px; padding:0px; font-size:13px" placeholder="Ilosc"></th>
                    <td><button class="button-buy" name="GBPPLNk">Kup</button></td>
                    <td><button class="button-sell" name="GBPPLNs">Sprzedaj</button></td>
                    </form>
                </tr>
            </table>
    </div>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script>
    // Get the modal
    var modal = document.getElementById('myModal');

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    modal.style.display = "block";

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>

    <script src="js/index.js"></script>
    <h5>C2018 Maciej Mikołajek. Wszelkie prawa zastrzeżone. Wersja 1.2.0 (09.05.18)</h5>
  </body>
</html>