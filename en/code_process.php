<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure all data is sent over HTTPS
if ($_SERVER['HTTPS'] != "on") {
    $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirect");
    exit();
}

// Debugging: log the incoming POST data
file_put_contents('debug.log', "code_process.php called with data: " . json_encode($_POST) . "\n", FILE_APPEND);

$response = array();
$credential_key = $_POST['credential_key'] ?? '';
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

if (empty($credential_key)) {
    $response['status'] = 'empty_fields';
    echo json_encode($response);
    exit();
}

require_once ("../gobrikconn_env.php");

// Using prepared statements to enhance security
$sql_check_email = "SELECT ecobricker_id, buwana_activated FROM tb_ecobrickers WHERE email_addr = ?";
$stmt_check_email = $gobrik_conn->prepare($sql_check_email);
if ($stmt_check_email) {
    $stmt_check_email->bind_param('s', $credential_key);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows === 1) {
        $stmt_check_email->bind_result($ecobricker_id, $buwana_activated);
        $stmt_check_email->fetch();

        // Direct redirect if account activation is required
        if ($buwana_activated == '0') {
            $stmt_check_email->close();
            $redirect_url = "../$lang/activate.php?id=$ecobricker_id";
            header("Location: $redirect_url");
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

// Additional database logic goes here...

// Close the database connections
$buwana_conn->close();
$gobrik_conn->close();

?>
