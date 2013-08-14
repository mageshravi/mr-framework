<?php
function linkStylesheets(array $css) {
    foreach ($css as $stylesheet) {
        echo "<link rel='stylesheet' type='text/css' media='screen' href='/css/$stylesheet.css' />";
    }
}
?>