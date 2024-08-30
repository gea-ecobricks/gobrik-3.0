<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PART 1 Get the credential key (i.e email)

$response = array();
$credential_key = $_POST['credential_key'] ?? '';

if (empty($credential_key)) {
    $response['status'] = 'empty_fields';
    echo json_encode($response);
    exit();
}


// PART 2: GoBrik Account validation

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

     if ($buwana_activated == '0') {  // This indicates the tb_ecobricker account hasn't been activated
    $response['status'] = 'activation_required';
    $response['redirect'] = "../$lang/activate.php?id=$ecobricker_id";
    echo json_encode($response);
    exit();
}

        $stmt_check_email->close();
    } else {
        $stmt_check_email->close();
        $response['status'] = 'not_found';
        $response['message'] = 'Email not found';
        echo json_encode($response);
        exit();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Database query failed: ' . $gobrik_conn->error;
    echo json_encode($response);
    exit();
}

// PART 3: Check Buwana Database for the credential

require_once ("../buwanaconn_env.php");

$sql_credential = "SELECT buwana_id, 2fa_issued_count FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $buwana_conn->prepare($sql_credential);
if ($stmt_credential) {
    $stmt_credential->bind_param('s', $credential_key);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($buwana_id, $issued_count);
        $stmt_credential->fetch();
        $stmt_credential->close();

        // Generate a new 2FA temporary code
        $temp_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
        $issued_datetime = date('Y-m-d H:i:s');
        $new_issued_count = $issued_count + 1;

        // Update the credentials_tb with new 2FA details
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
            $response['2fa_code'] = $temp_code; // Optionally return the code in the response
            echo json_encode($response);
            exit();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to update 2FA details: ' . $buwana_conn->error;
            echo json_encode($response);
            exit();
        }
    } else {
        $response['status'] = 'crednotfound';
        $response['message'] = 'Credential not found';
        echo json_encode($response);
        exit();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error preparing statement for credentials_tb: ' . $buwana_conn->error;
    echo json_encode($response);
    exit();
}

// Close the database connections
$buwana_conn->close();
$gobrik_conn->close();

?>
