<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in; otherwise, redirect to login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Include necessary files and setup JWT creation
require_once '../scripts/earthen_subscribe_functions.php';

// Get the user's buwana_id from the POST data
$buwana_id = $_POST['buwana_id'] ?? null;

// Initialize user variables
$credential_key = ''; // This should hold the user's email address

// Include database connection for retrieving the user's email
include '../buwanaconn_env.php';

// Fetch the user's email based on buwana_id
if ($buwana_id) {
    $sql_lookup_credential = "SELECT credential_key FROM credentials_tb WHERE buwana_id = ?";
    $stmt_lookup_credential = $buwana_conn->prepare($sql_lookup_credential);
    if ($stmt_lookup_credential) {
        $stmt_lookup_credential->bind_param("i", $buwana_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        die('Database error occurred while fetching user credentials.');
    }
}

// Ensure we have the user's email
if (empty($credential_key)) {
    die('User email could not be retrieved.');
}

// Retrieve selected subscriptions from the form submission
$selected_subscriptions = $_POST['subscriptions'] ?? [];

// Fetch current subscriptions from the Ghost API
$current_subscriptions = getCurrentUserSubscriptions($credential_key);

// Determine which newsletters to subscribe to and which to unsubscribe from
$to_subscribe = array_diff($selected_subscriptions, $current_subscriptions);
$to_unsubscribe = array_diff($current_subscriptions, $selected_subscriptions);

// Subscribe the user to the selected newsletters
foreach ($to_subscribe as $newsletter_id) {
    subscribeUserToNewsletter($credential_key, $newsletter_id);
}

// Unsubscribe the user from newsletters they did not select
foreach ($to_unsubscribe as $newsletter_id) {
    unsubscribeUserFromNewsletter($credential_key, $newsletter_id);
}

// Redirect the user to the login page with the required parameters after processing
header('Location: login.php?status=firsttime&id=' . urlencode($buwana_id));
exit();

// The rest of the functions remain unchanged
?>
