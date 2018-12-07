<?php
/* Displays user information and some useful messages - debugging only */
function debug_to_console( $data ) {
    $output = $data;
    if ( is_array( $output ) ) {
        $output = implode( ',', $output);
    }
    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}

?>