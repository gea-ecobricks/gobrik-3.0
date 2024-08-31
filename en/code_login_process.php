<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../buwanaconn_env.php'; // Buwana connection

$response = array('status' => 'error', 'message' => 'Something went wrong');

if (!empty($_POST['code']) && !empty($_POST['credential_key'])) {
    $credential_key = $_POST['credential_key'];
    $code = $_POST['code'];


    if ($buwana_conn->connect_error) {
        $response['message'] = "Connection failed: " . $buwana_conn->connect_error;
        echo json_encode($response);
        exit;
    }

    $stmt = $buwana_conn->prepare("SELECT buwana_id FROM credentials_tb WHERE credential_key = ? AND 2fa_temp_code = ?");
    $stmt->bind_param("ss", $credential_key, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Assuming successful login, set session variables
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $result->fetch_assoc()['buwana_id'];

        $response['status'] = 'success';
        $response['redirect'] = 'dashboard.php'; // Redirect to a logged-in page
    } else {
        $response['message'] = 'Invalid code';
        $response['status'] = 'invalid';
    }

    $stmt->close();
    $buwana_conn->close();
}

echo json_encode($response);
?>
