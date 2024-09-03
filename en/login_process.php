<?php
session_start();

// PART 1: Grab user credentials from the login form submission
$credential_key = $_POST['credential_key'] ?? '';
$password = $_POST['password'] ?? '';
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

if (empty($credential_key) || empty($password)) {
    header("Location: ../$lang/login.php?status=empty_fields&key=" . urlencode($credential_key));
    exit();
}

// PART 2: GoBrik Account validation

//gobrik_conn
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
// Buwana DB access credentials

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

        // SQL query to get password_hash from users_tb using buwana_id in Buwana database
        $sql_user = "SELECT password_hash FROM users_tb WHERE buwana_id = ?";
        $stmt_user = $buwana_conn->prepare($sql_user);
        if ($stmt_user) {
            // Bind the buwana_id parameter to the SQL query
            $stmt_user->bind_param('i', $buwana_id);
            // Execute the query
            $stmt_user->execute();
            // Store the result
            $stmt_user->store_result();

            // Check if exactly one record is found
            if ($stmt_user->num_rows === 1) {
                // Bind the result to $password_hash
                $stmt_user->bind_result($password_hash);
                // Fetch the result
                $stmt_user->fetch();

                // Verify the password entered by the user
                if (password_verify($password, $password_hash)) {

                    // PART 4: Update Buwana Account
                    // Update last_login with current date and time stamp and increment login_count
                    $sql_update_user = "UPDATE users_tb SET last_login = NOW(), login_count = login_count + 1 WHERE buwana_id = ?";
                    $stmt_update_user = $buwana_conn->prepare($sql_update_user);
                    if ($stmt_update_user) {
                        // Bind the buwana_id parameter to the SQL query
                        $stmt_update_user->bind_param('i', $buwana_id);
                        // Execute the query
                        $stmt_update_user->execute();
                        // Close the statement
                        $stmt_update_user->close();
                    } else {
                        // If there's an error preparing the update statement, terminate the script with an error message
                        die('Error preparing statement for updating users_tb: ' . $buwana_conn->error);
                    }

                    // Update last_login with current date and time stamp and increment times_used in credentials_tb
                    $sql_update_credential = "UPDATE credentials_tb SET last_login = NOW(), times_used = times_used + 1 WHERE buwana_id = ?";
                    $stmt_update_credential = $buwana_conn->prepare($sql_update_credential);
                    if ($stmt_update_credential) {
                        // Bind the buwana_id parameter to the SQL query
                        $stmt_update_credential->bind_param('i', $buwana_id);
                        // Execute the query
                        $stmt_update_credential->execute();
                        // Close the statement
                        $stmt_update_credential->close();
                    } else {
                        // If there's an error preparing the update statement, terminate the script with an error message
                        die('Error preparing statement for updating credentials_tb: ' . $buwana_conn->error);
                    }

                    // PART 5: Update GoBrik Account
                    // Update last_login with current date and time stamp and increment login_count in tb_ecobrickers
                    $sql_update_ecobricker = "UPDATE tb_ecobrickers SET last_login = NOW(), login_count = login_count + 1 WHERE email_addr = ?";
                    $stmt_update_ecobricker = $gobrik_conn->prepare($sql_update_ecobricker);
                    if ($stmt_update_ecobricker) {
                        // Bind the email_addr parameter to the SQL query
                        $stmt_update_ecobricker->bind_param('s', $credential_key);
                        // Execute the query
                        $stmt_update_ecobricker->execute();
                        // Close the statement
                        $stmt_update_ecobricker->close();
                    } else {
                        // If there's an error preparing the update statement, terminate the script with an error message
                        die('Error preparing statement for updating tb_ecobrickers: ' . $gobrik_conn->error);
                    }

                    // Set the session variable to indicate the user is logged in
                    $_SESSION['buwana_id'] = $buwana_id;

                    // Check if the redirect variable has a value
                    $redirect_url = !empty($redirect) ? $redirect . '.php' : 'dashboard.php';

                    // Redirect to the appropriate page
                    header("Location: " . $redirect_url);
                    exit();


                } else {
                    // Redirect to login page with an error message if the password is incorrect
                    header("Location: ../$lang/login.php?status=invalid_password&key=" . urlencode($credential_key));
                    exit();
                }
            } else {
                // Redirect to login page with an error message if the user is not found
                header("Location: ../$lang/login.php?status=invalid_user&key=" . urlencode($credential_key));
                exit();
            }
            // Close the statement
            $stmt_user->close();
        } else {
            // If there's an error preparing the user statement, terminate the script with an error message
            die('Error preparing statement for users_tb: ' . $buwana_conn->error);
        }
    } else {
        // Redirect to login page with an error message if the credential is invalid
        header("Location: ../$lang/login.php?status=invalid_credential&key=" . urlencode($credential_key));
        exit();
    }
} else {
    // If there's an error preparing the credential statement, terminate the script with an error message
    die('Error preparing statement for credentials_tb: ' . $buwana_conn->error);
}

$buwana_conn->close();
$gobrik_conn->close();
?>
