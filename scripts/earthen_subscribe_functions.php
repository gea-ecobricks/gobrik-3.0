<?php

/**
 * Encodes data to a URL-safe Base64 format.
 * 
 * @param string $data Data to be encoded.
 * @return string URL-safe base64 encoded string.
 */
function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

/**
 * Creates a JWT token for Ghost Admin API authentication.
 * 
 * @return string JWT token.
 * @throws Exception if the API key is not found or invalid.
 */
function createGhostJWT() {
    // Retrieve the API key from the environment variable
    $apiKey = getenv('EARTHEN_KEY');

    if (!$apiKey) {
        displayError('API key not set.');
        exit();
    }

    // Split the API Key into ID and Secret for JWT generation
    list($id, $secret) = explode(':', $apiKey);

    // Prepare the header and payload for the JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'kid' => $id]);
    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300, // Token valid for 5 minutes
        'aud' => '/v3/admin/' // Audience value
    ]);

    // Encode Header and Payload
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // Create the Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, hex2bin($secret), true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Return the complete JWT token
    return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
}

/**
 * Displays an error message within the designated error div on the page.
 * 
 * @param string $error_type The type of error to display.
 */
function displayError($error_type) {
    echo "<script>
        document.getElementById('earthen-server-error').style.display = 'block';
        document.getElementById('earthen-server-error').innerText = 'An error has occurred connecting to the Earthen Newsletter server: $error_type';
    </script>";
}

/**
 * Grabs active newsletters from the Ghost API and populates the subscription form.
 */
function grabActiveEarthenSubs() {
    try {
        // Define the API URL for fetching newsletters
        $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/newsletters/";
        $jwt = createGhostJWT();

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
            displayError('Curl error: ' . curl_error($ch));
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
                    $sub_id = htmlspecialchars($newsletter['id']);
                    $sub_slug = htmlspecialchars($newsletter['slug']);
                    $sub_name = htmlspecialchars($newsletter['name']);
                    $sub_description = htmlspecialchars($newsletter['description']);
                    $sub_sender_name = htmlspecialchars($newsletter['sender_name']);
                    $sub_language = "English"; // Adjust if data in the JSON specifies a different language
                    $sub_frequency = "1-3 posts a month"; // Hard-coded frequency for demonstration

                    // Output the subscription box HTML
                    echo "
                        <div id=\"{$sub_slug}\" class=\"sub-box\" data-color=\"green\">
                            <input type=\"checkbox\" class=\"sub-checkbox\" id=\"checkbox-{$sub_slug}\" name=\"subscriptions[]\" value=\"{$sub_id}\">
                            <label for=\"checkbox-{$sub_slug}\" class=\"checkbox-label\"></label>
                            <div class=\"sub-image\"></div>
                            <div class=\"sub-content\">
                                <div class=\"sub-header\">
                                    <div class=\"sub-icon\"></div>
                                    <div class=\"sub-header-text\">
                                        <div class=\"sub-name\">{$sub_name}</div>
                                        <div class=\"sub-sender-name\">by {$sub_sender_name}</div>
                                    </div>
                                </div>
                                <div class=\"sub-description\">{$sub_description}</div>
                                <div class=\"sub-lang\">{$sub_language} | {$sub_frequency}</div>
                            </div>
                        </div>
                    ";
                    }
                }
            } else {
                echo "<script>console.log('No active newsletters found.');</script>";
            }
        } else {
            displayError('HTTP status ' . $http_code);
        }

        // Close the cURL session
        curl_close($ch);
    } catch (Exception $e) {
        displayError('Exception: ' . $e->getMessage());
    }
}



/**
 * Checks the subscription status of an email with the Ghost API.
 *
 * @param string $email The email address to check.
 */


// Function to check subscription status and return JSON response only
function checkEarthenEmailStatus($email) {
    try {
        // Set up the API URL and headers for the Ghost API request
        $email_encoded = urlencode($email);
        $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/members/?filter=email:$email_encoded";
        $jwt = createGhostJWT();

        // Set up the cURL request to the Ghost Admin API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Ghost ' . $jwt,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        // Execute the cURL session
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log('Curl error: ' . curl_error($ch)); // Log error instead of printing
            return json_encode(['status' => 'error', 'message' => 'Curl error']);
        }

        if ($http_code >= 200 && $http_code < 300) {
            // Return the JSON response directly if the request is successful
            return $response;
        } else {
            error_log('HTTP status ' . $http_code . ': ' . $response); // Log error
            return json_encode(['status' => 'error', 'message' => 'API call failed with HTTP code: ' . $http_code]);
        }

        curl_close($ch);
    } catch (Exception $e) {
        error_log('Exception: ' . $e->getMessage()); // Log exceptions
        return json_encode(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()]);
    }
}






?>



