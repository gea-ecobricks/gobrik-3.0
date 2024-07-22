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

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;
$user_id = $_GET['id'] ?? null;

include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server


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
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    // Retrieve and sanitize form data
    $entered_credential = htmlspecialchars($_POST['credential_value']);
    $entered_password = $_POST['password'];

    // Check if entered credential matches the credential_key in the database
    if ($entered_credential === $credential_key) {
        // Retrieve the hashed password from users_tb
        $sql_get_password = "SELECT password_hash FROM users_tb WHERE user_id = ?";
        $stmt_get_password = $conn->prepare($sql_get_password);

        if ($stmt_get_password) {
            $stmt_get_password->bind_param("i", $user_id);
            $stmt_get_password->execute();
            $stmt_get_password->bind_result($hashed_password);
            $stmt_get_password->fetch();
            $stmt_get_password->close();

            // Verify the entered password
            if (password_verify($entered_password, $hashed_password)) {
                // Successful login, update the user's last_login in users_tb
                $sql_update_user = "UPDATE users_tb SET last_login = NOW() WHERE user_id = ?";
                $stmt_update_user = $conn->prepare($sql_update_user);

                if ($stmt_update_user) {
                    $stmt_update_user->bind_param("i", $user_id);
                    $stmt_update_user->execute();
                    $stmt_update_user->close();
                } else {
                    die("Error preparing statement for updating users_tb: " . $conn->error);
                }

                // Update times_used and last_login in credentials_tb
                $sql_update_credentials = "UPDATE credentials_tb SET times_used = times_used + 1, last_login = NOW() WHERE user_id = ?";
                $stmt_update_credentials = $conn->prepare($sql_update_credentials);

                if ($stmt_update_credentials) {
                    $stmt_update_credentials->bind_param("i", $user_id);
                    $stmt_update_credentials->execute();
                    $stmt_update_credentials->close();
                } else {
                    die("Error preparing statement for updating credentials_tb: " . $conn->error);
                }

                // Redirect to the dashboard or any other page
                header("Location: onboard-1.php?id=$user_id");
                exit();
            } else {
                echo "<script>alert('Invalid password.');</script>";
            }
        } else {
            die("Error preparing statement for getting password: " . $conn->error);
        }
    } else {
        echo "<script>alert('Invalid credential.');</script>";
    }
}

$conn->close();
?>





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
            <h2 data-lang-id="001-login-heading-signed-up">Your account is Ready! 🎉</h2>
            <p data-lang-id="002-login-subheading">Ok <?php echo $first_name; ?>, now please use your <?php echo $credential_type; ?> to login for the first time to start setting up your account:</p>
        </div>

       <!--SIGNUP FORM-->


 <form id="signed-up-login" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . htmlspecialchars($user_id); ?>">
        <div class="form-item">
            <label for="credential_value">Your <?php echo $credential_type; ?>:</label><br>
            <input type="text" id="credential_value" name="credential_value" value="<?php echo htmlspecialchars($credential_key); ?>" required>
        </div>

        <div class="form-item">
            <label for="password">Your password:</label><br>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-item">
            <input type="submit" value="Login">
        </div>
    </form>

        </div>


<div style="text-align:center;width:100%;margin:auto;margin-top:50px;margin-bottom:50px;"><p style="font-size:medium;">Don't have an account yet? <a href="signup.php">Signup!</a></p>


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
    switch (type) {
        case 'terms':
            content = `
                <div style="font-size: small;">
                    <?php include 'terms.php'; ?>
                </div>
            `;
            modal.style.position = 'absolute';
            modal.style.overflow = 'auto';
            modalBox.style.textAlign = 'left';
            modalBox.style.maxHeight = 'unset';
            modalBox.style.marginTop = '30px';
            modalBox.style.marginBottom = '30px';

    // Set scroll position to the top of the modal content
    modalBox.scrollTop = 0;

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
                <img src="../webps/faqs-400px.webp" alt="Ecobrick Term and Types" height="200px" width="200px" class="preview-image">
                <div class="preview-title">The Term</div>
                <div class="preview-text">In 2016 plastic transition leaders around the world, agreed to use the non-hyphenated, non-capitalize term ‘ecobrick’ as the consistent, standardized term of reference in the guidebook and their materials. In this way, ecobrickers around the world would be able to refer with one word to same concept and web searches and hashtags would accelerate global dissemination.</div>
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
