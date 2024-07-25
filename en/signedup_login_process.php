<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../buwana_env.php'; // This file provides the first database server, user, dbname information

// Retrieve form data
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
$credential_value = filter_input(INPUT_POST, 'credential_value', FILTER_SANITIZE_EMAIL);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

if (!$user_id || !$credential_value || !$password) {
    header("Location: signedup-login.php?id=$user_id&error=invalid_input");
    exit();
}

// Prepare and execute the query into the Buwana Database
$sql_lookup_password = "SELECT password_hash, first_name FROM users_tb WHERE user_id = ?";
$stmt_lookup_password = $conn->prepare($sql_lookup_password);

if ($stmt_lookup_password) {
    $stmt_lookup_password->bind_param("i", $user_id);
    $stmt_lookup_password->execute();
    $stmt_lookup_password->bind_result($stored_password_hash, $first_name);
    $stmt_lookup_password->fetch();
    $stmt_lookup_password->close();

    // Verify the entered password - UPDATE to $Lang
    if (password_verify($password, $stored_password_hash)) {
        // Password is correct, update user data
        $sql_update_user = "UPDATE users_tb SET
                            account_status = 'registration login complete',
                            created_at = NOW(),
                            last_login = NOW(),
                            languages_id = 'en',
                            login_count = login_count + 1
                            WHERE user_id = ?";
        $stmt_update_user = $conn->prepare($sql_update_user);
        if ($stmt_update_user) {
            $stmt_update_user->bind_param("i", $user_id);
            $stmt_update_user->execute();
            $stmt_update_user->close();
            $conn->close(); // Close the first database connection

            // Update the GoBrik Database
            $servername = "localhost";
            $username = "ecobricks_brikchain_viewer";
            $password = "desperate-like-the-Dawn";
            $dbname = "ecobricks_gobrik_msql_db";

            // Create connection
            $conn2 = new mysqli($servername, $username, $password, $dbname); // Establish new connection
            if ($conn2->connect_error) {
                error_log("Connection failed: " . $conn2->connect_error);
                header("Location: signedup-login.php?id=$user_id&error=db_connection_failed");
                exit();
            }

            // Check for existing ecobricker by email
            $sql_check_email = "SELECT ecobricker_id FROM ecobricker_live_tb WHERE email_addr = ?";
            $stmt_check_email = $conn2->prepare($sql_check_email);
            if ($stmt_check_email) {
                $stmt_check_email->bind_param("s", $credential_value);
                $stmt_check_email->execute();
                $stmt_check_email->store_result();

                if ($stmt_check_email->num_rows > 0) {
                    // Update existing ecobricker
                    $sql_update_ecobricker = "UPDATE ecobricker_live_tb SET
                                                first_name = ?,
                                                buwana_id = ?,
                                                buwana_activated = 'YES',
                                                buwana_activation_dt = NOW()
                                              WHERE email_addr = ?";
                    $stmt_update_ecobricker = $conn2->prepare($sql_update_ecobricker);
                    if ($stmt_update_ecobricker) {
                        $stmt_update_ecobricker->bind_param("sis", $first_name, $user_id, $credential_value);
                        if ($stmt_update_ecobricker->execute()) {
                            error_log("Updated existing ecobricker in ecobricker_live_tb: $first_name, $user_id");
                        } else {
                            error_log("Error executing update statement in ecobricks_gobrik_msql_db: " . $stmt_update_ecobricker->error);
                            header("Location: signedup-login.php?id=$user_id&error=db_update_failed");
                            exit();
                        }
                        $stmt_update_ecobricker->close();
                    } else {
                        error_log("Error preparing update statement in ecobricks_gobrik_msql_db: " . $conn2->error);
                        header("Location: signedup-login.php?id=$user_id&gobrikdberror=db_error");
                        exit();
                    }
                } else {
                    // Insert new ecobricker
                    $sql_insert_ecobricker = "INSERT INTO ecobricker_live_tb (first_name, buwana_id, email_addr, date_registered, maker_id, buwana_activated, buwana_activation_dt)
                                              VALUES (?, ?, ?, NOW(), ?, 'YES', NOW())";
                    $stmt_insert_ecobricker = $conn2->prepare($sql_insert_ecobricker);
                    if ($stmt_insert_ecobricker) {
                        $stmt_insert_ecobricker->bind_param("sisi", $first_name, $user_id, $credential_value, $user_id);
                        if ($stmt_insert_ecobricker->execute()) {
                            error_log("New user inserted into ecobricker_live_tb: $first_name, $user_id");
                        } else {
                            error_log("Error executing insert statement in ecobricks_gobrik_msql_db: " . $stmt_insert_ecobricker->error);
                            header("Location: signedup-login.php?id=$user_id&error=db_insert_failed");
                            exit();
                        }
                        $stmt_insert_ecobricker->close();
                    } else {
                        error_log("Error preparing insert statement in ecobricks_gobrik_msql_db: " . $conn2->error);
                        header("Location: signedup-login.php?id=$user_id&gobrikdberror=db_error");
                        exit();
                    }
                }

                $stmt_check_email->close();
            } else {
                error_log("Error preparing select statement in ecobricks_gobrik_msql_db: " . $conn2->error);
                header("Location: signedup-login.php?id=$user_id&gobrikdberror=db_error");
                exit();
            }

            $conn2->close(); // Close the second database connection

            // Redirect to dashboard
            $_SESSION['user_id'] = $user_id;
            header("Location: dashboard.php");
            exit();
        } else {
            error_log("Error updating user data: " . $conn->error);
            header("Location: signedup-login.php?id=$user_id&error=db_error");
            exit();
        }
    } else {
        header("Location: signedup-login.php?id=$user_id&error=wrong_password");
        exit();
    }
} else {
    error_log("Error preparing statement for users_tb: " . $conn->error);
    header("Location: signedup-login.php?id=$user_id&error=db_error");
    exit();
}

$conn->close();
?>
