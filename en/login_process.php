<?php
session_start();

// PART 1
$credential_key = $_POST['credential_key'] ?? '';
$password = $_POST['password'] ?? '';
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

if (empty($credential_key) || empty($password)) {
    header("Location: ../$lang/login.php?error=empty_fields");
    exit();
}

// PART 2: Check the GoBrik database
$servername = "localhost";
$username = "ecobricks_brikchain_viewer";
$password = "desperate-like-the-Dawn";
$dbname = "ecobricks_gobrik_msql_db";

$conn2 = new mysqli($servername, $username, $password, $dbname);
if ($conn2->connect_error) {
    error_log("Connection failed: " . $conn2->connect_error);
    header("Location: login.php?error=db_connection_failed");
    exit();
}

$sql_check_email = "SELECT ecobricker_id, buwana_activated FROM ecobricker_live_id WHERE email = ?";
$stmt_check_email = $conn2->prepare($sql_check_email);
if ($stmt_check_email) {
    $stmt_check_email->bind_param('s', $credential_key);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows === 1) {
        $stmt_check_email->bind_result($ecobricker_id, $buwana_activated);
        $stmt_check_email->fetch();

        if ($buwana_activated === 'yes') {
            header("Location: activate.php?user_id=$ecobricker_id");
            exit();
        }

        $stmt_check_email->close();
    } else {
        $stmt_check_email->close();
    }
} else {
    die('Error preparing statement for checking email: ' . $conn2->error);
}

// PART 3: Check Buwana user credentials
include '../buwana_env.php';

$sql_credential = "SELECT buwana_id FROM credentials_tb WHERE credential_key = ?";
$stmt_credential = $conn->prepare($sql_credential);
if ($stmt_credential) {
    $stmt_credential->bind_param('s', $credential_key);
    $stmt_credential->execute();
    $stmt_credential->store_result();

    if ($stmt_credential->num_rows === 1) {
        $stmt_credential->bind_result($buwana_id);
        $stmt_credential->fetch();
        $stmt_credential->close();

        $sql_user = "SELECT password_hash FROM users_tb WHERE buwana_id = ?";
        $stmt_user = $conn->prepare($sql_user);
        if ($stmt_user) {
            $stmt_user->bind_param('i', $buwana_id);
            $stmt_user->execute();
            $stmt_user->store_result();

            if ($stmt_user->num_rows === 1) {
                $stmt_user->bind_result($password_hash);
                $stmt_user->fetch();

                if (password_verify($password, $password_hash)) {
                    $_SESSION['buwana_id'] = $buwana_id;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    header("Location: login.php?error=invalid_password");
                    exit();
                }
            } else {
                header("Location: login.php?error=invalid_user");
                exit();
            }
            $stmt_user->close();
        } else {
            die('Error preparing statement for users_tb: ' . $conn->error);
        }
    } else {
        header("Location: login.php?error=invalid_credential");
        exit();
    }
} else {
    die('Error preparing statement for credentials_tb: ' . $conn->error);
}

$conn->close();
$conn2->close();
?>
