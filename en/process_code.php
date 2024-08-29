<?php
session_start();

// PART 1: Grab user credentials from the login form submission
$credential_key = $_POST['credential_key'] ?? '';
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

if (empty($credential_key)) {
    header("Location: ../$lang/login.php?status=empty_fields&key=" . urlencode($credential_key));
    exit();
}

// PART 2: GoBrik Account validation

// gobrik_conn
require_once ("../gobrikconn_env.php");

// Check the GoBrik database to see if the user has an unactivated account
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
            header("Location: ../$lang/activate.php?id=$ecobricker_id");  // Redirect to activation page
            exit();
        }

        $stmt_check_email->close();
    } else {
        $stmt_check_email->close();
    }
} else {
    error_log('Error preparing statement for checking email: ' . $gobrik_conn->error);
    die('Database query failed.');
}

// PART 3: Check Buwana Database
// buwana_conn
require_once ("../buwanaconn_env.php");

// SQL query to get buwana_id from credentials_tb using credential_key
$sql_credential = "SELECT buwana_id FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $buwana_conn->prepare($sql_credential);
if ($stmt_credential) {
    // Bind the credential_key parameter to the SQL query
    $stmt_credential->bind_param('s', $credential_key);
    // Execute the query
    $stmt_credential->execute();
    // Store the result
    $stmt_credential->store_result();

    // Check if exactly one record is found
    if ($stmt_credential->num_rows === 1) {
        // Bind the result to $buwana_id
        $stmt_credential->bind_result($buwana_id);
        // Fetch the result
        $stmt_credential->fetch();
        // Close the statement
        $stmt_credential->close();

        // Redirect back to login.php with a status indicating the credential was found
        header("Location: ../$lang/login.php?status=credfound&id=$buwana_id");
        exit();
    } else {
        // Redirect back to login.php with a status indicating the credential was not found
        header("Location: ../$lang/login.php?status=crednotfound&key=" . urlencode($credential_key));
        exit();
    }
} else {
    // If there's an error preparing the credential statement, terminate the script with an error message
    die('Error preparing statement for credentials_tb: ' . $buwana_conn->error);
}

// Close the database connections
$buwana_conn->close();
$gobrik_conn->close();
?>
