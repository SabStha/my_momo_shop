<?php
echo "Server is working!<br>";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "POST data: " . print_r($_POST, true) . "<br>";
echo "GET data: " . print_r($_GET, true) . "<br>";
echo "All data: " . print_r($_REQUEST, true) . "<br>";
?>
