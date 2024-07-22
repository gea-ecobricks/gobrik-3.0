<?php
session_start(); // Start the session at the beginning

include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

// Retrieve posted data
$user_id = $_POST['user_id'] ?? null;
$credential_value = $_POST['credential_value'] ?? null;
$password = $_POST['password'] ?? null;

// Input validation
if (!$user_id || !$credential_value || !$password) {
    // Redirect back to login page with error if any required field is missing
    header('Location: login.php?error=missing');
    exit();
}

// Look up the credentials from the database
$sql_lookup_credentials = "SELECT credential_key, password_hash FROM credentials_tb JOIN users_tb ON credentials_tb.user_id = users_tb.user_id WHERE credentials_tb.user_id = ?";
$stmt_lookup_credentials = $conn->prepare($sql_lookup_credentials);

if ($stmt_lookup_credentials) {
    $stmt_lookup_credentials->bind_param("i", $user_id);
    $stmt_lookup_credentials->execute();
    $stmt_lookup_credentials->bind_result($stored_credential_key, $stored_password_hash);
    $stmt_lookup_credentials->fetch();
    $stmt_lookup_credentials->close();

    // Verify the provided credential value and password
    if ($credential_value === $stored_credential_key && password_verify($password, $stored_password_hash)) {
        // Successful login
        $_SESSION['user_id'] = $user_id; // Set session variable for user_id

        // Redirect to the dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        // Invalid credentials
        header('Location: login.php?error=invalid');
        exit();
    }
} else {
    die("Error preparing statement for credentials_tb: " . $conn->error);
}

$conn->close();
?>
