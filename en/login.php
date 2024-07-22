<?php
include 'lang.php';
$version = '0.35';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php
include 'lang.php';
$version = '0.35';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;
$user_id = $_GET['id'] ?? null;

include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server


// Look up these fields from credentials_tb and users_tb using the user_id
$credential_type = '';
$credential_key = '';
$first_name = '';

if (isset($user_id)) {
    // First, look up the credential_type and credential_key from credentials_tb
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE user_id = ?";
    $stmt_lookup_credential = $conn->prepare($sql_lookup_credential);

    if ($stmt_lookup_credential) {
        $stmt_lookup_credential->bind_param("i", $user_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        die("Error preparing statement for credentials_tb: " . $conn->error);
    }

    // Then, look up the first_name from users_tb
    $sql_lookup_user = "SELECT first_name FROM users_tb WHERE id = ?";
    $stmt_lookup_user = $conn->prepare($sql_lookup_user);

    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $user_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement for users_tb: " . $conn->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    // Retrieve and sanitize form data
    $entered_credential = htmlspecialchars($_POST['credential_value']);
    $entered_password = $_POST['password'];

    // Check if entered credential matches the credential_key in the database
    if ($entered_credential === $credential_key) {
        // Retrieve the hashed password from users_tb
        $sql_get_password = "SELECT password FROM users_tb WHERE id = ?";
        $stmt_get_password = $conn->prepare($sql_get_password);

        if ($stmt_get_password) {
            $stmt_get_password->bind_param("i", $user_id);
            $stmt_get_password->execute();
            $stmt_get_password->bind_result($hashed_password);
            $stmt_get_password->fetch();
            $stmt_get_password->close();

            // Verify the entered password
            if (password_verify($entered_password, $hashed_password)) {
                // Successful login, update the user's last_login in users_tb
                $sql_update_user = "UPDATE users_tb SET last_login = NOW() WHERE id = ?";
                $stmt_update_user = $conn->prepare($sql_update_user);

                if ($stmt_update_user) {
                    $stmt_update_user->bind_param("i", $user_id);
                    $stmt_update_user->execute();
                    $stmt_update_user->close();
                } else {
                    die("Error preparing statement for updating users_tb: " . $conn->error);
                }

                // Update times_used and last_login in credentials_tb
                $sql_update_credentials = "UPDATE credentials_tb SET times_used = times_used + 1, last_login = NOW() WHERE user_id = ?";
                $stmt_update_credentials = $conn->prepare($sql_update_credentials);

                if ($stmt_update_credentials) {
                    $stmt_update_credentials->bind_param("i", $user_id);
                    $stmt_update_credentials->execute();
                    $stmt_update_credentials->close();
                } else {
                    die("Error preparing statement for updating credentials_tb: " . $conn->error);
                }

                // Redirect to the dashboard or any other page
                header("Location: dashboard.php?id=$user_id");
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            die("Error preparing statement for getting password: " . $conn->error);
        }
    } else {
        echo "Invalid credential.";
    }
}

$conn->close();
?>