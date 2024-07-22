<?php
session_start();
include '../buwana_env.php'; // Adjust path as needed

// Retrieve form data
$credential_value = $_POST['credential_value'] ?? '';
$password = $_POST['password'] ?? '';

// Validate input
if (empty($credential_value) || empty($password)) {
    header('Location: login.php?error=empty_fields');
    exit();
}

// Prepare and execute query to check credentials
$sql = "SELECT user_id, password_hash FROM users_tb WHERE credential_value = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('s', $credential_value);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $password_hash);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $user_id;
            header('Location: dashboard.php');
            exit();
        } else {
            header('Location: login.php?error=invalid_password');
            exit();
        }
    } else {
        header('Location: login.php?error=invalid_credential');
        exit();
    }
} else {
    die('Error preparing statement: ' . $conn->error);
}

$stmt->close();
$conn->close();
?>
