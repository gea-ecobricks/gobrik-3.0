<?php
$directory = basename(dirname($_SERVER['SCRIPT_NAME']));  //grabs language directory from url
$lang = $directory;
$version = '0.38';
$page = 'login';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';

//Turn on or off error reporting

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if a session is already active
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>


<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/blob/main/en/login.php-->


<!--this file contains the custom metac tags for this page along with the loading of our header and custom css-->
<?php require_once ("../includes/login-inc.php");?>

<!-- PAGE CONTENT-->

<div class="splash-content-block"></div>
<div id="splash-bar"></div>
<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">

          <div class="earth-community" style="margin-top:-65px;margin-bottom:-10px">
            <img src="../webps/earth-community.webp" style="width:65%;">
        </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-login-heading">Login</h2>
            <p data-lang-id="002-login-subheading">Welcome back to GoBrik!</p>
        </div>

        <!-- Login form -->
          <form id="login" method="post" action="login_process.php">
            <div class="form-item">
                <!--<label for="credential_value">Your e-mail:</label><br>-->
                <span data-lang-id="003-login-email">
                    <input type="text" id="credential_value" name="credential_value" required placeholder="Your e-mail...">
                <span>
            </div>
            <div class="form-item" data-lang-id="004-login-password">

                <!--<label for="password">Your Password:</label><br>-->
                <input type="password" id="password" name="password" required placeholder="Your password..">
                <p class="form-caption">Forget your password? <a href="#" onclick="showModalInfo('reset')" class="underline-link">Reset it.</a></p>
                <div id="password-error" class="form-field-error" style="display:none;">👉 Password is wrong.</div>
            </div>
            <div style="text-align:center;" data-lang-id="006-login-button-">
                <input type="submit" style="text-align:center;margin-top:15px;width:30%" id="submit-button" value="Login" class="enabled">
            </div>
        </form>
        </div><!--closes Landing content-->


    <div style="text-align:center;width:100%;margin:auto;margin-top:30px;margin-bottom:50px;">
        <p style="font-size:medium;" data-lang-id="000-no-account-yet">Don't have an account yet? <a href="signup.php">Signup!</a></p>
    </div>

</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE
language files are loaded there

-->


<?php require_once ("../footer-2024.php");?>


<!-- CUSTOM SCRIPTS -->

<script type="text/javascript">
    document.getElementById('login').addEventListener('submit', function(event) {
        // Example validation, modify as needed
        var credentialValue = document.getElementById('credential_value').value;
        var password = document.getElementById('password').value;

        if (credentialValue === '' || password === '') {
            event.preventDefault(); // Prevent form submission
            document.getElementById('password-error').style.display = 'block';
        }
    });
</script>

<!-- CUSTOM MODALS -->

<script type="text/javascript">
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

    // Show the modal and update other page elements
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

// Check if there's an error message and show the error div if needed
document.addEventListener("DOMContentLoaded", function() {
    const errorType = "<?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>";
    if (errorType === "wrong_password") {
        validatePassword(false);
    }
});
</script>
</body>
</html>
