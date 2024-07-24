<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false];
$user_id = $_GET['id'] ?? null;

include '../buwana_env.php'; // This file provides the database server, user, dbname information to access the server

// PART 1: Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    $response['error'] = 'logged_in';
    echo json_encode($response);
    exit();
}

$credential_type = '';
$credential_key = '';
$first_name = '';
$account_status = '';

if (isset($user_id)) {
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE user_id = ?";
    $stmt_lookup_credential = $conn->prepare($sql_lookup_credential);
    if ($stmt_lookup_credential) {
        $stmt_lookup_credential->bind_param("i", $user_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        exit();
    }

    $sql_lookup_user = "SELECT first_name, account_status FROM users_tb WHERE user_id = ?";
    $stmt_lookup_user = $conn->prepare($sql_lookup_user);
    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $user_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name, $account_status);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        exit();
    }

    $credential_type = htmlspecialchars($credential_type);
    $first_name = htmlspecialchars($first_name);

    if ($account_status !== 'name set only') {
        $response['error'] = 'account_status';
        echo json_encode($response);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    $credential_value = htmlspecialchars($_POST['credential_value']);
    $password = $_POST['password_hash'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

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
        } else {
            $sql_update_credential = "UPDATE credentials_tb SET credential_key = ? WHERE user_id = ?";
            $stmt_update_credential = $conn->prepare($sql_update_credential);
            if ($stmt_update_credential) {
                $stmt_update_credential->bind_param("si", $credential_value, $user_id);
                if ($stmt_update_credential->execute()) {
                    $sql_update_user = "UPDATE users_tb SET password_hash = ?, email = ?, account_status = 'registered no login' WHERE user_id = ?";
                    $stmt_update_user = $conn->prepare($sql_update_user);
                    if ($stmt_update_user) {
                        $stmt_update_user->bind_param("ssi", $password_hash, $credential_value, $user_id);
                        if ($stmt_update_user->execute()) {
                            $response['success'] = true;
                            echo json_encode($response);
                            exit();
                        } else {
                            $response['error'] = 'db_error';
                            echo json_encode($response);
                            exit();
                        }
                        $stmt_update_user->close();
                    } else {
                        $response['error'] = 'db_error';
                        echo json_encode($response);
                        exit();
                    }
                } else {
                    $response['error'] = 'db_error';
                    echo json_encode($response);
                    exit();
                }
                $stmt_update_credential->close();
            } else {
                $response['error'] = 'db_error';
                echo json_encode($response);
                exit();
            }
        }
    } else {
        $response['error'] = 'db_error';
        echo json_encode($response);
        exit();
    }

    $conn->close();
} else {
    $response['error'] = 'invalid_request';
    echo json_encode($response);
    exit();
}
?>
