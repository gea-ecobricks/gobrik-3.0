<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$response = ['success' => false];
$buwana_id = $_GET['id'] ?? null;
$directory = basename(dirname($_SERVER['SCRIPT_NAME']));
$lang = $directory;
$version = '0.457';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$credential_type = '';
$credential_key = '';
$first_name = '';
$account_status = '';

include '../buwanaconn_env.php'; // Database connection

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Look up user information if buwana_id is provided
if ($buwana_id) {
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE buwana_id = ?";
    $stmt_lookup_credential = $buwana_conn->prepare($sql_lookup_credential);
    if ($stmt_lookup_credential) {
        $stmt_lookup_credential->bind_param("i", $buwana_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        $response['error'] = 'db_error';
    }

    $sql_lookup_user = "SELECT first_name, account_status FROM users_tb WHERE buwana_id = ?";
    $stmt_lookup_user = $buwana_conn->prepare($sql_lookup_user);
    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $buwana_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name, $account_status);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        $response['error'] = 'db_error';
    }

    $credential_type = htmlspecialchars($credential_type);
    $first_name = htmlspecialchars($first_name);

    if ($account_status !== 'name set only') {
        $response['error'] = 'account_status';
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Sign Up | Step 2 | GoBrik</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/signup-inc.php");?>


<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="credentials-banner top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">


            <div style="text-align:center;width:100%;margin:auto;">
                <h2 data-lang-id="001-page2-heading">Setup Your Access</h2>
                <p><span data-lang-id="002-page2-heading">Alright </span> <?php echo $first_name; ?>:<span data-lang-id="002-let-use-you"> Let's use your</span> <?php echo $credential_type; ?> <span data-lang-id="003-as-your-means">as your means of registration and the way we contact you.</span></p>
            </div>


            <!--SIGNUP FORM-->
            <form id="password-confirm-form" method="post" action="signup_process.php?id=<?php echo htmlspecialchars($buwana_id); ?>">
                <div class="form-item" id="credential-section">
                    <label for="credential_value"><span data-lang-id="004-your">Your</span> <?php echo $credential_type; ?> please:</label><br>
                    <div id="duplicate-email-error" class="form-field-error" style="margin-top:10px;margin-bottom:-13px;" data-lang-id="010-duplicate-email">🚧 Whoops! Looks like that e-mail address is already being used by a Buwana Account. Please choose another.</div>
                    <div id="duplicate-gobrik-email" class="form-warning" style="margin-top:10px;margin-bottom:-13px;" ><span data-lang-id="010-gobrik-duplicate">🌏 It looks like this email is already being used with a legacy GoBrik account. Please <a href="login.php" class="underline-link">login with this email to upgrade your account.</a></div>

                    <div class="input-container">
                        <input type="text" id="credential_value" name="credential_value" required style="padding-left:45px;" aria-label="your email">
                        <div id="loading-spinner" class="spinner" style="display: none;"></div>
                    </div>
                <p class="form-caption" data-lang-id="006-email-subcaption">💌 This is the way we will contact you to confirm your account</p>
                </div>

                <div class="form-item" id="set-password" style="display: none;">
                    <label for="password_hash" data-lang-id="007-set-your-pass">Set your password:</label><br>
                    <div class="password-wrapper">
                        <input type="password" id="password_hash" name="password_hash" required minlength="6">
                        <span toggle="#password_hash" class="toggle-password" style="cursor: pointer;">🔒</span>
                    </div>
                    <p class="form-caption" data-lang-id="008-password-advice">🔑 Your password must be at least 6 characters.</p>
                </div>

                <div class="form-item" id="confirm-password-section" style="display: none;">
                    <label for="confirm_password" data-lang-id="009-confirm-pass">Confirm Your Password:</label><br>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <span toggle="#confirm_password" class="toggle-password" style="cursor: pointer;">🔒</span>
                    </div>
                    <div id="maker-error-invalid" class="form-field-error" style="margin-top:10px;" data-lang-id="010-pass-error-no-match">👉 Passwords do not match.</div>
                </div>


                <div class="form-item" id="human-check-section" style="display: none;">
                    <label for="human_check" data-lang-id="011-prove-human">Please prove you are human by typing the word "ecobrick" below:</label><br>
                    <input type="text" id="human_check" name="human_check" required>
                    <p class="form-caption" data-lang-id="012-fun-fact"> 🤓 Fun fact: <a href="#" onclick="showModalInfo('ecobrick', '<?php echo $lang; ?>')" class="underline-link">Ecobrick</a> is spelled without a space, capital or hyphen!</p>
                    <div>
                        <input type="checkbox" id="terms" name="terms" required checked>
                        <label for="terms" style="font-size:medium;" class="form-caption" data-lang-id="013-by-registering">By registering today, I agree to the <a href="#" onclick="showModalInfo('terms', '<?php echo $lang; ?>')" class="underline-link">GoBrik Terms of Service</a></label>
                    </div>
                    <div>
                        <input type="checkbox" id="newsletter" name="newsletter" checked>
                        <label for="newsletter" style="font-size:medium;" class="form-caption" data-lang-id="014-i-agree-newsletter">I agree to receive the <a href="#" onclick="showModalInfo('earthen', '<?php echo $lang; ?>')" class="underline-link">Earthen newsletter</a> for app, ecobrick, and earthen updates</label>
                    </div>
                </div>

                <div id="submit-section" style="display:none;text-align:center;margin-top:15px;" title="Be sure you wrote ecobrick correctly!">
                    <input type="submit" id="submit-button" value="Register" class="submit-button disabled">
                </div>
            </form>


        </div>

     <div style="text-align:center;width:100%;margin:auto;margin-top: 50px;">
                <p style="font-size:medium;" data-land-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
            </div>


    </div>
</div>

    <!--FOOTER STARTS HERE-->
    <?php require_once ("../footer-2024.php"); ?>


<script>
$(document).ready(function() {
    // Elements
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
    const duplicateEmailError = $('#duplicate-email-error');
    const duplicateGobrikEmail = $('#duplicate-gobrik-email');
    const loadingSpinner = $('#loading-spinner');

    // Initially hide all sections except the email field
    setPasswordSection.style.display = 'none';
    confirmPasswordSection.style.display = 'none';
    humanCheckSection.style.display = 'none';
    submitSection.style.display = 'none';

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Live email checking and validation
    $('#credential_value').on('input blur', function() {
        const email = $(this).val();

        if (isValidEmail(email)) {
            loadingSpinner.removeClass('green red').show();

            $.ajax({
                url: 'check_email.php',
                type: 'POST',
                data: { credential_value: email },
                success: function(response) {
                    loadingSpinner.hide();

                    try {
                        var res = JSON.parse(response);
                    } catch (e) {
                        console.error("Invalid JSON response", response);
                        alert("An error occurred while checking the email.");
                        return;
                    }

                    // Handle different responses
                    if (res.success) {
                        duplicateEmailError.hide();
                        duplicateGobrikEmail.hide();
                        loadingSpinner.removeClass('red').addClass('green').show();
                        setPasswordSection.style.display = 'block';
                    } else if (res.error === 'duplicate_email') {
                        duplicateEmailError.show();
                        duplicateGobrikEmail.hide();
                        loadingSpinner.removeClass('green').addClass('red').show();
                        setPasswordSection.style.display = 'none';
                    } else if (res.error === 'duplicate_gobrik_email') {
                        duplicateGobrikEmail.show();
                        duplicateEmailError.hide();
                        loadingSpinner.removeClass('red').addClass('green').show();
                        setPasswordSection.style.display = 'none'; // don't allow user to proceed with password setup
                    } else {
                        alert("An error occurred: " + res.error);
                    }
                },
                error: function() {
                    loadingSpinner.hide();
                    alert('An error occurred while checking the email. Please try again.');
                }
            });
        } else {
            setPasswordSection.style.display = 'none'; // Hide password section if email is invalid
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

    // Form submission
    $('#password-confirm-form').on('submit', function(e) {
        e.preventDefault(); // Prevent the form from submitting normally
        loadingSpinner.removeClass('green red').show();

        $.ajax({
            url: 'signup_process.php?id=<?php echo htmlspecialchars($buwana_id); ?>',
            type: 'POST',
            data: $(this).serialize(), // Serialize the form data
            success: function(response) {
                loadingSpinner.hide();
                try {
                    var res = JSON.parse(response);
                } catch (e) {
                    alert('An error occurred while processing the form.');
                    return;
                }

                if (res.success) {
                    window.location.href = res.redirect || 'confirm-email.php?id=<?php echo htmlspecialchars($buwana_id); ?>';
                } else if (res.error === 'duplicate_email') {
                    duplicateEmailError.show();
                    duplicateGobrikEmail.hide();
                    loadingSpinner.removeClass('green').addClass('red').show();
                } else if (res.error === 'duplicate_gobrik_email') {
                    duplicateGobrikEmail.show();
                    duplicateEmailError.hide();
                    loadingSpinner.removeClass('red').addClass('green').show();
                } else {
                    alert('An unexpected error occurred. Please try again.');
                }
            },
            error: function() {
                loadingSpinner.hide();
                alert('An error occurred while processing the form. Please try again.');
            }
        });
    });
});


/* Control the header position as the page scrolls*/


let lastScrollTop = 0;

window.onscroll = function() {
    scrollLessThan40();
    scrollMoreThan40();
};

function scrollLessThan40() {
    if (window.pageYOffset <= 40) {

        document.getElementById("header").style.height = "80px";
        document.getElementById("header").style.borderBottom = "none";
        document.getElementById("header").style.boxShadow = "none";
        document.getElementById("gea-logo").style.width = "185px";
        document.getElementById("gea-logo").style.height = "36px";
        document.getElementById("logo-gobrik").style.opacity = "1";
        document.getElementById("header").style.top = "0";
        document.getElementById("settings-buttons").style.padding = "16px 43px 16px 12px";
        document.getElementById("language-menu-slider").style.top = "-15px";
        document.getElementById("login-menu-slider").style.top = "-15px";
    }
}

function scrollMoreThan40() {
    if (window.pageYOffset >= 40) {
       alert("hello!!")
        document.getElementById("header").style.height = "55px";
        document.getElementById("header").style.borderBottom = "var(--header-accent) 0.5px solid";
        document.getElementById("header").style.boxShadow = "0px 0px 15px rgba(0, 0, 10, 0.805)";
        document.getElementById("gea-logo").style.width = "165px";
        document.getElementById("gea-logo").style.height = "33px";
        document.getElementById("logo-gobrik").style.opacity = "0.9";
        document.getElementById("settings-buttons").style.padding = "14px 43px 16px 12px";
        document.getElementById("language-menu-slider").style.top = "-35px";
        document.getElementById("login-menu-slider").style.top = "-35px";
    }
}

</script>





</body>
</html>
