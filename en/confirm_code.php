<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to PHPMailer

$response = array();
$credential_key = $_POST['credential_key'] ?? '';

if (empty($credential_key)) {
    $response['status'] = 'empty_fields';
    echo json_encode($response);
    exit();
}

require_once ("../gobrikconn_env.php");

$sql_check_email = "SELECT ecobricker_id, buwana_activated FROM tb_ecobrickers WHERE email_addr = ?";
$stmt_check_email = $gobrik_conn->prepare($sql_check_email);
if ($stmt_check_email) {
    $stmt_check_email->bind_param('s', $credential_key);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows === 1) {
        $stmt_check_email->bind_result($ecobricker_id, $buwana_activated);
        $stmt_check_email->fetch();

        if ($buwana_activated == '0') {
            $response['status'] = 'activation_required';
            $response['redirect'] = "activate.php?id=$ecobricker_id";
            echo json_encode($response);
            exit();
        }

        $stmt_check_email->close();
    } else {
        $stmt_check_email->close();
        $response['status'] = 'not_found';
        echo json_encode($response);
        exit();
    }
} else {
    $response['status'] = 'error';
    echo json_encode($response);
    exit();
}

require_once ("../buwanaconn_env.php");

$sql_credential = "SELECT buwana_id, 2fa_issued_count FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $gobrik_conn->prepare($sql_credential);
if ($stmt_credential) {
    $stmt_credential->bind_param('s', $credential_key);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($buwana_id, $issued_count);
        $stmt_credential->fetch();
        $stmt_credential->close();

        // Corrected to generate a 5-character alphanumeric code
        $temp_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
        $issued_datetime = date('Y-m-d H:i:s');
        $new_issued_count = $issued_count + 1;

        $sql_update = "UPDATE credentials_tb SET
                       2fa_temp_code = ?,
                       2fa_code_issued = ?,
                       2fa_issued_count = ?
                       WHERE buwana_id = ?";
        $stmt_update = $buwana_conn->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param('ssii', $temp_code, $issued_datetime, $new_issued_count, $buwana_id);
            $stmt_update->execute();
            $stmt_update->close();

            $response['status'] = 'credfound';
            $response['buwana_id'] = $buwana_id;
            $response['2fa_code'] = $temp_code;
            echo json_encode($response);
            exit();
        } else {
            $response['status'] = 'error';
            echo json_encode($response);
            exit();
        }
    } else {
        $response['status'] = 'crednotfound';
        echo json_encode($response);
        exit();
    }
} else {
    $response['status'] = 'error';
    echo json_encode($response);
    exit();
}

$buwana_conn->close();
$gobrik_conn->close();

?>
