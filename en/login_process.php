<?php
session_start();

// PART 1 Grab user credentials from the login form submission
$credential_key = $_POST['credential_key'] ?? '';
$password = $_POST['password'] ?? '';
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

if (empty($credential_key) || empty($password)) {
    header("Location: ../$lang/login.php?error=empty_fields");
    exit();
}

// PART 2: GoBrik validation

// GoBrik database credentials (we'll hide this soon!)
$gobrik_servername = "localhost";
$gobrik_username = "ecobricks_brikchain_viewer";
$gobrik_password = "desperate-like-the-Dawn";
$gobrik_dbname = "ecobricks_gobrik_msql_db";

// Create connection for GoBrik database
$gobrik_conn = new mysqli($gobrik_servername, $gobrik_username, $gobrik_password, $gobrik_dbname);
if ($gobrik_conn->connect_error) {
    error_log("Connection failed: " . $conn2->connect_error);
    header("Location: login.php?error=db_connection_failed");
    exit();
}
$gobrik_conn->set_charset("utf8mb4");

// Check the GoBrik database to see if user has an unactivated account
$sql_check_email = "SELECT ecobricker_id, buwana_activated FROM tb_ecobrickers WHERE email_addr = ?";
$stmt_check_email = $gobrik_conn->prepare($sql_check_email);
if ($stmt_check_email) {
    $stmt_check_email->bind_param('s', $credential_key);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows === 1) {
        $stmt_check_email->bind_result($ecobricker_id, $buwana_activated);
        $stmt_check_email->fetch();

        if ($buwana_activated === 'yes') {
            header("Location: activate.php?user_id=$ecobricker_id");  // page will let users activate their Buwana account
            exit();
        }

        $stmt_check_email->close();
    } else {
        $stmt_check_email->close();
    }
} else {
    die('Error preparing statement for checking email: ' . $conn2->error);
}


// PART 3: Check Buwana Database
// Buwana DB access credentials (we'll hid this soon too!)

$buwana_servername = "localhost";
$buwana_username = "ecobricks_gobrik_app";
$buwana_password = "1EarthenAuth!";
$buwana_dbname = "ecobricks_earthenAuth_db";

// Establish connections to both databases
$buwana_conn = new mysqli($buwana_servername, $buwana_username, $buwana_password, $buwana_dbname);

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


                    // Part 4: Successful login

                    // Assuming buwana_id is obtained after successful login
                    $buwana_id = $user_row['buwana_id'];

                    // Update login_count in users_tb
                    $updateLoginCountStmt = $conn->prepare("UPDATE users_tb SET login_count = login_count + 1 WHERE buwana_id = ?");
                    $updateLoginCountStmt->bind_param("i", $buwana_id);
                    $updateLoginCountStmt->execute();
                    $updateLoginCountStmt->close();

                    // Update last_login and times_used in credentials_tb
                    $currentDateTime = date("Y-m-d H:i:s");
                    $updateCredentialsStmt = $conn->prepare("UPDATE credentials_tb SET last_login = ?, times_used = times_used + 1 WHERE buwana_id = ?");
                    $updateCredentialsStmt->bind_param("si", $currentDateTime, $buwana_id);
                    $updateCredentialsStmt->execute();
                    $updateCredentialsStmt->close();

                    // Redirect to the appropriate page after login
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Redirect to login page with an error message if the password is incorrect
                    header("Location: login.php?error=invalid_password");
                    exit();
                }
            } else {
                // Redirect to login page with an error message if the user is not found
                header("Location: login.php?error=invalid_user");
                exit();
            }
            // Close the statement
            $stmt_user->close();
        } else {
            // If there's an error preparing the user statement, terminate the script with an error message
            die('Error preparing statement for users_tb: ' . $conn->error);
        }
    } else {
        // Redirect to login page with an error message if the credential is invalid
        header("Location: login.php?error=invalid_credential");
        exit();
    }
} else {
    // If there's an error preparing the credential statement, terminate the script with an error message
    die('Error preparing statement for credentials_tb: ' . $conn->error);
}


$buwana_conn->close();
$gobrik_conn->close();
?>

