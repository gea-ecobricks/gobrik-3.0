<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files and setup JWT creation
require_once '../scripts/earthen_subscribe_functions.php';

// Get the user's data from the POST request
$buwana_id = $_POST['buwana_id'] ?? null;
$credential_key = $_POST['credential_key'] ?? null;
$subscribed_newsletters = json_decode($_POST['subscribed_newsletters'] ?? '[]', true); // Decode the JSON array of subscribed newsletters
$ghost_member_id = $_POST['ghost_member_id'] ?? null; // Get the member ID directly from POST

// Ensure we have the user's email
if (empty($credential_key)) {
    die('User email could not be retrieved.');
}

// Retrieve selected subscriptions from the form submission
$selected_subscriptions = $_POST['subscriptions'] ?? [];

// Determine which newsletters to subscribe to and which to unsubscribe from
$to_subscribe = array_diff($selected_subscriptions, $subscribed_newsletters);
// $to_unsubscribe = array_diff($subscribed_newsletters, $selected_subscriptions); // Commenting out unsubscribe determination

// If subscribed_newsletters is empty, treat this as a new user subscription
if (empty($subscribed_newsletters)) {
    // Call the function once with all the newsletter IDs in the array
    subscribeUserToNewsletter($credential_key, $to_subscribe);
}

} else {
    // If subscribed_newsletters is not empty, use the provided member ID to update subscriptions
if ($ghost_member_id) {
    // Update all selected subscriptions at once
    updateSubscribeUser($ghost_member_id, $selected_subscriptions);
} else {
    error_log('Error: Member ID is missing for updating subscriptions.');
}

}

// Redirect the user to the login page with the required parameters after processing
header('Location: login.php?status=firsttime&id=' . urlencode($buwana_id));
exit();
?>
