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
        $response = array('status' => 'success', 'redirect' => 'dashboard.php');
        echo json_encode($response);
        exit;
    }

    // Ensure the database connection is successful
    if ($buwana_conn->connect_error) {
        $response['message'] = "Connection failed: " . $buwana_conn->connect_error;
        echo json_encode($response);
        exit;
    }

    // Prepare the SQL statement
    $stmt = $buwana_conn->prepare("SELECT buwana_id FROM credentials_tb WHERE credential_key = ? AND UPPER(2fa_temp_code) = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $credential_key, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Login success
            $row = $result->fetch_assoc();
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $row['buwana_id'];
            $response = array('status' => 'success', 'redirect' => 'dashboard.php');
        } else {
            // Invalid code
            $response = array('status' => 'invalid', 'message' => 'Invalid code');
        }
        $stmt->close();
    } else {
        // SQL preparation error
        $response['message'] = 'SQL preparation error: ' . $buwana_conn->error;
    }
    $buwana_conn->close();
} else {
    $response['message'] = 'Required fields are missing';
}

echo json_encode($response);
?>