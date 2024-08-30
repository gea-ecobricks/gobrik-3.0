<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to PHPMailer

// Initialize variables
$response = array();
$credential_key = $_POST['credential_key'] ?? '';
$ecobricker_id = '';
$buwana_activated = '';
$first_name = '';
$email_addr = '';
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

if (empty($credential_key)) {
    $response['status'] = 'empty_fields';
    echo json_encode($response);
    exit();
}

require_once("../gobrikconn_env.php");

// PART 2 Functions

function generateCode() {
    return strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

function sendVerificationCode($email_addr, $login_code, $buwana_id) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.ecobricks.org';
        $mail->SMTPAuth = true;
        $mail->Username = 'gobrik@ecobricks.org';
        $mail->Password = '1Welcome!';
        $mail->SMTPSecure = false;
        $mail->Port = 26;

        $mail->setFrom('gobrik@ecobricks.org', 'GoBrik Team');
        $mail->addAddress($email_addr);

        $mail->isHTML(true);
        $mail->Subject = 'GoBrik Login Code';
        $mail->Body = "Hello,<br><br>Your code to login to your account is: <b>$login_code</b><br><br>Return to your browser and enter the code or click this link to login directly:<br><br>https://beta.gobrik.com/login.php?code=$buwana_id+$login_code<br><br>The GoBrik team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        file_put_contents('debug.log', "Mailer Error: " . $e->getMessage() . "\n", FILE_APPEND); // Log detailed error
        return false;
    }
}


// PART 3 Check GoBrik to see if user account is activated.
$sql_check_email = "SELECT ecobricker_id, buwana_activated, email_addr, first_name FROM tb_ecobrickers WHERE email_addr = ?";
$stmt_check_email = $gobrik_conn->prepare($sql_check_email);
if ($stmt_check_email) {
    $stmt_check_email->bind_param('s', $credential_key);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows === 1) {
        $stmt_check_email->bind_result($ecobricker_id, $buwana_activated, $email_addr, $first_name);
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

// PART 4: Check Buwana Database for the credential
require_once("../buwanaconn_env.php");

// Prepare the SQL statement to check for the existence of the credential_key
$sql_credential = "SELECT buwana_id FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $buwana_conn->prepare($sql_credential);
if ($stmt_credential) {
    $stmt_credential->bind_param('s', $credential_key);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    // Check if the credential_key exists in the database
    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($buwana_id);
        $stmt_credential->fetch();
        $stmt_credential->close();

        // Credential found, respond with credfound status
        $response['status'] = 'credfound';
        $response['buwana_id'] = $buwana_id; // Optionally return the buwana_id
        echo json_encode($response);
        exit();
    } else {
        // No credential found with the given key
        $stmt_credential->close();
        $response['status'] = 'crednotfound';
        echo json_encode($response);
        exit();
    }
} else {
    // SQL preparation failed, log the error and respond
    file_put_contents('debug.log', "SQL Prep Error: " . $buwana_conn->error . "\n", FILE_APPEND);
    $response['status'] = 'error';
    $response['message'] = 'Error preparing statement for credentials_tb: ' . $buwana_conn->error;
    echo json_encode($response);
    exit();
}

// Close the database connections
$buwana_conn->close();
$gobrik_conn->close();


?>
