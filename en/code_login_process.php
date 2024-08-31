<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../buwanaconn_env.php'; // Ensure this path is correct and the file exists

// Initialize the response array
$response = array('status' => 'error', 'message' => 'Something went wrong');

// Helper function to log debug messages
function log_debug($message) {
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

// Helper function to send JSON response and exit
function send_response($response) {
    echo json_encode($response);
    exit();
}

// Check if required POST fields are present
if (!empty($_POST['code']) && !empty($_POST['credential_key'])) {
    $credential_key = strtolower(trim($_POST['credential_key'])); // Make it case-insensitive
    $code = strtoupper(trim($_POST['code'])); // Make it case-insensitive

    // Bypass code for master key 'AYYEW'
    if ($code === 'AYYEW') {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = 'master'; // Assign a dummy user ID for master login
        $_SESSION['buwana_id'] = 'master'; // Set a session variable for consistent usage
        log_debug("Master code used for login. Session started.");
        $response = array('status' => 'success', 'redirect' => 'dashboard.php');
        send_response($response);
    }

    // Ensure the database connection is successful
    if ($buwana_conn->connect_error) {
        log_debug("Connection failed: " . $buwana_conn->connect_error);
        $response['message'] = "Connection failed: " . $buwana_conn->connect_error;
        send_response($response);
    }

    // Prepare the SQL statement
    $stmt = $buwana_conn->prepare("SELECT buwana_id FROM credentials_tb WHERE credential_key = ? AND UPPER(2fa_temp_code) = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $credential_key, $code);
        if ($stmt->execute()) {
            $stmt->bind_result($buwana_id);
            if ($stmt->fetch()) {
                // Login success
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $buwana_id;
                $_SESSION['buwana_id'] = $buwana_id; // Consistent session variable usage
                log_debug("Login successful. Session started for user ID: $buwana_id");
                $response = array('status' => 'success', 'redirect' => 'dashboard.php');
            } else {
                // Invalid code
                log_debug("Invalid code for credential: $credential_key");
                $response = array('status' => 'invalid', 'message' => 'Invalid code');
            }
        } else {
            // Error during statement execution
            log_debug('SQL execution error: ' . $stmt->error);
            $response['message'] = 'SQL execution error: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        // SQL preparation error
        log_debug('SQL preparation error: ' . $buwana_conn->error);
        $response['message'] = 'SQL preparation error: ' . $buwana_conn->error;
    }
    $buwana_conn->close();
} else {
    $response['message'] = 'Required fields are missing';
}

// Ensure no output before this point
send_response($response);
?>
