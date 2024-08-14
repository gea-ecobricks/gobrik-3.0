<?php
// Turn off error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

// Start session
session_start();

// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

// Check if user is logged in
if (isset($_SESSION['buwana_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Sanitize and validate language directory from URL
$lang = htmlspecialchars(basename(dirname($_SERVER['SCRIPT_NAME'])), ENT_QUOTES, 'UTF-8');

// Version and last modified information
$version = '0.588';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Handle CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once("../includes/login-inc.php"); // Includes header, styles, and opens body tag
?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="earth-community top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h3 data-lang-id="001-login-heading">Welcome back!</h3>
            <h4 data-lang-id="002-login-subheading" style="margin-top:5px, margin-bottom:5px;">Login with your account credentials.</h4>
        </div>

        <!-- Login form -->
        <form id="login" method="post" action="login_process.php" onsubmit="return validateForm();">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-item">
                <label for="credential_key" data-lang-id="003-login-email">Your e-mail:</label>
                <input type="text" id="credential_key" name="credential_key" required placeholder="Your e-mail...">
            </div>

            <div class="form-item">
                <label for="password" data-lang-id="004-login-password">Your password:</label>
                <input type="password" id="password" name="password" required placeholder="Your password...">
                <span toggle="#password" class="toggle-password" style="cursor: pointer;">🔒</span>
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

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php");?>

<script>
// Function to validate the form before submission
function validateForm() {
    const email = document.querySelector('input[name="credential_key"]').value;
    const password = document.getElementById('password').value;
    if (!email || !password) {
        document.getElementById('password-error').style.display = 'block';
        return false;
    }
    return true;
}

// Function to close modal
function closeModal() {
    const modal = document.getElementById('form-modal-message');
    modal.style.display = 'none';
    document.getElementById('page-content').classList.remove('blurred');
    document.getElementById('footer-full').classList.remove('blurred');
    document.body.classList.remove('modal-open');
}

// Show modal information based on type
function showModalInfo(type, email = '') {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    let content = '';
    photobox.style.display = 'none';
    switch (type) {
        case 'reset':
            content = `
                <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                    <h1>🔓</h1>
                </div>
                <div class="preview-title">Reset Password</div>
                <form id="resetPasswordForm" action="reset_password.php" method="POST" onsubmit="return validateForm()">
                    <div class="preview-text" style="font-size:medium;">Enter your email to reset your password:</div>
                    <input type="email" name="email" required value="${email}">
                    <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                        <div id="no-buwana-email" class="form-warning" style="margin-top:5px;margin-bottom:5px;" data-lang-id="010-no-buwana-email">🤔 Hmmm... we can't find an account that uses this email!</div>
                        <button type="submit" class="submit-button enabled">Reset Password</button>
                    </div>
                </form>
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

// Check URL parameters on page load
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('email_not_found')) {
        const email = urlParams.get('email') || '';
        showModalInfo('reset', email);
        setTimeout(() => {
            const noBuwanaEmail = document.getElementById('no-buwana-email');
            if (noBuwanaEmail) {
                noBuwanaEmail.style.display = 'block';
            }
        }, 100);
    }
}

// Toggle password visibility and switch between the lock emojis
$(document).ready(function() {
    $('.toggle-password').click(function() {
        let input = $($(this).attr('toggle'));
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).text('🔓'); // Change to unlocked emoji
        } else {
            input.attr('type', 'password');
            $(this).text('🔒'); // Change to locked emoji
        }
    });
});

</script>

</body>
</html>
