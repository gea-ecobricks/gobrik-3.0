<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'confirm_code.php'; // Include the sendVerificationCode function

// Initialize variables
$ecobricker_id = $_GET['id'] ?? null;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$first_name = '';
$email_addr = '';
$code_sent = false;
$version = '0.474';
$page = 'activate';
$static_code = 'AYYEW'; // The static code for now
$generated_code = ''; // New generated code

// Function to generate a random 5-character alphanumeric code
function generateCode() {
    return strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}



// PART 1: Check if ecobricker_id is passed in the URL
if (is_null($ecobricker_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No ecobricker ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}


// PART 2: Look up user information using ecobricker_id provided in URL
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


// PART 2.5: Generate the code and update the activation_code field in the database
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

$gobrik_conn->close();

//PART 3: Handle form submission to send the confirmation code by email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['send_email']) || isset($_POST['resend_email']))) {
    $code_sent = sendVerificationCode($first_name, $email_addr, $generated_code);
    if ($code_sent) {
        $code_sent_flag = true;
    } else {
        echo '<script>alert("Message could not be sent. Please try again later.");</script>';
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
    var staticCode = "AYYEW";
    var generatedCode = <?php echo json_encode($generated_code); ?>;
    var countdownTimer;
    var timeLeft = 60;

    // Fetch buwana_id from PHP safely using json_encode to prevent line break issues
    var ecobricker_id = <?php echo json_encode($ecobricker_id); ?>;

    // Handle code entry
    var codeBoxes = document.querySelectorAll('.code-box');
    codeBoxes.forEach((box, index) => {
        box.addEventListener('keyup', function(e) {
            if (box.value.length == 1 && index < codeBoxes.length - 1) {
                codeBoxes[index + 1].focus();
            }
            var enteredCode = '';
            codeBoxes.forEach(box => enteredCode += box.value.toUpperCase());

            if (enteredCode.length === 5) {
                // Check if the code matches either AYYEW or the generated code
                if (enteredCode === staticCode || enteredCode === generatedCode) {
                    setTimeout(function() {
                        // Redirect to activate-2.php with buwana_id as a parameter
                        window.location.href = "activate-2.php?id=" + ecobricker_id;
                    }, 2000);
                } else {
                    document.getElementById('code-feedback').innerText = 'Incorrect code. Please try again.';
                    codeBoxes.forEach(box => box.value = '');
                    codeBoxes[0].focus();
                }
            }
        });
    });

    // Handle the resend code timer
    countdownTimer = setInterval(function() {
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
});

</script>

</body>
</html>
