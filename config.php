<?php
$host = "127.0.0.1:3306";
$user = "root";
$pass = "";
$dbname = "dailyexpense";

// Create connection (OOP style)
$con = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error . 
        " | Seems like you haven't created the DATABASE with an exact name");
}
?>
