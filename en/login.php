<?php
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));  //grabs language directory from url
$version = '0.521';
$page = 'login';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';

// Turn on or off error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and session active
session_start();
if (isset($_SESSION['buwana_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>


<script>
// Function to validate password
function validatePassword(isValid) {
    const passwordErrorDiv = document.getElementById('password-error');
    if (!isValid) {
        passwordErrorDiv.style.display = 'flex';
    } else {
        passwordErrorDiv.style.display = 'none';
    }
}

// Function to show modal information
function showModalInfo(type) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');
    let content = '';
    photobox.style.display = 'none';
    switch (type) {
        case 'reset':
            content = `
                <img src="../pngs/exchange-bird.png" alt="Reset Password" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Reset Password</div>
                <div class="preview-text">Oops! This function is not yet operational. Create another account for the moment as all accounts will be deleted once we migrate from beta to live.</div>
            `;
            break;
        default:
            content = '<p>Invalid term selected.</p>';
    }
    messageContainer.innerHTML = content;

    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

// Check if there's an error message and show the error div if needed
document.addEventListener("DOMContentLoaded", function() {
    const errorType = "<?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>";
    if (errorType === "invalid_password") {
        validatePassword(false);
    }
});

// Form submission validation
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('login').addEventListener('submit', function(event) {
        var credentialValue = document.getElementById('credential_key').value;
        var password = document.getElementById('password').value;

        if (credentialValue === '' || password === '') {
            event.preventDefault();
            document.getElementById('password-error').style.display = 'block';
        }
    });
});
</script>
<?php require_once ("../includes/login-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="signup-team top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h3 data-lang-id="001-login-heading">Welcome back!</h3>
            <h4 data-lang-id="002-login-subheading" style="margin-top:5px, margin-bottom:5px;">Login with your GoBrik or Buwana account credentials.</h4>
        </div>

        <!-- Login form -->
        <form id="login" method="post" action="login_process.php">
            <div class="form-item">
                <span data-lang-id="003-login-email">
                    <input type="text" id="credential_key" name="credential_key" required placeholder="Your e-mail...">
                <span>
            </div>
            <div class="form-item">
                <div data-lang-id="004-login-password">
                    <input type="password" id="password" name="password" required placeholder="Your password...">
                </div>
                    <div id="password-error" class="form-field-error" style="display:none;" data-lang-id="000-password-wrong">👉 Password is wrong.</div>
                    <p class="form-caption" data-lang-id="005-forgot-password">Forget your password? <a href="#" onclick="showModalInfo('reset')" class="underline-link">Reset it.</a></p>
            </div>
            <div style="text-align:center;" data-lang-id="006-login-button-">
                <input type="submit" style="text-align:center;margin-top:15px;width:30%; min-width: 175px;" id="submit-button" value="🔑 Login" class="submit-button enabled">
            </div>
        </form>
    </div>
<div style="text-align:center;width:100%;margin:auto;margin-top:30px;margin-bottom:50px;">
    <p style="font-size:medium;" data-lang-id="000-no-account-yet">Don't have an account yet? <a href="signup.php">Signup!</a></p>
</div>
</div>

</div>

</div> <!--main-->

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php");?>
</body>
</html>
