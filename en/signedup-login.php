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

// Grab language directory from URL
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.587';
$page = 'signedup-login';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Echo the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';


include '../buwanaconn_env.php'; // This file provides the database server, user, dbname information to access the server

$buwana_id = $_GET['id'] ?? null;

// Initialize variables
$credential_type = '';
$credential_key = '';
$first_name = '';

if ($buwana_id) {
    // Prepare the SQL statement for credentials_tb
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE buwana_id = ?";
    if ($stmt_lookup_credential = $buwana_conn->prepare($sql_lookup_credential)) {
        $stmt_lookup_credential->bind_param("i", $buwana_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        echo "Error preparing statement for credentials_tb: " . $buwana_conn->error;
    }

    // Prepare the SQL statement for users_tb
    $sql_lookup_user = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
    if ($stmt_lookup_user = $buwana_conn->prepare($sql_lookup_user)) {
        $stmt_lookup_user->bind_param("i", $buwana_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        echo "Error preparing statement for users_tb: " . $buwana_conn->error;
    }
}

$buwana_conn->close();
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


function closeModal() {
    const modal = document.getElementById('form-modal-message');
    modal.style.display = 'none';
    document.getElementById('page-content').classList.remove('blurred');
    document.getElementById('footer-full').classList.remove('blurred');
    document.body.classList.remove('modal-open');
}

function validateForm() {
    const email = document.querySelector('input[name="email"]').value;
    if (!email) {
        alert('Please enter a valid email address.');
        return false;
    }
    return true;
}

document.addEventListener("DOMContentLoaded", function() {
    const errorType = "<?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>";
    if (errorType) {
        alert(errorType);
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


<title>Login | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->


<?php require_once ("../includes/signedup-login-inc.php"); ?>


<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="dolphin-pic top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="100-login-heading-signed-up">Your account is ready! 🎉</h2>
            <p>Ok <?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="101-login-subheading-signed-up">now please use your <?php echo htmlspecialchars($credential_type); ?> to login for the first time to start setting up your account:</span></p>
        </div>

        <!-- LOGIN FORM -->
        <form id="signed-up-login" method="post" action="signedup_login_process.php">
            <!--<input type="hidden" name="buwana_id" value="<?php echo htmlspecialchars($buwana_id); ?>">
            <div class="form-item">
                <label for="credential_value"><span data-lang-id="000-your">Your</span> <?php echo htmlspecialchars($credential_type); ?> :</label><br>
                <input type="text" id="credential_value" name="credential_value" value="<?php echo htmlspecialchars($credential_key); ?>" required>
            </div>-->

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
                <label for="password" data-lang-id="000-your-password">Your password:</label><br>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <span toggle="#password" class="toggle-password" style="cursor: pointer;">🔒</span>
                </div>

                <p class="form-caption" data-lang-id="000-forgot-your-password">Forgot your password? <a href="#" onclick="showPasswordReset('reset')" class="underline-link">Reset it.</a></p>
                <div id="password-error" class="form-field-error" style="display:none;" data-lang-id="000-password-wrong">👉 Password is wrong.</div>
            </div>

            <div style="text-align:center;">
                <input type="submit" style="text-align:center;margin-top:15px;width:30%" id="submit-button" value="Login" class="enabled">
            </div>
        </form>
    </div>
</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php");?>


<script>


function validateForm() {
    document.getElementById('no-buwana-email').style.display = 'none';
    return true;
}

// Check URL parameters on page load: what's this??
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('email_not_found')) {
        const email = urlParams.get('email') || '';
        const lang = '<?php echo $lang; ?>'; // Ensure $lang is passed from the backend
        showPasswordReset('reset', lang, email);
        setTimeout(() => {
            const noBuwanaEmail = document.getElementById('no-buwana-email');
            if (noBuwanaEmail) {
                noBuwanaEmail.style.display = 'block';
            }
        }, 100);
    }
}

// Form submission validation
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('signed-up-login').addEventListener('submit', function(event) {
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
