
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

$user_id = $_POST['user_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    // Retrieve and sanitize form data
    $entered_credential = htmlspecialchars($_POST['credential_value']);
    $entered_password = $_POST['password'];

    // Look up these fields from credentials_tb and users_tb using the user_id
    $credential_type = '';
    $credential_key = '';
    $first_name = '';

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

    // Check if entered credential matches the credential_key in the database
    if ($entered_credential === $credential_key) {
        // Retrieve the hashed password from users_tb
        $sql_get_password = "SELECT password_hash FROM users_tb WHERE user_id = ?";
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
                $sql_update_user = "UPDATE users_tb SET last_login = NOW() WHERE user_id = ?";
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
                header("Location: onboard-1.php?id=$user_id");
                exit();
            } else {
                header("Location: login.php?id=$user_id&error=password");
                exit();
            }
        } else {
            die("Error preparing statement for getting password: " . $conn->error);
        }
    } else {
        header("Location: login.php?id=$user_id&error=credential");
        exit();
    }
}

$conn->close();
?>
