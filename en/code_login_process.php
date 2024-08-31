<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../buwanaconn_env.php'; // Assume this file returns $buwana_conn as a mysqli object

$response = array('status' => 'error', 'message' => 'Something went wrong');

if (!empty($_POST['code']) && !empty($_POST['credential_key'])) {
    $credential_key = $_POST['credential_key'];
    $code = $_POST['code'];

    // Prepare the SQL statement
    if ($stmt = $buwana_conn->prepare("SELECT buwana_id FROM credentials_tb WHERE credential_key = ? AND 2fa_temp_code = ?")) {
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
