<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

require '../vendor/autoload.php'; // Include Composer's autoloader

// Turn on or off error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../buwanaconn_env.php'; // This file provides the first database server, user, dbname information

// Validate the email input
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : '';

if ($email) {
    try {
        // Check if email exists in the database
        $stmt = $buwana_conn->prepare("SELECT email, first_name FROM users_tb WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $buwana_conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($result_email, $first_name);
        $stmt->fetch();
        $stmt->close();

        if ($result_email) {
            // Generate a unique token
            $password_reset_token = bin2hex(random_bytes(16)); // Generates a random 32-character token
            $password_reset_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Update the user's password reset token and deadline in the database
            $stmt = $buwana_conn->prepare("UPDATE users_tb SET password_reset_token = ?, password_reset_expires = ? WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $buwana_conn->error);
            }
            $stmt->bind_param("sss", $password_reset_token, $password_reset_expires, $email);
            $stmt->execute();
            $stmt->close();

            // Send the password reset link to the user's email
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'mail.ecobricks.org'; // Set the SMTP server to send through
                $mail->SMTPAuth = true;
                $mail->Username = 'gobrik@ecobricks.org'; // SMTP username
                $mail->Password = '1Welcome!'; // SMTP password
                $mail->SMTPSecure = false; // Disable SSL encryption
                $mail->Port = 26; // TCP port to connect to

                // Recipients
                $mail->setFrom('gobrik@ecobricks.org', 'GoBrik');
                $mail->addAddress($email); // Add a recipient

                // Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'Reset your GoBrik password';
                $mail->Body    = "Hello $first_name,<br><br>
                A password reset was requested at " . date('Y-m-d H:i:s') . " on GoBrik.com for your Buwana account. If you didn't request this, please disregard! To reset your password, please click the following link:<br><br>
                <a href='https://beta.gobrik.com/{$lang}/password-reset.php?token={$password_reset_token}'>Reset Password</a><br><br>
                Have a great and green day!<br><br>
                The GoBrik Team<br>
                gobrik@ecobricks.org<br>
                app: GoBrik.com<br>
                news: earthen.io<br>
                briks: ecobricks.org<br>
                ";

                $mail->send();
                echo '<script>alert("An email with a link to reset your GoBrik Buwana password has been sent!"); window.location.href = "../' . $lang . '/login.php";</script>';
            } catch (Exception $e) {
                echo '<script>alert("Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '"); window.location.href = "../' . $lang . '/login.php";</script>';
            }
        } else {
            // Redirect if the email is not found in the database
            header('Location: ../' . $lang . '/login.php?email_not_found&email=' . urlencode($email));
            exit();
        }
    } catch (Exception $e) {
        echo "<script>console.error('Error: " . $e->getMessage() . "');</script>";
    }
} else {
    // Invalid email input
    echo '<script>alert("Please enter a valid email address."); window.location.href = "../' . $lang . '/login.php";</script>';
}

// Close the database connection
$buwana_conn->close();
?>
