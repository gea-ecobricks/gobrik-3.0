<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../buwanaconn_env.php'; // Ensure this path is correct and the file exists

$response = array('status' => 'error', 'message' => 'Something went wrong');

if (!empty($_POST['code']) && !empty($_POST['credential_key'])) {
    $credential_key = strtolower(trim($_POST['credential_key'])); // Make it case-insensitive
    $code = strtoupper(trim($_POST['code'])); // Make it case-insensitive

    // Bypass code for master key 'AYYEW'
    if ($code === 'AYYEW') {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = 'master'; // Assign a dummy user ID for master login
        $_SESSION['buwana_id'] = 'master'; // Set a session variable for consistent usage
        file_put_contents('debug.log', "Master code used for login. Session started.\n", FILE_APPEND);
        $response = array('status' => 'success', 'redirect' => 'dashboard.php');
        echo json_encode($response);
        exit();
    }

    // Ensure the database connection is successful
    if ($buwana_conn->connect_error) {
        $response['message'] = "Connection failed: " . $buwana_conn->connect_error;
        echo json_encode($response);
        exit();
    }

    // Prepare the SQL statement
    $stmt = $buwana_conn->prepare("SELECT buwana_id FROM credentials_tb WHERE credential_key = ? AND UPPER(2fa_temp_code) = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $credential_key, $code);
        $stmt->execute();

        // Use bind_result() to fetch the result
        $stmt->bind_result($buwana_id);
        if ($stmt->fetch()) {
            // Login success
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $buwana_id;
            $_SESSION['buwana_id'] = $buwana_id; // Consistent session variable usage
            file_put_contents('debug.log', "Login successful. Session started for user ID: $buwana_id\n", FILE_APPEND);
            $response = array('status' => 'success', 'redirect' => 'dashboard.php');
        } else {
            // Invalid code
            file_put_contents('debug.log', "Invalid code for credential: $credential_key\n", FILE_APPEND);
            $response = array('status' => 'invalid', 'message' => 'Invalid code');
        }

        $stmt->close();
    } else {
        // SQL preparation error
        file_put_contents('debug.log', 'SQL preparation error: ' . $buwana_conn->error . "\n", FILE_APPEND);
        $response['message'] = 'SQL preparation error: ' . $buwana_conn->error;
    }
    $buwana_conn->close();
} else {
    $response['message'] = 'Required fields are missing';
}

// Ensure no output before this point
echo json_encode($response);
exit();
?>
