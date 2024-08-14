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

// Version and Last Modified (can be externalized in a real application)
$version = '0.59';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Handle CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        .form-field-error { color: red; }
        .toggle-password { cursor: pointer; }
    </style>
</head>
<body>
    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">
            <div style="text-align:center;width:100%;margin:auto;">
                <h3 data-lang-id="001-login-heading">Welcome back!</h3>
                <h4 data-lang-id="002-login-subheading" style="margin-top:5px; margin-bottom:5px;">Login with your account credentials.</h4>
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
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" required placeholder="Your password...">
                        <span class="toggle-password" toggle="#password" style="position:absolute; right:10px; top:50%; transform:translateY(-50%);">🔒</span>
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

    <!-- Modal structure -->
    <div id="form-modal-message" style="display:none;" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
        <div id="modal-photo-box"></div>
        <div class="modal-message"></div>
    </div>

    <!-- Footer -->
    <?php require_once ("../footer-2024.php");?>

    <script>
        // Toggle password visibility and switch between the lock emojis
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.toggle-password').forEach(function(toggleElement) {
                toggleElement.addEventListener('click', function() {
                    let input = document.querySelector(this.getAttribute('toggle'));
                    if (input.getAttribute('type') === 'password') {
                        input.setAttribute('type', 'text');
                        this.textContent = '🔓'; // Change to unlocked emoji
                    } else {
                        input.setAttribute('type', 'password');
                        this.textContent = '🔒'; // Change to locked emoji
                    }
                });
            });
        });

        // Function to validate form
        function validateForm() {
            const email = document.querySelector('input[name="credential_key"]').value;
            if (!email) {
                alert('Please enter a valid email address.');
                return false;
            }
            return true;
        }

        // Modal handling
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
    </script>
</body>
</html>
