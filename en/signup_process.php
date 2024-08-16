<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start(); // Start output buffering

$response = ['success' => false];
$buwana_id = $_GET['id'] ?? null;

include '../buwanaconn_env.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($buwana_id)) {
    // Sanitize and validate inputs
    $credential_value = filter_var($_POST['credential_value'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password_hash'];

    // Validate password length
    if (strlen($password) < 6) {
        $response['error'] = 'invalid_password';
        echo json_encode($response);
        ob_end_clean(); // Clear buffer
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists in the Buwana database
    $sql_check_email = "SELECT COUNT(*) FROM users_tb WHERE email = ?";
    $stmt_check_email = $buwana_conn->prepare($sql_check_email);
    if ($stmt_check_email) {
        $stmt_check_email->bind_param("s", $credential_value);
        $stmt_check_email->execute();
        $stmt_check_email->bind_result($email_count);
        $stmt_check_email->fetch();
        $stmt_check_email->close();

        if ($email_count > 0) {
            $response['error'] = 'duplicate_email';
        } else {
            // Update credentials_tb with the new credential key
            $sql_update_credential = "UPDATE credentials_tb SET credential_key = ? WHERE buwana_id = ?";
            $stmt_update_credential = $buwana_conn->prepare($sql_update_credential);
            if ($stmt_update_credential) {
                $stmt_update_credential->bind_param("si", $credential_value, $buwana_id);
                if ($stmt_update_credential->execute()) {
                    // Update users_tb with the new password, email, and account status
                    $sql_update_user = "UPDATE users_tb SET password_hash = ?, email = ?, account_status = 'registered no login' WHERE buwana_id = ?";
                    $stmt_update_user = $buwana_conn->prepare($sql_update_user);
                    if ($stmt_update_user) {
                        $stmt_update_user->bind_param("ssi", $password_hash, $credential_value, $buwana_id);
                        if ($stmt_update_user->execute()) {
                            $response['success'] = true;
                        } else {
                            $response['error'] = 'db_error';
                        }
                        $stmt_update_user->close();
                    } else {
                        $response['error'] = 'db_error';
                    }
                } else {
                    $response['error'] = 'db_error';
                }
                $stmt_update_credential->close();
            } else {
                $response['error'] = 'db_error';
            }
        }
    } else {
        $response['error'] = 'db_error';
    }

    // Close the database connection
    $buwana_conn->close();
} else {
    $response['error'] = 'invalid_request';
}

ob_end_clean(); // Clear any previous output

// Return the JSON response
echo json_encode($response);
exit();
?>
