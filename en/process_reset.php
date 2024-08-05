<?php
// Turn on or off error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session before any output
session_start();

// Grab language directory from URL
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

// Database credentials
$buwana_servername = "localhost";
$buwana_username = "ecobricks_gobrik_app";
$buwana_password = "1EarthenAuth!";
$buwana_dbname = "ecobricks_earthenAuth_db";

// Establish connection to the database
$buwana_conn = new mysqli($buwana_servername, $buwana_username, $buwana_password, $buwana_dbname);

// Check connection
if ($buwana_conn->connect_error) {
    die("Connection failed: " . $buwana_conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';

    if ($token && $password && $confirmPassword) {
        if ($password === $confirmPassword && strlen($password) >= 6) {
            // Check if token is valid
            $stmt = $buwana_conn->prepare("SELECT email FROM users_tb WHERE password_reset_token = ?");
            if (!$stmt) {
                die("Prepare statement failed: " . $buwana_conn->error);
            }
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->bind_result($email);
            $stmt->fetch();
            $stmt->close();

            if ($email) {
                // Update the user's password and reset token details in the database
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $currentDateTime = date('Y-m-d H:i:s');
                $stmt = $buwana_conn->prepare("UPDATE users_tb SET password_hash = ?, password_last_reset_dt = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE email = ?");
                if (!$stmt) {
                    die("Prepare statement failed: " . $buwana_conn->error);
                }
                $stmt->bind_param("sss", $hashedPassword, $currentDateTime, $email);
                $stmt->execute();
                $stmt->close();

                echo '<script>alert("Your password has been reset! You can now log in using your new password."); window.location.href = "login.php";</script>';
                exit();
            } else {
                echo '<script>alert("Invalid token. Please try again."); window.location.href = "login.php";</script>';
                exit();
            }
        } else {
            echo '<script>alert("Passwords do not match or are not long enough. Please try again."); window.location.href = "password-reset.php?token=' . urlencode($token) . '";</script>';
            exit();
        }
    } else {
        echo '<script>alert("All fields are required. Please try again."); window.location.href = "password-reset.php?token=' . urlencode($token) . '";</script>';
        exit();
    }
} else {
    echo '<script>alert("Invalid request. Please try again."); window.location.href = "login.php";</script>';
    exit();
}

// Close the database connection
$buwana_conn->close();
?>
