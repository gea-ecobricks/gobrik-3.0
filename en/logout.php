<?php
session_start(); // Start the session

// Retrieve the buwana_id before destroying the session
$buwana_id = isset($_SESSION['buwana_id']) ? $_SESSION['buwana_id'] : '';

// Retrieve the redirect parameter from the query string, if it exists
$redirect = isset($_GET['redirect']) ? filter_var($_GET['redirect'], FILTER_SANITIZE_STRING) : '';

// Log the action for debugging purposes
file_put_contents('debug.log', "Logging out user with ID: $buwana_id\n", FILE_APPEND);

// Destroy the session
session_unset();
session_destroy();

// Build the redirect URL with status, id, and redirect parameters
$redirect_url = 'login.php?status=logout&id=' . urlencode($buwana_id);
if (!empty($redirect)) {
    $redirect_url .= '&redirect=' . urlencode($redirect);
}

// Redirect to the login page
header('Location: ' . $redirect_url);
exit();
?>
