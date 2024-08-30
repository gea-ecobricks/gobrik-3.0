<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debugging: log the incoming POST data
file_put_contents('debug.log', "code_process.php called with data: " . json_encode($_POST) . "\n", FILE_APPEND);

$response = array();
$credential_key = $_POST['credential_key'] ?? '';

if (empty($credential_key)) {
    $response['status'] = 'empty_fields';
    echo json_encode($response);
    exit();
}

// Debugging: log the current stage
file_put_contents('debug.log', "After credential key check: $credential_key\n", FILE_APPEND);


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

        if ($buwana_activated == '0') {  // Ensure this is a comparison
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

// PART 3: Check Buwana Database

require_once ("../buwanaconn_env.php");

$sql_credential = "SELECT buwana_id FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $buwana_conn->prepare($sql_credential);
if ($stmt_credential) {
    $stmt_credential->bind_param('s', $credential_key);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($buwana_id);
        $stmt_credential->fetch();
        $stmt_credential->close();

        $response['status'] = 'credfound';
        $response['buwana_id'] = $buwana_id;
        echo json_encode($response);
        exit();
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
