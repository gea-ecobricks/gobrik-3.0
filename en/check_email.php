<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false];

include '../buwana_env.php'; // This file provides the first database server, user, dbname information to access the server

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate email input
    $credential_value = filter_var($_POST['credential_value'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($credential_value, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = 'invalid_email';
        echo json_encode($response);
        exit();
    }

    // Check if the email already exists in the first database
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
            echo json_encode($response);
            exit();
        }
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        exit();
    }

    // Check if the email already exists in the GoBrik database
    $servername = "localhost";
    $username = "ecobricks_brikchain_viewer";
    $password = "desperate-like-the-Dawn";
    $dbname = "ecobricks_gobrik_msql_db";

    // Create connection
    $conn_gobrik = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn_gobrik->connect_error) {
        $response['error'] = 'db_error';
        echo json_encode($response);
        exit();
    }

    $sql_check_gobrik_email = "SELECT COUNT(*) FROM load_ecobricker_trim WHERE email_addr = ?";
    $stmt_check_gobrik_email = $conn_gobrik->prepare($sql_check_gobrik_email);
    if ($stmt_check_gobrik_email) {
        $stmt_check_gobrik_email->bind_param("s", $credential_value);
        $stmt_check_gobrik_email->execute();
        $stmt_check_gobrik_email->bind_result($gobrik_email_count);
        $stmt_check_gobrik_email->fetch();
        $stmt_check_gobrik_email->close();

        if ($gobrik_email_count > 0) {
            $response['error'] = 'duplicate_gobrik_email';
            echo json_encode($response);
            exit();
        }
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        exit();
    }

    // Close the GoBrik database connection
    $conn_gobrik->close();

    $response['success'] = true;
} else {
    $response['error'] = 'invalid_request';
}

// Return the JSON response
echo json_encode($response);
exit();
?>
