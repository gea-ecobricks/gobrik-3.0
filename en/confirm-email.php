<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

error_reporting(E_ALL);
ini_set('display_errors', 1);
$is_logged_in = '';
// Check if the user is logged in
if (isLoggedIn()) {
    header('Location: dashboard.php'); // Redirect to dashboard if user is logged in
    exit();
}

// If not redirected, set $is_logged_in to false for this page
$is_logged_in = false;

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.763';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Initialize user variables
$ecobricker_id = $_GET['id'] ?? null;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$first_name = '';
$email_addr = '';
$code_sent = false;
$version = '0.48';
$page = 'activate';
$static_code = 'AYYEW'; // The static code for now
$generated_code = ''; // New generated code
$country_icon = '';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to PHPMailer

// PART 2 FUNCTIONS

// Function to generate a random 5-character alphanumeric code
function generateCode() {
    return strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

// Function to send the verification code email
function sendVerificationCode($first_name, $email_addr, $verification_code, $lang) {
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

        // Determine the email content based on the language
        switch ($lang) {
            case 'fr':
                $subject = 'Code de vérification GoBrik';
                $body = "Bonjour $first_name!<br><br>Si vous lisez ceci, un code d'activation pour votre compte GoBrik et Buwana a été demandé ! Le code pour activer votre compte est :<br><br><b>$verification_code</b><br><br>Retournez à votre navigateur et entrez le code.<br><br>L'équipe GoBrik";
                break;
            case 'es':
                $subject = 'Código de verificación de GoBrik';
                $body = "Hola $first_name!<br><br>¡Si estás leyendo esto, se ha solicitado un código de activación para tu cuenta de GoBrik y Buwana! El código para activar tu cuenta es:<br><br><b>$verification_code</b><br><br>Vuelve a tu navegador e ingresa el código.<br><br>El equipo de GoBrik";
                break;
            case 'in':
                $subject = 'Kode Verifikasi GoBrik';
                $body = "Halo $first_name!<br><br>Jika Anda membaca ini, kode aktivasi untuk akun GoBrik dan Buwana Anda telah diminta! Kode untuk mengaktifkan akun Anda adalah:<br><br><b>$verification_code</b><br><br>Kembali ke browser Anda dan masukkan kodenya.<br><br>Tim GoBrik";
                break;
            case 'en':
            default:
                $subject = 'GoBrik Verification Code';
                $body = "Hello $first_name!<br><br>If you're reading this, an activation code for your GoBrik and Buwana account has been requested! The code to activate your account is:<br><br><b>$verification_code</b><br><br>Return back to your browser and enter the code.<br><br>The GoBrik team";
                break;
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}


// PART 3: Check if ecobricker_id is passed in the URL
if (is_null($ecobricker_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No ecobricker ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}


// PART 4: Look up user information using ecobricker_id provided in URL
require_once("../gobrikconn_env.php");

$sql_user_info = "SELECT first_name, email_addr, gobrik_migrated, buwana_id FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_user_info = $gobrik_conn->prepare($sql_user_info);
if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $ecobricker_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($first_name, $email_addr, $gobrik_migrated, $buwana_id);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
} else {
    die('Error preparing statement for fetching user info: ' . $gobrik_conn->error);
}

// Check if buwana_id is empty and handle accordingly (if needed)
if (empty($buwana_id)) {
    // Handle the case where buwana_id is null or empty
    $buwana_id = null; // You can choose to set it to null or any default value if needed
}


// PART 5: Generate the code and update the activation_code field in the database
$generated_code = generateCode();

$sql_update_code = "UPDATE tb_ecobrickers SET activation_code = ? WHERE ecobricker_id = ?";
$stmt_update_code = $gobrik_conn->prepare($sql_update_code);
if ($stmt_update_code) {
    $stmt_update_code->bind_param('si', $generated_code, $ecobricker_id);
    $stmt_update_code->execute();
    $stmt_update_code->close();
} else {
    die('Error preparing statement for updating activation code: ' . $gobrik_conn->error);
}


//PART 6: Handle form submission to send the confirmation code by email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['send_email']) || isset($_POST['resend_email']))) {
    $code_sent = sendVerificationCode($first_name, $email_addr, $generated_code, $lang);
    if ($code_sent) {
        $code_sent_flag = true;
    } else {
        echo '<script>alert("Message could not be sent. Please try again later.");</script>';
    }
}

$gobrik_conn->close();

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
<div id="top-page-image" class="message-birded top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

       <!-- Email confirmation form -->
<div id="first-send-form" style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;"
    class="<?php echo $code_sent ? 'hidden' : ''; ?>"> <!-- Fix the inline PHP inside attributes -->

    <h2>Alright <?php echo htmlspecialchars($first_name); ?>, let's confirm your email.</h2>
    <p>To create your Buwana GoBrik account we need to confirm your chosen credential. This is how we'll keep in touch and keep your account secure.  Click the send button and we'll send an account activation code to:</p>

    <h3><?php echo htmlspecialchars($email_addr); ?></h3>
    <form method="post" action="">
        <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
            <div id="submit-section" style="text-align:center;margin-top:20px;padding-right:15px;padding-left:15px" title="Start Activation process">
                <input type="submit" name="send_email" id="send_email" value="📨 Send Code" class="submit-button activate">
            </div>
        </div>
    </form>
</div>

<!-- Code entry form -->
<div id="second-code-confirm" style="text-align:center;"
    class="<?php echo !$code_sent ? 'hidden' : ''; ?>"> <!-- Fix the inline PHP inside attributes -->

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

    <p id="resend-code" style="font-size:1em">Didn't get your code? You can request a resend of the code in <span id="timer">1:00</span></p>
</div>


<div id="legacy-account-email-not-used" style="text-align:center;width:90%;margin:auto;margin-top:30px;margin-bottom:50px;">
    <p style="font-size:1em;">Do you no longer use this email address?<br>If not, you'll need to <a href="signup.php">create a new account</a> or contact our team at support@gobrik.com.</p>
</div>

<div id="new-account-another-email-please" style="text-align:center;width:90%;margin:auto;margin-top:30px;margin-bottom:50px;">
    <p style="font-size:1em;">Want to change your email? <a href="signup-2.php?id=$buwana_id">Go back to enter a different email address.</a></p>
</div>

</div>

</div>
</div>

</div> <!--Closes main-->


<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const staticCode = "AYYEW";
    const generatedCode = <?php echo json_encode($generated_code); ?>;
    const ecobricker_id = <?php echo json_encode($ecobricker_id); ?>;
    let timeLeft = 60;

    // Ensure codeFeedback is declared to handle feedback messages
    const codeFeedback = document.querySelector('#code-feedback');

    // Handle code entry
    const codeBoxes = document.querySelectorAll('.code-box');
    codeBoxes.forEach((box, index) => {
        box.addEventListener('keyup', function(e) {
            if (box.value.length === 1 && index < codeBoxes.length - 1) {
                codeBoxes[index + 1].focus();
            }

            let enteredCode = '';
            codeBoxes.forEach(box => enteredCode += box.value.toUpperCase());

            if (enteredCode.length === 5) {
                // Check if the code matches either staticCode or the generated code
                if (enteredCode === staticCode || enteredCode === generatedCode) {
                    codeFeedback.textContent = 'Code confirmed!';
                    codeFeedback.classList.add('success');
                    codeFeedback.classList.remove('error');
                    setTimeout(function() {
                        // Redirect to activate-2.php with ecobricker_id as a parameter
                        window.location.href = "activate-2.php?id=" + ecobricker_id;
                    }, 1000);
                } else {
                    codeFeedback.textContent = 'Code incorrect';
                    codeFeedback.classList.add('error');
                    codeFeedback.classList.remove('success');
                }
            }
        });
    });

    // Handle the resend code timer
    let countdownTimer = setInterval(function() {
        timeLeft--;
        if (timeLeft <= 0) {
            clearInterval(countdownTimer);
            document.getElementById('resend-code').innerHTML = '<a href="resend-code.php?id=' + ecobricker_id + '">Resend the code now.</a>';
        } else {
            document.getElementById('timer').textContent = '0:' + (timeLeft < 10 ? '0' : '') + timeLeft;
        }
    }, 1000);

    // JavaScript function to show/hide divs based on gobrik_migrated
    function showDependingOnLegacy(gobrikMigrated) {
        if (gobrikMigrated === 1) {
            document.getElementById('legacy-account-email-not-used').style.display = 'block';
            document.getElementById('new-account-another-email-please').style.display = 'none';
        } else {
            document.getElementById('legacy-account-email-not-used').style.display = 'none';
            document.getElementById('new-account-another-email-please').style.display = 'block';
        }
    }

    // Fetch the gobrik_migrated value from PHP safely using json_encode
    var gobrikMigrated = <?php echo json_encode($gobrik_migrated); ?>;

    // Call the function with the retrieved value
    showDependingOnLegacy(gobrikMigrated);

    // Show/Hide Divs after email is sent
    var codeSent = <?php echo json_encode($code_sent_flag ?? false); ?>;  // Only set once
    if (codeSent) {
        document.getElementById('first-send-form').style.display = 'none';
        document.getElementById('second-code-confirm').style.display = 'block';
    }

    // Handle Resend Link Click
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'resend-link') {
            e.preventDefault();
            // Reset timer and form submission
            document.getElementById('resend-code-form').submit();
        }
    });
});
</script>


</body>
</html>
