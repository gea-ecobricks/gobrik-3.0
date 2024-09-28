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

// Ensure we have the user's email
if (empty($credential_key)) {
    die('User email could not be retrieved.');
}

// Retrieve selected subscriptions from the form submission
$selected_subscriptions = $_POST['subscriptions'] ?? [];

// Determine which newsletters to subscribe to and which to unsubscribe from
$to_subscribe = array_diff($selected_subscriptions, $subscribed_newsletters);
$to_unsubscribe = array_diff($subscribed_newsletters, $selected_subscriptions);

// If subscribed_newsletters is empty, treat this as a new user subscription
if (empty($subscribed_newsletters)) {
    foreach ($to_subscribe as $newsletter_id) {
        subscribeUserToNewsletter($credential_key, $newsletter_id);
    }
} else {
    // If subscribed_newsletters is not empty, update the existing user
    // Fetch existing member ID to confirm user presence before updating
    $existing_member_id = getExistingMemberId($credential_key);

    if ($existing_member_id) {
        // Subscribe the user to the selected newsletters
        foreach ($to_subscribe as $newsletter_id) {
            updateSubscribeUser($existing_member_id, $newsletter_id);
        }
        // Unsubscribe the user from newsletters they did not select
        foreach ($to_unsubscribe as $newsletter_id) {
            updateUnsubscribeUser($existing_member_id, $newsletter_id);
        }
    } else {
        error_log('Error: Existing member ID could not be retrieved for updating subscriptions.');
    }
}

// Redirect the user to the login page with the required parameters after processing
header('Location: login.php?status=firsttime&id=' . urlencode($buwana_id));
exit();





/**
 * Subscribe the user to a specific newsletter based on the newsletter ID.
 */
function subscribeUserToNewsletter($email, $newsletter_id) {
    try {
        $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/";
        $jwt = createGhostJWT();

        // Prepare subscription data
        $data = [
            'members' => [
                [
                    'email' => $email,
                    'newsletters' => [['id' => $newsletter_id]]
                ]
            ]
        ];

        // Convert data to JSON and log it for debugging
        $jsonData = json_encode($data);
        error_log("Attempting to subscribe user with data: " . $jsonData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log the response and status code for debugging
        error_log('Subscription API response: ' . $response);
        error_log('HTTP status code: ' . $http_code);

        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error subscribing to newsletter: ' . curl_error($ch) . ' - Response: ' . $response);
        }

        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception occurred while subscribing to newsletter: ' . $e->getMessage());
    }
}



/**
 * Update subscription for an existing user using PATCH.
 */
function updateSubscribeUser($member_id, $newsletter_id) {
    try {
        // Ensure the URL is correctly formatted with the member ID
        $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/" . $member_id . '/';
        $jwt = createGhostJWT();

        // Prepare updated subscription data
        // This assumes that the member's newsletters need to be fully replaced with the new set of newsletters
        $data = [
            'newsletters' => [['id' => $newsletter_id]] // Assuming we need to update the specific newsletter
        ];

        $jsonData = json_encode($data);
        error_log("Attempting to update subscription for user: " . $jsonData);

        // Update the member with the new subscription
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // Changed to PUT because PATCH may not be correctly supported in some versions
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log the response and status code for debugging
        error_log('Update subscription API response: ' . $response);
        error_log('HTTP status code: ' . $http_code);

        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error updating subscription: ' . curl_error($ch) . ' - Response: ' . $response);
        }

        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception occurred while updating subscription: ' . $e->getMessage());
    }
}

/**
 * Get the existing member ID based on their email.
 */
function getExistingMemberId($email) {
    try {
        $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/?filter=email:" . urlencode($email);
        $jwt = createGhostJWT();

        // Fetch current member data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response_data = json_decode($response, true);

        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error fetching member data: ' . curl_error($ch) . ' - Response: ' . $response);
            curl_close($ch);
            return null;
        }

        curl_close($ch);
        return $response_data['members'][0]['id'] ?? null; // Correctly fetch the member ID
    } catch (Exception $e) {
        error_log('Exception occurred while fetching member ID: ' . $e->getMessage());
        return null;
    }
}







/**
 * Update to unsubscribe a user from a specific newsletter using PATCH.
 */
function updateUnsubscribeUser($member_id, $newsletter_id) {
    try {
        $ghost_api_url = "https://earthen.io/ghost/api/v4/admin/members/" . $member_id . '/';
        $jwt = createGhostJWT();

        // Prepare data to unsubscribe from the newsletter
        $data = [
            'newsletters' => [['id' => $newsletter_id, 'subscribed' => false]]
        ];

        $jsonData = json_encode($data);
        error_log("Attempting to update unsubscribe for user: " . $jsonData);

        // Update the member to unsubscribe from the newsletter
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); // Use PATCH to update
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log the response and status code for debugging
        error_log('Unsubscribe API response: ' . $response);
        error_log('HTTP status code: ' . $http_code);

        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error unsubscribing from newsletter: ' . curl_error($ch) . ' - Response: ' . $response);
        }

        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception occurred while unsubscribing: ' . $e->getMessage());
    }
}
?>
