<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));

require '../vendor/autoload.php'; // Include Composer's autoloader

// Turn on or off error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if ($email) {
    try {
        // Check if email exists in the database
        $stmt = $buwana_conn->prepare("SELECT email FROM users_tb WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $buwana_conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($result_email);
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

            // Capture PHPMailer debug output
            ob_start();

            // Send the password reset link to the user's email
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->SMTPDebug = 2; // Enable verbose debug output
                $mail->Debugoutput = function($str, $level) {
                    echo "Debug level $level; message: $str\n";
                };
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
                $mail->Subject = 'Password Reset';
                $mail->Body    = "Please click the following link to reset your password: <a href='https://beta.gobrik.com/{$lang}/password-reset.php?token={$password_reset_token}'>Reset Password</a>";

                $mail->send();
                echo '<script>alert("An email with your password reset link has been sent!"); window.location.href = "../' . $lang . '/login.php";</script>';
            } catch (Exception $e) {
                $debug_output = ob_get_clean();
                $debug_output = htmlspecialchars($debug_output, ENT_QUOTES); // Sanitize output for JavaScript
                echo "<script>console.error('SMTP Debug Output:\\n$debug_output');</script>";
                echo '<script>alert("Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '"); window.location.href = "../' . $lang . '/login.php";</script>';
            }
        } else {
            echo '<script>
                    function showNoBuwanaEmail() {
                        var noBuwanaEmail = document.getElementById("no-buwana-email");
                        if (noBuwanaEmail) {
                            noBuwanaEmail.style.display = "block";
                        } else {
                            setTimeout(showNoBuwanaEmail, 1000); // Try again in 100ms
                        }
                    }
                    showNoBuwanaEmail();
                  </script>';
        }
    } catch (Exception $e) {
        echo "<script>console.error('Error: " . $e->getMessage() . "');</script>";
    }
} else {
    echo '<script>alert("Please enter a valid email address."); window.location.href = "../' . $lang . '/login.php";</script>';
}

// Close the database connection
$buwana_conn->close();
?>
