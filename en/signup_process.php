<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start(); // Start output buffering

$response = ['success' => false];

include '../buwanaconn_env.php'; // Buwana database connection
include '../gobrikconn_env.php'; // GoBrik database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate inputs
    $credential_value = filter_var($_POST['credential_value'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password_hash'];
    $first_name = htmlspecialchars($_POST['first_name'], ENT_QUOTES);

    // Validate password length
    if (strlen($password) < 6) {
        $response['error'] = 'invalid_password';
        echo json_encode($response);
        ob_end_clean(); // Clear buffer
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists in the Buwana database
    $sql_check_email_buwana = "SELECT COUNT(*) FROM users_tb WHERE email = ?";
    $stmt_check_email_buwana = $buwana_conn->prepare($sql_check_email_buwana);
    if ($stmt_check_email_buwana) {
        $stmt_check_email_buwana->bind_param("s", $credential_value);
        $stmt_check_email_buwana->execute();
        $stmt_check_email_buwana->bind_result($email_count_buwana);
        $stmt_check_email_buwana->fetch();
        $stmt_check_email_buwana->close();

        if ($email_count_buwana > 0) {
            // If email exists in Buwana, return error
            $response['error'] = 'duplicate_email';
            echo json_encode($response);
            ob_end_clean();
            exit();
        }
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    // Check if the email already exists in the GoBrik database
    $sql_check_email_gobrik = "SELECT ecobricker_id FROM tb_ecobrickers WHERE email_addr = ?";
    $stmt_check_email_gobrik = $gobrik_conn->prepare($sql_check_email_gobrik);
    if ($stmt_check_email_gobrik) {
        $stmt_check_email_gobrik->bind_param("s", $credential_value);
        $stmt_check_email_gobrik->execute();
        $stmt_check_email_gobrik->bind_result($ecobricker_id);
        $stmt_check_email_gobrik->fetch();
        $stmt_check_email_gobrik->close();

        if ($ecobricker_id) {
            // If email exists in GoBrik, alert and redirect to activate.php
            $response['error'] = 'duplicate_gobrik_email';
            $response['redirect'] = "activate.php?id=$ecobricker_id";
            echo json_encode($response);
            ob_end_clean();
            exit();
        }
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        ob_end_clean();
        exit();
    }

    // Proceed with creating the Buwana user account
    $sql_create_user = "INSERT INTO users_tb (first_name, email, password_hash, account_status, created_at, last_login) VALUES (?, ?, ?, 'registered no login', NOW(), NOW())";
    $stmt_create_user = $buwana_conn->prepare($sql_create_user);

    if ($stmt_create_user) {
        $stmt_create_user->bind_param("sss", $first_name, $credential_value, $password_hash);

        if ($stmt_create_user->execute()) {
            $buwana_id = $buwana_conn->insert_id;

            // Insert the credential into the credentials_tb table
            $sql_insert_credential = "INSERT INTO credentials_tb (buwana_id, credential_key, credential_type) VALUES (?, ?, 'email')";
            $stmt_insert_credential = $buwana_conn->prepare($sql_insert_credential);
            $stmt_insert_credential->bind_param("is", $buwana_id, $credential_value);
            $stmt_insert_credential->execute();
            $stmt_insert_credential->close();

            // Create the Ecobricker account in GoBrik
            $sql_create_ecobricker = "INSERT INTO tb_ecobrickers (first_name, buwana_id, email_addr, date_registered, maker_id, buwana_activated, buwana_activation_dt) VALUES (?, ?, ?, NOW(), ?, 1, NOW())";
            $stmt_create_ecobricker = $gobrik_conn->prepare($sql_create_ecobricker);

            if ($stmt_create_ecobricker) {
                $stmt_create_ecobricker->bind_param("sisi", $first_name, $buwana_id, $credential_value, $buwana_id);
                if ($stmt_create_ecobricker->execute()) {
                    $ecobricker_id = $stmt_create_ecobricker->insert_id;

                    // Successfully created both accounts, redirect to confirm-email.php
                    $response['success'] = true;
                    $response['redirect'] = "confirm-email.php?id=$ecobricker_id";
                } else {
                    $response['error'] = 'db_error';
                }
                $stmt_create_ecobricker->close();
            } else {
                $response['error'] = 'db_error';
            }

            $stmt_create_user->close();
        } else {
            $response['error'] = 'db_error';
        }
    } else {
        $response['error'] = 'db_error';
    }

    // Close database connections
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    $response['error'] = 'invalid_request';
}

ob_end_clean(); // Clear any previous output

// Return the JSON response
echo json_encode($response);
exit();
?>
