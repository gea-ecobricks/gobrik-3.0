<?php
session_start(); // Start the session to access session variables

// Retrieve the redirect parameter from the query string, if it exists
$redirect = isset($_GET['redirect']) ? filter_var($_GET['redirect'], FILTER_SANITIZE_STRING) : '';

// Log the action for debugging purposes
file_put_contents('debug.log', "Logging out user with session ID: " . session_id() . "\n", FILE_APPEND);

// Unset all session variables
$_SESSION = [];

// Destroy the session
if (session_id() !== "" || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/'); // Clear the session cookie
}
session_destroy(); // Destroy the session

// Clear all cookies related to session or user data
if (isset($_COOKIE['buwana_id'])) {
    setcookie('buwana_id', '', time() - 3600, '/');
}

// Build the redirect URL with status and redirect parameters
$redirect_url = 'login.php?status=logout';
if (!empty($redirect)) {
    $redirect_url .= '&redirect=' . urlencode($redirect);
}

// Redirect to the login page
header('Location: ' . $redirect_url);
exit();
?>
