<?php
// Turn on or off error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session before any output
session_start();

// Check if user is logged in and session active
if (isset($_SESSION['buwana_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.61';
$page = 'login';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$buwana_id = isset($_GET['buwana_id']) ? htmlspecialchars($_GET['buwana_id']) : '';
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';



function getLogoutMessage($lang) {
    $messages = [
        'en' => "You've been logged out.",
        'fr' => "Vous avez été déconnecté.",
        'id' => "Anda telah keluar.",
        'es' => "Has cerrado tu sesión."
    ];
    return $messages[$lang] ?? "You've been logged out."; // Default to English if $lang is not found
}


// Echo the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>




<?php require_once ("../includes/login-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="earth-community top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h3 data-lang-id="001-login-heading">
                <?php
                if ($status === 'loggedout') {
                    echo htmlspecialchars(getLogoutMessage($lang));
                } else {
                    echo "Welcome back!";
                }
                ?>
            </h3>

            <?php if ($buwana_id): ?>
                <p>Logged out from Buwana ID: <?php echo htmlspecialchars($buwana_id); ?></p>
            <?php endif; ?>

            <h4 data-lang-id="002-login-subheading" style="margin-top:5px, margin-bottom:5px;">Login with your account credentials.</h4>
        </div>

        <form id="login" method="post" action="login_process.php" onsubmit="return validateForm();">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <div class="form-item">
        <label for="credential_key" data-lang-id="003-login-email">Your e-mail:</label>
        <div class="input-wrapper" style="position: relative;">
            <input type="text" id="credential_key" name="credential_key" required placeholder="Your e-mail..." value="<?php echo isset($_GET['credential_key']) ? htmlspecialchars($_GET['credential_key']) : ''; ?>">
            <span class="toggle-select" style="cursor: pointer; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">🔑</span>
            <div id="dropdown-menu" style="display: none; position: absolute; right: 10px; top: 100%; z-index: 1000; background: white; border: 1px solid #ccc; width: 150px; text-align: left;">
                <div class="dropdown-item">E-mail</div>
                <div class="dropdown-item disabled" style="opacity: 0.5;">SMS</div>
                <div class="dropdown-item disabled" style="opacity: 0.5;">Trainer</div>
            </div>
        </div>
    </div>

    <div class="form-item">
        <label for="password" data-lang-id="004-login-password">Your password:</label>
        <div class="password-wrapper" style="position: relative;">
            <input type="password" id="password" name="password" required placeholder="Your password...">
            <span toggle="#password" class="toggle-password" style="cursor: pointer; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">🔒</span>
        </div>
        <div id="password-error" class="form-field-error" style="display:none;margin-top: 0px;margin-bottom:-15px;" data-lang-id="000-password-wrong">👉 Password is wrong.</div>
        <p class="form-caption" data-lang-id="000-forgot-your-password">Forgot your password? <a href="#" onclick="showPasswordReset('reset')" class="underline-link">Reset it.</a></p>
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

<script>



        function validateForm() {
            document.getElementById('no-buwana-email').style.display = 'none';
            return true;
        }






// Function to validate password and show/hide the error message
function validatePassword(isValid) {
    const passwordErrorDiv = document.getElementById('password-error');
    if (!isValid) {
        passwordErrorDiv.style.display = 'flex';
    } else {
        passwordErrorDiv.style.display = 'none';
    }
}

function closeModal() {
    const modal = document.getElementById('form-modal-message');
    modal.style.display = 'none';
    document.getElementById('page-content').classList.remove('blurred');
    document.getElementById('footer-full').classList.remove('blurred');
    document.body.classList.remove('modal-open');
}

function validateForm() {
    const email = document.querySelector('input[name="credential_key"]').value;
    if (!email) {
        alert('Please enter a valid email address.');
        return false;
    }
    return true;
}

document.addEventListener("DOMContentLoaded", function() {
    const errorType = "<?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>";
    if (errorType) {
        if (errorType === 'invalid_password') {
            validatePassword(false);
        } else {
            alert(errorType);
        }
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



/*credentials menu*/

document.addEventListener("DOMContentLoaded", function () {
    const toggleSelectIcon = document.querySelector('.toggle-select');
    const dropdownMenu = document.getElementById('dropdown-menu');
    const credentialKeyInput = document.getElementById('credential_key');
    const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');

    // Toggle dropdown menu visibility on click
    toggleSelectIcon.addEventListener('click', function () {
        dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
    });

    // Close dropdown if clicked outside
    document.addEventListener('click', function (e) {
        if (!toggleSelectIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = 'none';
        }
    });

    // Handle dropdown item selection
    dropdownItems.forEach(function (item) {
        item.addEventListener('click', function () {
            if (!item.classList.contains('disabled')) {
                credentialKeyInput.value = item.textContent.trim();
                dropdownMenu.style.display = 'none';
            }
        });
    });
});



</script>


</body>
</html>
