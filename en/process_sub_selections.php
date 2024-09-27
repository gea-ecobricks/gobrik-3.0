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

// Get the user's buwana_id from the URL
$buwana_id = $_GET['id'] ?? null;

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

/**
 * Get the current subscriptions of a user based on their email address.
 */
function getCurrentUserSubscriptions($email) {
    $subscriptions = [];

    // Call the checkEarthenEmailStatus function to get the user's current subscriptions
    $response = checkEarthenEmailStatus($email);
    $response_data = json_decode($response, true);

    if (isset($response_data['status']) && $response_data['status'] === 'success' && $response_data['registered'] === 1) {
        foreach ($response_data['newsletters'] as $newsletter) {
            $subscriptions[] = $newsletter['id']; // Collect the newsletter IDs
        }
    }

    return $subscriptions;
}

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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch) || $http_code >= 400) {
            error_log('Error subscribing to newsletter: ' . curl_error($ch));
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

            curl_setopt($ch, CURLOPT_URL, $ghost_api_url . $member_id . '/');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch) || $http_code >= 400) {
                error_log('Error unsubscribing from newsletter: ' . curl_error($ch));
            }
        }

        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception occurred while unsubscribing from newsletter: ' . $e->getMessage());
    }
}
?>
