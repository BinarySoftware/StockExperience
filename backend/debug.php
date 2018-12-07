<?php

function console_log( $data ){
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
  }

function console_log_messages( ...$messages ){
    $msgs = '';
    foreach ($messages as $msg) {
        $msgs .= json_encode($msg);
    }

    echo '<script>';
    echo 'console.log('. json_encode($msgs) .')';
    echo '</script>';
}

?>