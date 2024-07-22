<?php
session_start(); // Start the session

// Destroy the session
session_unset();
session_destroy();

// Redirect to the login page or home page
header('Location: login.php?status=loggedout');
exit();
?>
