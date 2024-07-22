<?php
session_start();
include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

// Retrieve form data
$user_id = $_POST['user_id'];
$credential_value = $_POST['credential_value'];
$password = $_POST['password'];

// Look up the stored password hash from the users_tb
$sql_lookup_password = "SELECT password_hash FROM users_tb WHERE user_id = ?";
$stmt_lookup_password = $conn->prepare($sql_lookup_password);

if ($stmt_lookup_password) {
    $stmt_lookup_password->bind_param("i", $user_id);
    $stmt_lookup_password->execute();
    $stmt_lookup_password->bind_result($stored_password_hash);
    $stmt_lookup_password->fetch();
    $stmt_lookup_password->close();

    // Verify the entered password with the stored password hash
    if (password_verify($password, $stored_password_hash)) {
        // Password is correct, set session variables and redirect to the dashboard or appropriate page
        $_SESSION['user_id'] = $user_id;
        header("Location: dashboard.php"); // Change this to the appropriate page
        exit();
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
