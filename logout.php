<?php
require_once('config.php');  // Database connection
require_once('user.php');  

$user = new User($con);  // Initialize the User class
$user->logout();         // Destroy the session and log out

// Redirect to login page
header("Location: login.php");
exit;
