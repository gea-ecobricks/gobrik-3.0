<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Base64Url Encode function (defined outside for global use)
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Prepare the function to check the subscription status
function checkEarthenEmailStatus($email) {
    // Prepare and encode the email address for use in the API URL
    $email_encoded = urlencode($email);
    $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/members/?filter=email:$email_encoded";

    // Split API Key into ID and Secret for JWT generation
    $apiKey = '66db68b5cff59f045598dbc3:5c82d570631831f277b1a9b4e5840703e73a68e948812b2277a0bc11c12c973f';
    list($id, $secret) = explode(':', $apiKey);

    // Prepare the header and payload for the JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'kid' => $id]);
    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300, // Token valid for 5 minutes
        'aud' => '/v3/admin/' // Corrected audience value to match the expected pattern
    ]);

    // Encode Header and Payload
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // Create the Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, hex2bin($secret), true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Create the JWT token
    $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

    // Set up the cURL request to the Ghost Admin API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true); // Use GET to fetch data

    // Execute the cURL session
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        echo json_encode(['status' => 'error', 'message' => 'Curl error: ' . curl_error($ch)]);
        exit();
    }

    if ($http_code >= 200 && $http_code < 300) {
        // Successful response, parse the JSON data
        $response_data = json_decode($response, true);

        // Check if members are found
        $registered = 0; // Default to not registered
        $newsletters = []; // Array to hold newsletter names

        if ($response_data && isset($response_data['members']) && is_array($response_data['members']) && count($response_data['members']) > 0) {
            $registered = 1; // Member with the given email exists

            // Extract newsletter names
            if (isset($response_data['members'][0]['newsletters'])) {
                foreach ($response_data['members'][0]['newsletters'] as $newsletter) {
                    $newsletters[] = $newsletter['name'];
                }
            }

            echo json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is subscribed.', 'newsletters' => $newsletters]);
        } else {
            echo json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is not subscribed.']);
        }
    } else {
        // Handle error
        error_log('HTTP status ' . $http_code . ': ' . $response);
        echo json_encode(['status' => 'error', 'message' => 'API call to Earthen.io failed with HTTP code: ' . $http_code]);
    }

    // Close the cURL session
    curl_close($ch);
}

// Function to unsubscribe a user
function earthenUnsubscribe($email) {
    $email_encoded = urlencode($email);
    $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/members/$email_encoded";

    // Split API Key into ID and Secret for JWT generation
    $apiKey = '66db68b5cff59f045598dbc3:5c82d570631831f277b1a9b4e5840703e73a68e948812b2277a0bc11c12c973f';
    list($id, $secret) = explode(':', $apiKey);

    // Prepare the header and payload for the JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'kid' => $id]);
    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300, // Token valid for 5 minutes
        'aud' => '/v3/admin/'
    ]);

    // Encode Header and Payload
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // Create the Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, hex2bin($secret), true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Create the JWT token
    $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

    // Set up the cURL request to delete the user
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); // Use DELETE to unsubscribe

    // Execute the cURL session
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        echo json_encode(['status' => 'error', 'message' => 'Curl error: ' . curl_error($ch)]);
        exit();
    }

    if ($http_code >= 200 && $http_code < 300) {
        // Successful response
        echo json_encode(['status' => 'success', 'message' => 'User has been unsubscribed.']);
    } else {
        // Handle error
        error_log('HTTP status ' . $http_code . ': ' . $response);
        echo json_encode(['status' => 'error', 'message' => 'Unsubscribe failed with HTTP code: ' . $http_code]);
    }

    // Close the cURL session
    curl_close($ch);
}

// Handle incoming requests
if (isset($_POST['email'])) {
    $email = $_POST['email'];

    if (isset($_POST['unsubscribe']) && $_POST['unsubscribe'] === 'true') {
        earthenUnsubscribe($email);
    } else {
        checkEarthenEmailStatus($email);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No email address provided.']);
}
?>
