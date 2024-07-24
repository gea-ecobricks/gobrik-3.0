<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false];

include '../buwana_env.php'; // This file provides the database server, user, dbname information to access the server

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate email input
    $credential_value = filter_var($_POST['credential_value'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($credential_value, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = 'invalid_email';
        echo json_encode($response);
        exit();
    }

    // Check if the email already exists
    $sql_check_email = "SELECT COUNT(*) FROM users_tb WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    if ($stmt_check_email) {
        $stmt_check_email->bind_param("s", $credential_value);
        $stmt_check_email->execute();
        $stmt_check_email->bind_result($email_count);
        $stmt_check_email->fetch();
        $stmt_check_email->close();

        if ($email_count > 0) {
            $response['error'] = 'duplicate_email';
        } else {
            $response['success'] = true;
        }
    } else {
        $response['error'] = 'db_error';
    }

    // Close the database connection
    $conn->close();
} else {
    $response['error'] = 'invalid_request';
}

// Return the JSON response
echo json_encode($response);
exit();
?>
