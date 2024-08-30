<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to PHPMailer

function sendVerificationCode($first_name, $email_addr, $verification_code) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.ecobricks.org';
        $mail->SMTPAuth = true;
        $mail->Username = 'gobrik@ecobricks.org';
        $mail->Password = '1Welcome!';
        $mail->SMTPSecure = false;
        $mail->Port = 26;

        // Recipients
        $mail->setFrom('gobrik@ecobricks.org', 'GoBrik Team');
        $mail->addAddress($email_addr);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'GoBrik Verification Code';
        $mail->Body = "Hello $first_name!<br><br>If you're reading this, we're glad! The code to activate your account is:<br><br><b>$verification_code</b><br><br>Return back to your browser and enter the code.<br><br>The GoBrik team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
