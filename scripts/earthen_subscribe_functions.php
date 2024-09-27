
<?php

// Function to perform base64 URL encoding
function base64UrlEncode($data) {
    // Base64 encode the data, then remove padding and replace characters to make it URL-safe
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

// Gather subscription data
// grabActiveEarthenSubs();

//Check subscription status
$is_subscribed = false;
$earthen_subscriptions = ''; // To store newsletter names if subscribed
if (!empty($credential_key)) {
    ob_start(); // Start output buffering to capture the JSON response
    checkEarthenEmailStatus($credential_key); // Pass credential_key as $email
    $api_response = ob_get_clean(); // Get the output and clean the buffer

    // Parse the API response
    $response_data = json_decode($api_response, true);
    if (isset($response_data['status']) && $response_data['status'] === 'success' && $response_data['registered'] === 1) {
        $is_subscribed = true;
        // Join newsletter names with commas
        $earthen_subscriptions = !empty($response_data['newsletters']) ? implode(', ', $response_data['newsletters']) : '';
    }
}



// Function to grab active newsletters from Ghost API and populate the subscription form
function grabActiveEarthenSubs() {
    // Define the API URL for fetching newsletters
    $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/newsletters/";

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
        echo "<script>console.error('Curl error: " . curl_error($ch) . "');</script>";
        exit();
    }

    if ($http_code >= 200 && $http_code < 300) {
        // Successful response, parse the JSON data
        $response_data = json_decode($response, true);

        if ($response_data && isset($response_data['newsletters']) && is_array($response_data['newsletters'])) {
            // Generate HTML for each active newsletter
            foreach ($response_data['newsletters'] as $newsletter) {
                if ($newsletter['status'] === 'active') {
                    // Extract data
                    $id = htmlspecialchars($newsletter['id']);
                    $name = htmlspecialchars($newsletter['name']);
                    $description = htmlspecialchars($newsletter['description']);
                    $sender_name = htmlspecialchars($newsletter['sender_name']);
                    $language = "English"; // Assuming all are in English; adjust if you have this data in the JSON

                    // Output the subscription box HTML
                    echo "
                        <div id=\"{$id}\" class=\"sub-box\" data-color=\"green\">
                            <input type=\"checkbox\" class=\"sub-checkbox\" id=\"checkbox-{$id}\" name=\"subscriptions[]\" value=\"{$id}\">
                            <label for=\"checkbox-{$id}\" class=\"checkbox-label\"></label>
                            <div class=\"sub-icon\"></div>
                            <div class=\"sub-content\">
                                <h4 class=\"sub-name\">{$name}</h4>
                                <p class=\"sub-sender-name\">by {$sender_name}</p>
                                <p class=\"sub-description\">{$description}</p>
                                <p class=\"subscription-language\">{$language}</p>
                            </div>
                        </div>
                    ";
                }
            }
        } else {
            echo "<script>console.log('No active newsletters found.');</script>";
        }
    } else {
        // Handle error
        error_log('HTTP status ' . $http_code . ': ' . $response);
        echo "<script>console.error('API call to Earthen.io failed with HTTP code: " . $http_code . "');</script>";
    }

    // Close the cURL session
    curl_close($ch);
}




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

?>