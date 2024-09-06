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

// PART 3: Check registration status on the Ghost platform
$email_encoded = urlencode($email_addr);
$ghost_url = "https://earthen.io/ghost/api/v3/admin/members/?email=$email_encoded&key=66db68b5cff59f045598dbc3:5c82d570631831f277b1a9b4e5840703e73a68e948812b2277a0bc11c12c973f";

// Perform the API call
$response = file_get_contents($ghost_url);

if ($response === false) {
    error_log('API call to Earthen.io failed.');
    echo '<script>console.error("API call to Earthen.io failed.");</script>';
    // Skip further processing and redirect
    header('Location: activate-3.php?id=' . $ecobricker_id);
    exit();
}

$response_data = json_decode($response, true);

$registered = 0;
if ($response_data && isset($response_data['members']) && count($response_data['members']) > 0) {
    $registered = 1;
}


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
