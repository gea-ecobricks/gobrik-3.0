<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$ecobricker_id = $_GET['id'] ?? null;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$first_name = '';
$email_addr = '';
$code_sent = false;
$version = '0.472';
$page = 'activate';

// PART 1: Check if ecobricker_id is passed in the URL
if (is_null($ecobricker_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No ecobricker ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 2: Look up user information using ecobricker_id provided in URL

//gobrik_conn creds
require_once ("../gobrikconn_env.php");

// Prepare and execute SQL statement to fetch user details
$sql_user_info = "SELECT first_name, email_addr FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_user_info = $gobrik_conn->prepare($sql_user_info);
if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $ecobricker_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($first_name, $email_addr);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
} else {
    die('Error preparing statement for fetching user info: ' . $gobrik_conn->error);
}

$gobrik_conn->close();

// PART 3: Handle form submission to send the confirmation code by email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    echo "Form submitted. Preparing to send email..."; // Debugging output

    require '../vendor/autoload.php'; // Path to PHPMailer

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
        $mail->Body = "Hello $first_name!<br><br>If you're reading this, we're glad! The code to activate your account is:<br><br><b>AYYEW</b><br><br>Return back to your browser and enter the code, or visit this page:<br>https://beta.gobrik.com/en/active.php?status=go&buwana_id=63 <br><br>The GoBrik team";

        if ($mail->send()) {
            echo "Email sent successfully."; // Debugging output
            $code_sent = true;
            echo '<script>alert("An email with your code has been sent!");</script>';
        } else {
            echo "Failed to send email."; // Debugging output
        }
    } catch (Exception $e) {
        echo '<script>alert("Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '");</script>';
    }
}
?>


<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Confirm Your Email</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/activate-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="regen-top top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <!-- Email confirmation form -->
        <div id="first-send-form" style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;" <?php if ($code_sent) echo 'class="hidden"'; ?>>
            <h2><?php echo htmlspecialchars($first_name); ?>, first let's confirm your email.</h2>
            <p>Click the send button to send a confirmation email to <?php echo htmlspecialchars($email_addr); ?> to receive your account activation code.</p>
            <form method="post" action="">
               <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                <div id="submit-section" style="text-align:center;margin-top:20px;padding-right:15px;padding-left:15px" title="Start Activation process">
                    <input type="submit" id="send_email" value="📨 Send email" class="submit-button activate">
                </div>
            </div>

            </form>
            <p style="font-size:1em;">Do you no longer use this email address? You'll need to <a href="signup.php">create a new account</a> or contact our team at support@gobrik.com.</p>
        </div>

        <!-- Code entry form -->
        <div id="second-code-confirm" <?php if (!$code_sent) echo 'class="hidden"'; ?>>
            <h2>Please enter your code:</h2>
            <p>Check your email <?php echo htmlspecialchars($email_addr); ?> for your account confirmation code. Enter it here:</p>

            <form id="code-form">
                <input type="text" maxlength="1" class="code-box" required>
                <input type="text" maxlength="1" class="code-box" required>
                <input type="text" maxlength="1" class="code-box" required>
                <input type="text" maxlength="1" class="code-box" required>
                <input type="text" maxlength="1" class="code-box" required>
            </form>

            <p id="code-feedback"></p>

            <p id="resend-code">Didn't get your code? You can request a resend of the code in <span id="timer">1:00</span></p>
        </div>

    </div>
</div>

</div>


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>


<script>
$(document).ready(function () {
    var code = "AYYEW";
    var countdownTimer;
    var timeLeft = 60;

    // Handle code entry
    $('.code-box').on('input', function () {
        var inputs = $('.code-box');
        var enteredCode = '';
        inputs.each(function () {
            enteredCode += $(this).val().toUpperCase();
        });

        if (enteredCode.length === 5) {
            if (enteredCode === code) {
                $('#code-feedback').text('Code confirmed!').addClass('success').removeClass('error');
                setTimeout(function () {
                    window.location.href = "activate-2.php";
                }, 2000);
            } else {
                $('#code-feedback').text('Code incorrect').addClass('error').removeClass('success');
            }
        }
    });

    // Countdown timer for resend code
    countdownTimer = setInterval(function () {
        if (timeLeft <= 0) {
            clearInterval(countdownTimer);
            $('#resend-code').html('<a href="#">Click here to resend the code</a>');
        } else {
            timeLeft--;
            $('#timer').text('0:' + (timeLeft < 10 ? '0' + timeLeft : timeLeft));
        }
    }, 1000);

    // Resend code logic
    $('#resend-code').on('click', 'a', function (e) {
        e.preventDefault();
        // Reset the timer
        timeLeft = 60;
        $('#timer').text('1:00');
        $('#resend-code').html('Didn\'t get your code? You can request a resend of the code in <span id="timer">1:00</span>');

        // Resend email logic
         $('form').submit();
    });
});
</script>
</body>
</html>
