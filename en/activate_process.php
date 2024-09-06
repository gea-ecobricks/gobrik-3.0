<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get parameters from the URL
$ecobricker_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$buwana_id = filter_input(INPUT_GET, 'buwana_id', FILTER_SANITIZE_NUMBER_INT);

// Check if the user is logged in
if (isLoggedIn()) {
    header('Location: dashboard.php'); // Redirect to dashboard if the user is logged in
    exit();
}

// Initialize variables
$email_addr = '';
$current_date_time = date('Y-m-d H:i:s');

// PART 1: Update GoBrik Database for tb_ecobrickers
require_once("../gobrikconn_env.php");

$sql_update_ecobricker = "UPDATE tb_ecobrickers SET email_confirm_dt = ? WHERE ecobricker_id = ?";
$stmt_update_ecobricker = $gobrik_conn->prepare($sql_update_ecobricker);

if ($stmt_update_ecobricker) {
    $stmt_update_ecobricker->bind_param('si', $current_date_time, $ecobricker_id);
    $stmt_update_ecobricker->execute();
    $stmt_update_ecobricker->close();
} else {
    die('Error preparing statement for updating tb_ecobrickers: ' . $gobrik_conn->error);
}

// Fetch the email address
$sql_fetch_email = "SELECT email_addr FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_fetch_email = $gobrik_conn->prepare($sql_fetch_email);

if ($stmt_fetch_email) {
    $stmt_fetch_email->bind_param('i', $ecobricker_id);
    $stmt_fetch_email->execute();
    $stmt_fetch_email->bind_result($email_addr);
    $stmt_fetch_email->fetch();
    $stmt_fetch_email->close();
} else {
    die('Error preparing statement for fetching email: ' . $gobrik_conn->error);
}

// PART 2: Update Buwana Database if buwana_id is provided
if (!empty($buwana_id)) {
    require_once("../buwanaconn_env.php");

    $sql_update_credentials = "UPDATE credentials_tb SET email_confirm_dt = ? WHERE buwana_id = ?";
    $stmt_update_credentials = $buwana_conn->prepare($sql_update_credentials);

    if ($stmt_update_credentials) {
        $stmt_update_credentials->bind_param('si', $current_date_time, $buwana_id);
        $stmt_update_credentials->execute();
        $stmt_update_credentials->close();
    } else {
        die('Error preparing statement for updating credentials_tb: ' . $buwana_conn->error);
    }
}

// PART 3: Check registration status on the Ghost platform using cURL

$email_encoded = urlencode($email_addr);
$ghost_admin_api_key = '66db68b5cff59f045598dbc3:5c82d570631831f277b1a9b4e5840703e73a68e948812b2277a0bc11c12c973f'; // Admin API Key
$ghost_url = "https://earthen.io/ghost/api/v3/admin/members/?filter=email:$email_encoded";

// Create JWT for Ghost Admin API
function createJWT($api_key) {
    $parts = explode(':', $api_key);
    $id = $parts[0];
    $secret = $parts[1];

    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode([
        'iat' => time(),
        'exp' => time() + 60, // Token valid for 60 seconds
        'aud' => '/v3/admin/'
    ]);

    // Encode Header
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    // Encode Payload
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    // Create Signature Hash
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, hex2bin($secret), true);
    // Encode Signature to Base64Url
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    // Create JWT
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    return $jwt;
}

// Generate the JWT token
$jwt = createJWT($ghost_admin_api_key);

// Initialize cURL session
$curl = curl_init();

// Set cURL options
curl_setopt_array($curl, [
    CURLOPT_URL => $ghost_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FAILONERROR => true,
    CURLOPT_SSL_VERIFYHOST => false, // For testing; remove in production
    CURLOPT_SSL_VERIFYPEER => false, // For testing; remove in production
    CURLOPT_HTTPHEADER => [
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ],
]);

// Execute the cURL request
$response = curl_exec($curl);

// Check for cURL errors
if ($response === false) {
    $error_msg = curl_error($curl);
    error_log('cURL error: ' . $error_msg);
    echo '<script>console.error("API call to Earthen.io failed: ' . $error_msg . '");</script>';

    // Close cURL session and redirect
    curl_close($curl);
    header('Location: activate-3.php?id=' . $ecobricker_id);
    exit();
}

// Close cURL session
curl_close($curl);

// Decode the API response
$response_data = json_decode($response, true);

$registered = 0;
if ($response_data && isset($response_data['members']) && count($response_data['members']) > 0) {
    $registered = 1;
}


//PART 4


// Update GoBrik Database with registration status
$sql_update_registration = "UPDATE tb_ecobrickers SET earthen_registered = ? WHERE ecobricker_id = ?";
$stmt_update_registration = $gobrik_conn->prepare($sql_update_registration);

if ($stmt_update_registration) {
    $stmt_update_registration->bind_param('ii', $registered, $ecobricker_id);
    $stmt_update_registration->execute();
    $stmt_update_registration->close();
} else {
    error_log('Error preparing statement for updating earthen_registered in tb_ecobrickers: ' . $gobrik_conn->error);
}

// Update Buwana Database with registration status if buwana_id is provided
if (!empty($buwana_id)) {
    $sql_update_earthen_status = "UPDATE credentials_tb SET earthen_registered = ? WHERE buwana_id = ?";
    $stmt_update_earthen_status = $buwana_conn->prepare($sql_update_earthen_status);

    if ($stmt_update_earthen_status) {
        $stmt_update_earthen_status->bind_param('ii', $registered, $buwana_id);
        $stmt_update_earthen_status->execute();
        $stmt_update_earthen_status->close();
    } else {
        error_log('Error preparing statement for updating earthen_registered in credentials_tb: ' . $buwana_conn->error);
    }
}

// Close database connections
$gobrik_conn->close();
if (isset($buwana_conn)) {
    $buwana_conn->close();
}

// Redirect to the next activation page
header('Location: activate-3.php?id=' . $ecobricker_id);
exit();
?>
