<?php
session_start();
include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

// Retrieve form data
$user_id = $_POST['user_id'];
$credential_value = $_POST['credential_value'];
$password = $_POST['password'];

// Look up the stored password hash and first_name from users_tb
$sql_lookup_password = "SELECT password_hash, first_name FROM users_tb WHERE user_id = ?";
$stmt_lookup_password = $conn->prepare($sql_lookup_password);

if ($stmt_lookup_password) {
    $stmt_lookup_password->bind_param("i", $user_id);
    $stmt_lookup_password->execute();
    $stmt_lookup_password->bind_result($stored_password_hash, $first_name);
    $stmt_lookup_password->fetch();
    $stmt_lookup_password->close();

    // Verify the entered password with the stored password hash
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

            // Part 3: Update another database

           include '../ecobricks_env.php'; // this file provides the database server, user, dbname information to access the server


            $sql_insert_ecobricker = "INSERT INTO load_ecobricker_trim (first_name, buwana_id, date_registered) VALUES (?, ?, NOW())";
            $stmt_insert_ecobricker = $conn2->prepare($sql_insert_ecobricker);
            if ($stmt_insert_ecobricker) {
                $stmt_insert_ecobricker->bind_param("si", $first_name, $user_id);
                $stmt_insert_ecobricker->execute();
                $stmt_insert_ecobricker->close();
            } else {
                die("Error preparing statement in ecobricks_gobrik_msql_db: " . $conn2->error);
            }
            $conn2->close();

            // Set session variables and redirect to the dashboard or appropriate page
            $_SESSION['user_id'] = $user_id;
            header("Location: dashboard.php"); // Change this to the appropriate page
            exit();
        } else {
            die("Error updating user data: " . $conn->error);
        }
    } else {
        // Password is incorrect, redirect back to the login page with an error message
        header("Location: signedup-login.php?id=$user_id&error=wrong_password");
        exit();
    }
} else {
    die("Error preparing statement for users_tb: " . $conn->error);
}

$conn->close();
?>
