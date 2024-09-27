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


/**
 * Subscribe the user to a specific newsletter based on the newsletter ID.
 */
function subscribeUserToNewsletter($email, $newsletter_id) {
    try {
        $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/members/";
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
 * Unsubscribe the user from a specific newsletter based on the newsletter ID.
 */
function unsubscribeUserFromNewsletter($email, $newsletter_id) {
    try {
        $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/members/?filter=email:" . urlencode($email);
        $jwt = createGhostJWT();

        // Fetch current member data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log the response and status code for debugging
        error_log('Unsubscribe fetch member API response: ' . $response);
        error_log('HTTP status code: ' . $http_code);

        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error fetching member data: ' . curl_error($ch));
            curl_close($ch);
            return;
        }

        $response_data = json_decode($response, true);
        $member_id = $response_data['members'][0]['id'] ?? null;

        if ($member_id) {
            // Prepare unsubscribe data
            $data = [
                'members' => [
                    [
                        'id' => $member_id,
                        'newsletters' => [['id' => $newsletter_id, 'subscribed' => false]]
                    ]
                ]
            ];

            $jsonData = json_encode($data);
            error_log("Attempting to unsubscribe user with data: " . $jsonData);

            curl_setopt($ch, CURLOPT_URL, $ghost_api_url . $member_id . '/');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Log the response and status code for debugging
            error_log('Unsubscribe API response: ' . $response);
            error_log('HTTP status code: ' . $http_code);

            if (curl_errno($ch) || $http_code >= 400) {
                error_log('Error unsubscribing from newsletter: ' . curl_error($ch) . ' - Response: ' . $response);
            }
        }

        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception occurred while unsubscribing from newsletter: ' . $e->getMessage());
    }
}

?>
