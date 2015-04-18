<?php
require_once('Util.php');
Util::log( "***+++ Test Post +++***");

// Example to parse "PUT" requests
parse_str(file_get_contents('php://input'), $_PUT);

Util::log( "Result: {$_PUT}");
// The result
print_r($_PUT);
?>