<?php
include 'lang.php';
$version = '0.35';
$page = 'login';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>


<script>
    function validatePassword(isValid) {
        const passwordErrorDiv = document.getElementById('password-error');
        if (!isValid) {
            passwordErrorDiv.style.display = 'block';
        } else {
            passwordErrorDiv.style.display = 'none';
        }
    }
</script>

<title>Login | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/login-inc.php");?>

<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->

<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">

        <div class="dolphin-pic" style="margin-top:-45px;background-size:contain;">
            <img src="../webps/earth-community.webp" width="80%">
        </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-login-heading-signed-up">Login</h2>
            <p data-lang-id="002-login-subheading">We're glad you're back.</p>
        </div>

        <!-- SIGNUP FORM -->
        <form id="signed-up-login" method="post" action="login_process.php">
            <input type="hidden" name="user_id" value="">
            <div class="form-item">
                <label for="credential_value">Your email or SMS number:</label><br>
                <input type="text" id="credential_value" name="credential_value" value="<?php echo $credential_key; ?>" required>
            </div>
            <div class="form-item">
                <label for="password">Your password:</label><br>
                <input type="password" id="password" name="password" required>
                <p class="form-caption">Forget your password? <a href="#" onclick="showModalInfo('reset')" class="underline-link">Reset it.</a></p>
                <div id="password-error" class="form-field-error" style="display:none;">👉 Password is wrong.</div>
            </div>
            <div class="form-item" id="submit-section" style="text-align:center;margin-top:15px;" title="And login!">
                <input type="submit" id="submit-button" value="Login" class="enabled">
            </div>
        </form>
    </div>

    <div style="text-align:center;width:100%;margin:auto;margin-top:50px;margin-bottom:50px;">
        <p style="font-size:medium;">Don't have an account yet? <a href="signup.php">Signup!</a></p>
    </div>
</div>

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php");?>

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
