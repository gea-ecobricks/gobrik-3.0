<?php
include 'lang.php';
$version = '0.35';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;
$user_id = $_GET['id'] ?? null;

$servername = "localhost";
$username = "ecobricks_gobrik_app";
$password = "1EarthenAuth!";
$dbname = "ecobricks_earthenAuth_db";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Look up these fields from credentials_tb and users_tb using the user_id
$credential_type = '';
$credential_key = '';
$first_name = '';

if (isset($user_id)) {
    // First, look up the credential_type and credential_key from credentials_tb
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

    // Then, look up the first_name from users_tb
    $sql_lookup_user = "SELECT first_name FROM users_tb WHERE user_id = ?";
    $stmt_lookup_user = $conn->prepare($sql_lookup_user);

    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $user_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement for users_tb: " . $conn->error);
    }

    $credential_type = htmlspecialchars($credential_type); // Sanitize to prevent XSS
    $first_name = htmlspecialchars($first_name); // Sanitize to prevent XSS
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    // Retrieve and sanitize form data
    $credential_value = htmlspecialchars($_POST['credential_value']);
    $password_hash = password_hash($_POST['password_hash'], PASSWORD_DEFAULT);

    // Update the credentials_tb with the credential_key
    $sql_update_credential = "UPDATE credentials_tb SET credential_key = ? WHERE user_id = ?";
    $stmt_update_credential = $conn->prepare($sql_update_credential);

    if ($stmt_update_credential) {
        $stmt_update_credential->bind_param("si", $credential_value, $user_id);

        if ($stmt_update_credential->execute()) {
            // Update the users_tb with the password and change the account status
            $sql_update_user = "UPDATE users_tb SET password_hash = ?, account_status = 'registered no login' WHERE user_id = ?";
            $stmt_update_user = $conn->prepare($sql_update_user);

            if ($stmt_update_user) {
                $stmt_update_user->bind_param("si", $password_hash, $user_id);

                if ($stmt_update_user->execute()) {
                    $success = true;
                    // Redirect to signedup-login.php with user_id
                    header("Location: signedup-login.php?id=$user_id");
                    exit();
                } else {
                    echo "Error: " . $stmt_update_user->error;
                }
                $stmt_update_user->close();
            } else {
                echo "Error preparing statement for users_tb: " . $conn->error;
            }
        } else {
            echo "Error: " . $stmt_update_credential->error;
        }
        $stmt_update_credential->close();
    } else {
        echo "Error preparing statement for credentials_tb: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request method or missing user ID.";
}
?>




<title>Signup 2 | GoBrik 3.0</title>

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

        <div class="earth-com" style="margin-top:-45px;">
        <img src="../webps/earth-community.webp" width="60%">
    </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-signup-heading">Setup Your Access</h2>
            <p data-lang-id="002-gobrik-subtext">Alright <?php echo $first_name; ?>: You've chosen to use <?php echo $credential_type; ?> as your means of registration and the way we contact you.</p>
        </div>

       <!--SIGNUP FORM-->



<form id="password-confirm-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . htmlspecialchars($user_id); ?>">
    <div class="form-item" id="credential-section">
        <label for="credential_value">Your <?php echo $credential_type; ?> please:</label><br>
        <input type="text" id="credential_value" name="credential_value" required>
        <p class="form-caption" data-lang-id="006-volume-ml-caption">💌 This is the way we will contact you to confirm your account</p>
    </div>

    <div class="form-item" id="set-password" style="display: none;">
        <label for="password_hash">Set your password:</label><br>
        <input type="password" id="password_hash" name="password_hash" required minlength="6">
        <p class="form-caption" data-lang-id="006-volume-ml-caption">🔑 Your password must be at least 6 characters.</p>
    </div>

    <div class="form-item" id="confirm-password-section" style="display: none;">
        <label for="confirm_password">Confirm Your Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <div id="maker-error-invalid" class="form-field-error" style="margin-top:10px;" data-lang-id="005b-name-error">👉 Passwords do not match.</div>
    </div>

    <div class="form-item" id="human-check-section" style="display: none;">
        <label for="human_check">Please prove you are human by typing the word "ecobrick" below:</label><br>
        <input type="text" id="human_check" name="human_check" required>
        <p class="form-caption" data-lang-id="006-volume-ml-caption"> 🤓 Fun fact: <a href="#" onclick="showModalInfo('ecobrick')" class="underline-link">'ecobrick'</a> is spelled without a space, capital or hyphen!</p>
        <div>
            <input type="checkbox" id="terms" name="terms" required checked>
            <label for="terms" style="font-size:medium;" class="form-caption">By registering today, I agree to the <a href="#" onclick="showModalInfo('terms')" class="underline-link">GoBrik Terms of Service</a></label>
        </div>
        <div>
            <input type="checkbox" id="newsletter" name="newsletter" checked>
            <label for="newsletter" style="font-size:medium;" class="form-caption">I agree to receive the <a href="#" onclick="showModalInfo('earthen')" class="underline-link">Earthen newsletter</a> for app, ecobrick, and earthen updates</label>
        </div>
    </div>

    <div class="form-item" id="submit-section" style="display:none;text-align:center;margin-top:15px;" title="Be sure you wrote ecobrick correctly!">
        <input type="submit" id="submit-button" value="Register" disabled>
    </div>
</form>

        </div>


<div style="text-align:center;width:100%;margin:auto;margin-top:50px;margin-bottom:50px;"><p style="font-size:medium;">Already have an account? <a href="login.php">Login</a></p>


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
        switch(type) {
            case 'terms':
                content = `

                <div style="font-size:small;">
               <?php  include 'terms.php'; ?>
               </div>
            `;
                break;
            case 'earthen':
                content = `
                <img src="../svgs/earthen-newsletter-logo.svg" alt="Earthen Newsletter" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Earthen Newsletter</div>
                <div class="preview-text">Receive our bi-monthly Earthen newsletter and follow the latest developments in the plastic transition movement.</div>
            `;
                break;

             case 'ecobrick':
                content = `
                <img src="../svgs/earthen-newsletter-logo.svg" alt="Earthen Newsletter" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Earthen Newsletter</div>
                <div class="preview-text">Receive our bi-monthly Earthen newsletter and follow the latest developments in the plastic transition movement.</div>
            `;
                break;

            default:
                content = '<p>Invalid ecobrick type selected.</p>';
        }

        messageContainer.innerHTML = content;

        // Show the modal and update other page elements
        modal.style.display = 'flex';
        modal.style.position = 'relative';
        modal.style.position = 'relative';
        modalBox.style.align = 'left';
        modalBox.style.maxHeight = 'unset';

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
