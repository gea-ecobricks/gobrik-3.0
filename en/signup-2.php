<?php
$directory = basename(dirname($_SERVER['SCRIPT_NAME']));
$lang = $directory;
$version = '0.371';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;
$user_id = $_GET['id'] ?? null;
$duplicate_email_error = false;

include '../buwana_env.php'; // This file provides the database server, user, dbname information to access the server

// PART 1: Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Looks like you already have an account and are logged in! Let\'s take you to your dashboard.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}

// Initialize variables
$credential_type = '';
$credential_key = '';
$first_name = '';
$account_status = '';

if (isset($user_id)) {
    // Look up the credential_type and credential_key from credentials_tb
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

    // Look up the first_name and account_status from users_tb
    $sql_lookup_user = "SELECT first_name, account_status FROM users_tb WHERE user_id = ?";
    $stmt_lookup_user = $conn->prepare($sql_lookup_user);
    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $user_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name, $account_status);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement for users_tb: " . $conn->error);
    }

    // Sanitize output
    $credential_type = htmlspecialchars($credential_type);
    $first_name = htmlspecialchars($first_name);

    // Check the account_status
    if ($account_status !== 'name set only') {
        echo "<script>
            alert('Sorry! It looks like the credentials for this account have already been set. Use your account management panel to change your password.');
            window.location.href='update-account.php';
        </script>";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    // Retrieve and sanitize form data
    $credential_value = htmlspecialchars($_POST['credential_value']);
    $password = $_POST['password_hash'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // CHECKS: Check if the email is already used
    $sql_check_email = "SELECT COUNT(*) FROM users_tb WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    if ($stmt_check_email) {
        $stmt_check_email->bind_param("s", $credential_value);
        $stmt_check_email->execute();
        $stmt_check_email->bind_result($email_count);
        $stmt_check_email->fetch();
        $stmt_check_email->close();

        if ($email_count > 0) {
            $duplicate_email_error = true;
        } else {
            // Update the credentials_tb with the credential_key
            $sql_update_credential = "UPDATE credentials_tb SET credential_key = ? WHERE user_id = ?";
            $stmt_update_credential = $conn->prepare($sql_update_credential);
            if ($stmt_update_credential) {
                $stmt_update_credential->bind_param("si", $credential_value, $user_id);
                if ($stmt_update_credential->execute()) {
                    // Update the users_tb with the password, email, and change the account status
                    $sql_update_user = "UPDATE users_tb SET password_hash = ?, email = ?, account_status = 'registered no login' WHERE user_id = ?";
                    $stmt_update_user = $conn->prepare($sql_update_user);
                    if ($stmt_update_user) {
                        $stmt_update_user->bind_param("ssi", $password_hash, $credential_value, $user_id);
                        if ($stmt_update_user->execute()) {
                            $success = true;
                            // Redirect to signedup-login.php with user_id
                            header("Location: signedup-login.php?id=$user_id");
                            exit();
                        } else {
                            error_log("Error executing user update statement: " . $stmt_update_user->error);
                            echo "An error occurred while updating your account. Please try again.";
                        }
                        $stmt_update_user->close();
                    } else {
                        error_log("Error preparing user update statement: " . $conn->error);
                        echo "An error occurred while updating your account. Please try again.";
                    }
                } else {
                    error_log("Error executing credential update statement: " . $stmt_update_credential->error);
                    echo "An error occurred while updating your account. Please try again.";
                }
                $stmt_update_credential->close();
            } else {
                error_log("Error preparing credential update statement: " . $conn->error);
                echo "An error occurred while updating your account. Please try again.";
            }
        }
    } else {
        die("Error preparing email check statement: " . $conn->error);
    }

    $conn->close();
}
?>





<title>Register Email | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/signup-inc.php");?>


<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->

<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">

        <div class="my-ecobricks" style="margin-top:-45px;">
        <img src="../webps/earth-community.webp" width="60%">
    </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-signup-heading2">Setup Your Access</h2>
            <p>Alright <span data-lang-id="002-alright"><?php echo $first_name; ?></span>:<span data-lang-id="002-let-use-you"> Let's use your</span> <?php echo $credential_type; ?> <span data-lang-id="003-as-your-means">as your means of registration and the way we contact you.</span></p>
        </div>

       <!--SIGNUP FORM-->



<form id="password-confirm-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . htmlspecialchars($user_id); ?>">
    <div class="form-item" id="credential-section">
        <label for="credential_value"><span data-lang-id="004-your">Your</span> <?php echo $credential_type; ?> please:</label><br>
        <input type="text" id="credential_value" name="credential_value" required>
        <p class="form-caption" data-lang-id="006-email-subcaption">💌 This is the way we will contact you to confirm your account</p>
        <?php if ($duplicate_email_error): ?>
                <div id="duplicate-email-error" class="form-field-error" style="margin-top:10px;" data-lang-id="010-pass-error-no-match">🚧 Whoops! Looks like that e-mail address is already being used by a Buwana Account. Please choose another.</div>
            <?php endif; ?>

    </div>

    <div class="form-item" id="set-password" style="display: none;">
        <label for="password_hash" data-lang-id="007-set-your-pass">Set your password:</label><br>
        <input type="password" id="password_hash" name="password_hash" required minlength="6">
        <p class="form-caption" data-lang-id="008-password-advice">🔑 Your password must be at least 6 characters.</p>
    </div>

    <div class="form-item" id="confirm-password-section" style="display: none;">
        <label for="confirm_password" data-lang-id="009-confirm-pass">Confirm Your Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <div id="maker-error-invalid" class="form-field-error" style="margin-top:10px;"  data-lang-id="010-pass-error-no-match">👉 Passwords do not match.</div>
    </div>

    <div class="form-item" id="human-check-section" style="display: none;">
        <label for="human_check" data-lang-id="011-prove-human">Please prove you are human by typing the word "ecobrick" below:</label><br>
        <input type="text" id="human_check" name="human_check" required>
        <p class="form-caption" data-lang-id="012-fun-fact"> 🤓 Fun fact: <a href="#" onclick="showModalInfo('ecobrick')" class="underline-link">'ecobrick'</a> is spelled without a space, capital or hyphen!</p>
        <div>
            <input type="checkbox" id="terms" name="terms" required checked>
            <label for="terms" style="font-size:medium;" class="form-caption" data-lang-id="013-by-registering">By registering today, I agree to the <a href="#" onclick="showModalInfo('terms')" class="underline-link">GoBrik Terms of Service</a></label>
        </div>
        <div>
            <input type="checkbox" id="newsletter" name="newsletter" checked>
            <label for="newsletter" style="font-size:medium;" class="form-caption" data-lang-id="014-i-agree-newsletter">I agree to receive the <a href="#" onclick="showModalInfo('earthen')" class="underline-link">Earthen newsletter</a> for app, ecobrick, and earthen updates</label>
        </div>
    </div>
 <!--<button  type="submit" id="submit-button" aria-label="Submit Form" class="enabled">
        🔐 <span data-lang-id="016-submit-to-register" id="submit-button-text">Register</span>
    </button>-->
    <div class="form-item" id="submit-section" style="display:none;text-align:center;margin-top:15px;" title="Be sure you wrote ecobrick correctly!">
        <input type="submit" id="submit-button" value="Register" disabled>
    </div>
</form>

        </div>


 <div style="text-align:center;width:100%;margin:auto;"><p style="font-size:medium;" data-land-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
        </div>

    </div><!--closes Landing content-->
</div>

</div><!--closes main and starry background-->

<!--FOOTER STARTS HERE-->

<?php require_once ("../footer-2024.php");?>

</div><!--close page content-->




    <script type="text/javascript">


function showModalInfo(type) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');
    let content = '';
    photobox.style.display = 'none';
    switch (type) {
        case 'terms':
            content = `
                <div style="font-size: small;">
                    <?php include "../files/terms-$lang.php"; ?>
                </div>
            `;
            modal.style.position = 'absolute';
            modal.style.overflow = 'auto';
            modalBox.style.textAlign = 'left';
            modalBox.style.maxHeight = 'unset';
            modalBox.style.marginTop = '30px';
            modalBox.style.marginBottom = '30px';
            modalBox.scrollTop = 0;
            modal.style.alignItems = 'flex-start';

            break;
        case 'earthen':
            content = `
                <img src="../svgs/earthen-newsletter-logo.svg" alt="Earthen Newsletter" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Earthen Newsletter</div>
                <div class="preview-text">We use our Earthen email newsletter to keep our users informed of the latest developments in the plastic transition movement and the world of ecobricking.  Free with your GoBrik account or unclick to opt-out. We use ghost.org's open source newsletter platform that makes it easy to unsubscribe anytime.</div>
            `;
            break;
        case 'ecobrick':
            content = `
                <img src="../webps/faqs-400px.webp" alt="Ecobrick Term and Types" height="200px" width="200px" class="preview-image">
                <div class="preview-title">The Term</div>
                <div class="preview-text">In 2016 plastic transition leaders around the world, agreed to use the non-hyphenated, non-capitalize term ‘ecobrick’ as the consistent, standardized term of reference in the guidebook and their materials. In this way, ecobrickers around the world would be able to refer with one word to same concept and web searches and hashtags would accelerate global dissemination.</div>
            `;
            break;
        default:
            content = '<p>Invalid term selected.</p>';
    }

    messageContainer.innerHTML = content;


    // Show the modal and update other page elements
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}








    document.addEventListener('DOMContentLoaded', function() {
    const credentialField = document.getElementById('credential_value');
    const passwordField = document.getElementById('password_hash');
    const confirmPasswordField = document.getElementById('confirm_password');
    const humanCheckField = document.getElementById('human_check');
    const termsCheckbox = document.getElementById('terms');
    const submitButton = document.getElementById('submit-button');
    const confirmPasswordSection = document.getElementById('confirm-password-section');
    const humanCheckSection = document.getElementById('human-check-section');
    const submitSection = document.getElementById('submit-section');
    const setPasswordSection = document.getElementById('set-password');
    const makerErrorInvalid = document.getElementById('maker-error-invalid');

    // Initially show only the credential field
    setPasswordSection.style.display = 'none';
    confirmPasswordSection.style.display = 'none';
    humanCheckSection.style.display = 'none';
    submitSection.style.display = 'none';

    // Function to validate email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Show password field when a valid email is entered
    credentialField.addEventListener('input', function() {
        if (isValidEmail(credentialField.value)) {
            setPasswordSection.style.display = 'block';
        } else {
            setPasswordSection.style.display = 'none';
            confirmPasswordSection.style.display = 'none';
            humanCheckSection.style.display = 'none';
            submitSection.style.display = 'none';
        }
    });

    // Show confirm password field when password length is at least 6 characters
    passwordField.addEventListener('input', function() {
        if (passwordField.value.length >= 6) {
            confirmPasswordSection.style.display = 'block';
        } else {
            confirmPasswordSection.style.display = 'none';
            humanCheckSection.style.display = 'none';
            submitSection.style.display = 'none';
        }
    });

    // Show human check section and submit button when passwords match
    confirmPasswordField.addEventListener('input', function() {
        if (passwordField.value === confirmPasswordField.value) {
            makerErrorInvalid.style.display = 'none';
            humanCheckSection.style.display = 'block';
            submitSection.style.display = 'block';
        } else {
            makerErrorInvalid.style.display = 'block';
            humanCheckSection.style.display = 'none';
            submitSection.style.display = 'none';
        }
    });

    // Activate submit button when "ecobrick" is typed and terms checkbox is checked
    function updateSubmitButtonState() {
        if (humanCheckField.value.toLowerCase() === 'ecobrick' && termsCheckbox.checked) {
            submitButton.classList.remove('disabled');
            submitButton.classList.add('enabled');
            submitButton.disabled = false;
        } else {
            submitButton.classList.remove('enabled');
            submitButton.classList.add('disabled');
            submitButton.disabled = true;
        }
    }

    humanCheckField.addEventListener('input', updateSubmitButtonState);
    termsCheckbox.addEventListener('change', updateSubmitButtonState);
});

</script>





</body>
</html>
