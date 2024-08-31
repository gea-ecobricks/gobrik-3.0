<?php
session_start(); // Start the session

// Retrieve the buwana_id before destroying the session
$buwana_id = isset($_SESSION['buwana_id']) ? $_SESSION['buwana_id'] : '';

// Log the action for debugging purposes
file_put_contents('debug.log', "Logging out user with ID: $buwana_id\n", FILE_APPEND);

// Destroy the session
session_unset();
session_destroy();

// Redirect to the login page with the status and buwana_id
header('Location: login.php?status=logout&id=' . urlencode($buwana_id));
exit();
?>
