<?php

// using this method to log small bits of data to the console while debugging
function console_log( $data ) {
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
}

  // using this method to log larger arrays of data to the console while debugging
function console_log_messages( ...$messages ) {
    $msgs = '';

    foreach ($messages as $msg) {
        $msgs .= json_encode($msg);
    }

    echo '<script>';
    echo 'console.log('. json_encode($msgs) .')';
    echo '</script>';
}

?>