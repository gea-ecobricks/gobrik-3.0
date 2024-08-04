<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $stmt = $buwana_conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Generate a new password (for demonstration purposes)
            $newPassword = bin2hex(random_bytes(4)); // Generates a random 8-character password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the user's password in the database
            $stmt = $buwana_conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashedPassword, $email);
            $stmt->execute();

            // Send the new password to the user's email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.ecobricks.org'; // Set the SMTP server to send through
                $mail->SMTPAuth = true;
                $mail->Username = 'gobrik@ecobricks.org'; // SMTP username
                $mail->Password = '1Welcome!'; // SMTP password
                $mail->SMTPSecure = 'tls'; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                $mail->Port = 587; // TCP port to connect to

                // Recipients
                $mail->setFrom('gobrik@ecobricks.org', 'GoBrik');
                $mail->addAddress($email); // Add a recipient

                // Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'Password Reset';
                $mail->Body    = "Your new password is: $newPassword";

                $mail->send();
                echo '<script>alert("An email with your password has been sent!"); window.location.href = "login.php";</script>';
            } catch (Exception $e) {
                echo '<script>alert("Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '"); window.location.href = "login.php";</script>';
            }
        } else {
            echo '<script>alert("Email not found!"); window.location.href = "login.php";</script>';
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo '<script>alert("Please enter a valid email address."); window.location.href = "login.php";</script>';
}

// Close the database connection
$buwana_conn->close();
?>
