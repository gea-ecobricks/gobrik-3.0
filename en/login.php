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
$buwana_id = isset($_GET['buwana_id']) ? filter_var($_GET['buwana_id'], FILTER_SANITIZE_NUMBER_INT) : '';
$status = isset($_GET['status']) ? filter_var($_GET['status'], FILTER_SANITIZE_STRING) : '';



// PHP functions to get the correct messages based on the language
function getLogoutMessage($lang) {
    $messages = [
        'en' => "You've been logged out.",
        'fr' => "Vous avez été déconnecté.",
        'id' => "Anda telah keluar.",
        'es' => "Has cerrado tu sesión."
    ];
    return $messages[$lang] ?? $messages['en']; // Default to English if $lang is not found
}

function getFirstTimeMessage($lang) {
    $messages = [
        'en' => "Your Buwana Account is Created! 🎉",
        'fr' => "Votre compte Buwana est créé ! 🎉",
        'id' => "Akun Buwana Anda sudah Dibuat! 🎉",
        'es' => "¡Tu cuenta de Buwana está creada! 🎉"
    ];
    return $messages[$lang] ?? $messages['en']; // Default to English if $lang is not found
}

function getLoginMessage($lang) {
    $messages = [
        'en' => "Welcome back!",
        'fr' => "Bon retour !",
        'id' => "Selamat datang kembali!",
        'es' => "¡Bienvenido de nuevo!"
    ];
    return $messages[$lang] ?? $messages['en']; // Default to English if $lang is not found
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
    // Display the correct message based on the status
    if ($status === 'loggedout') {
        echo htmlspecialchars(getLogoutMessage($lang));
    } elseif ($status === 'firsttime') {
        echo htmlspecialchars(getFirstTimeMessage($lang));
    } else {
        echo htmlspecialchars(getLoginMessage($lang));
    }
    ?>
</h3>


            <h4 data-lang-id="002-login-subheading" style="margin-top:5px, margin-bottom:5px;">Login with your account credentials.</h4>
        </div>

        <form id="login" method="post" action="login_process.php" onsubmit="return validateForm();">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <div class="form-item">
<!--        <label for="credential_key" data-lang-id="003-login-email">Your e-mail:</label>
-->
        <div class="input-wrapper" style="position: relative;">
            <input type="text" id="credential_key" name="credential_key" required placeholder="Your e-mail..." value="<?php echo isset($_GET['credential_key']) ? htmlspecialchars($_GET['credential_key']) : ''; ?>">
            <span class="toggle-select" style="cursor: pointer; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">🔑</span>
            <div id="dropdown-menu" style="display: none; position: absolute; right: 10px; top: 100%; z-index: 1000; background: white; border: 1px solid #ccc; width: 150px; text-align: left;">
                <div class="dropdown-item" value="Your email...">E-mail</div>
                <div class="dropdown-item disabled" style="opacity: 0.5;">SMS</div>
                <div class="dropdown-item disabled" style="opacity: 0.5;">Peer</div>
            </div>
                <div id="no-buwana-mail" class="form-field-error" style="display:none;margin-top: 0px;margin-bottom:-15px;" data-lang-id="000-no-buwana-account">🤔 We can't find this credential in the database.</div>

        </div>
    </div>

    <div class="form-item">
        <!--<label for="password" data-lang-id="004-login-password">Your password:</label>-->
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



/* PASSWORD RESET MODAL  */
function showPasswordReset(type, lang = 'en', email = '') {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    let content = '';
    photobox.style.display = 'none';

    switch (type) {
        case 'reset':
            let title, promptText, buttonText, errorText;

            switch (lang) {
                case 'fr':
                    title = "Réinitialiser le mot de passe";
                    promptText = "Entrez votre email pour réinitialiser votre mot de passe :";
                    buttonText = "Réinitialiser le mot de passe";
                    errorText = "🤔 Hmmm... nous ne trouvons aucun compte utilisant cet email !";
                    break;
                case 'es':
                    title = "Restablecer la contraseña";
                    promptText = "Ingrese su correo electrónico para restablecer su contraseña:";
                    buttonText = "Restablecer la contraseña";
                    errorText = "🤔 Hmmm... no podemos encontrar una cuenta que use este correo electrónico!";
                    break;
                case 'id':
                    title = "Atur Ulang Kata Sandi";
                    promptText = "Masukkan email Anda untuk mengatur ulang kata sandi Anda:";
                    buttonText = "Atur Ulang Kata Sandi";
                    errorText = "🤔 Hmmm... kami tidak dapat menemukan akun yang menggunakan email ini!";
                    break;
                default: // 'en'
                    title = "Reset Password";
                    promptText = "Enter your email to reset your password:";
                    buttonText = "Reset Password";
                    errorText = "🤔 Hmmm... we can't find an account that uses this email!";
                    break;
            }

            content = `
                <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                    <h1>🔓</h1>
                </div>
                <div class="preview-title">${title}</div>
                <form id="resetPasswordForm" action="reset_password.php" method="POST">
                    <div class="preview-text" style="font-size:medium;">${promptText}</div>
                    <input type="email" name="email" required value="${email}">
                    <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                        <div id="no-buwana-email" class="form-warning" style="display:none;margin-top:5px;margin-bottom:5px;" data-lang-id="010-no-buwana-email">${errorText}</div>
                        <button type="submit" class="submit-button enabled">${buttonText}</button>
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

window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);

    // Check if the 'email_not_found' parameter exists in the URL
    if (urlParams.has('email_not_found')) {
        // Get the email from the URL parameters
        const email = urlParams.get('email') || '';

        // Get the language from the backend (PHP) or default to 'en'
        const lang = '<?php echo $lang; ?>'; // Make sure this is echoed from your PHP

        // Show the reset modal with the pre-filled email and appropriate language
        showPasswordReset('reset', lang, email);

        // Wait for the modal to load, then display the "email not found" error message
        setTimeout(() => {
            const noBuwanaEmail = document.getElementById('no-buwana-email');
            if (noBuwanaEmail) {
                console.log("Displaying the 'email not found' error.");
                noBuwanaEmail.style.display = 'block';
            }
        }, 100);
    }
};

</script>


</body>
</html>
