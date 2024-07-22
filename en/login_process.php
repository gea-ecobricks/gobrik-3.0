<?php
session_start();
include '../buwana_env.php'; // Adjust path as needed

// Retrieve form data
$credential_value = $_POST['credential_value'] ?? '';
$password = $_POST['password'] ?? '';

// Validate input
if (empty($credential_value) || empty($password)) {
    header('Location: signedup_login.php?error=empty_fields');
    exit();
}

// Prepare and execute query to check credentials
$sql_credential = "SELECT user_id FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $conn->prepare($sql_credential);

if ($stmt_credential) {
    $stmt_credential->bind_param('s', $credential_value);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($user_id);
        $stmt_credential->fetch();
        $stmt_credential->close();

        // Now retrieve the password hash from users_tb
        $sql_user = "SELECT password_hash FROM users_tb WHERE user_id = ?";
        $stmt_user = $conn->prepare($sql_user);

        if ($stmt_user) {
            $stmt_user->bind_param('i', $user_id);
            $stmt_user->execute();
            $stmt_user->store_result();

            if ($stmt_user->num_rows === 1) {
                $stmt_user->bind_result($password_hash);
                $stmt_user->fetch();

                // Verify password
                if (password_verify($password, $password_hash)) {
                    $_SESSION['user_id'] = $user_id;
                    header('Location: dashboard.php');
                    exit();
                } else {
                    header('Location: signedup_login.php?error=invalid_password');
                    exit();
                }
            } else {
                header('Location: signedup_login.php?error=invalid_user');
                exit();
            }
            $stmt_user->close();
        } else {
            die('Error preparing statement for users_tb: ' . $conn->error);
        }
    } else {
        header('Location: signedup_login.php?error=invalid_credential');
        exit();
    }
} else {
    die('Error preparing statement for credentials_tb: ' . $conn->error);
}

$conn->close();
?>
