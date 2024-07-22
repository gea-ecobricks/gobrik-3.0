<?php
include 'lang.php';
$version = '0.346';
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

// Look up these fields from users_tb using the user_id
$credential_type = '';
$first_name = '';

if (isset($user_id)) {
    $sql_lookup_user = "SELECT credential_type, first_name FROM users_tb WHERE id = ?";
    $stmt_lookup_user = $conn->prepare($sql_lookup_user);

    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $user_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($credential_type, $first_name);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

    $credential_type = htmlspecialchars($credential_type); // Sanitize to prevent XSS
    $first_name = htmlspecialchars($first_name); // Sanitize to prevent XSS
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    // Retrieve and sanitize form data
    $credential_value = htmlspecialchars($_POST['credential_value']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Update the credentials_tb with the credential_value
    $sql_update_credential = "UPDATE credentials_tb SET credentials_key = ? WHERE user_id = ?";
    $stmt_update_credential = $conn->prepare($sql_update_credential);

    if ($stmt_update_credential) {
        $stmt_update_credential->bind_param("ssi", $credential_value, $_POST['credential'], $user_id);

        if ($stmt_update_credential->execute()) {
            // Update the users_tb with the password and change the account status
            $sql_update_user = "UPDATE users_tb SET password = ?, account_status = 'registered no login' WHERE id = ?";
            $stmt_update_user = $conn->prepare($sql_update_user);

            if ($stmt_update_user) {
                $stmt_update_user->bind_param("si", $password, $user_id);

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

        <div class="signup-team">
        <img src="../svgs/signup-team.svg?v=2" width="60%">
    </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-signup-heading">Set your password</h2>
            <p style="font-size:medium;" data-lang-id="002-gobrik-subtext">Alright $first_name: You've chosen to use $credential as your means of registration and the way we contact you.</p>
        </div>

       <!--SIGNUP FORM-->



<form id="user-signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . htmlspecialchars($user_id); ?>">

    <div class="form-item" id="credential-section">
        <label for="credential_value">Please provide your <?php echo $credential; ?>:</label><br>
        <input type="text" id="credential_value" name="credential_value" required>
        <p class="form-caption" data-lang-id="006-volume-ml-caption">This is the way we will contact you to confirm your account</p>
    </div>

    <div class="form-item">
        <label for="password">Set your password:</label><br>
        <input type="password" id="password" name="password" required minlength="6">
    </div>

    <div class="form-item" id="confirm-password-section" style="display: none;">
        <label for="confirm_password">Confirm Your Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <div id="maker-error-invalid" class="form-field-error" data-lang-id="005b-name-error">Passwords do not match.</div>
    </div>

    <div class="form-item" id="human-check-section" style="display: none;">
        <label for="human_check">Please prove you are human by typing the word "ecobrick" below:</label><br>
        <input type="text" id="human_check" name="human_check" required>
        <p class="form-caption" data-lang-id="006-volume-ml-caption">Fun fact: 'ecobrick' is spelled without capitals or hyphens!</p>
        <div>
            <input type="checkbox" id="terms" name="terms" required checked>
            <label for="terms">By registering today, I agree to the GoBrik terms of service</label>
        </div>
        <div>
            <input type="checkbox" id="newsletter" name="newsletter" checked>
            <label for="newsletter">I agree to receive the Earthen newsletter for app, ecobrick, and earthen updates</label>
        </div>
    </div>

    <div class="form-item" id="submit-section" style="display: none;">
        <input type="submit" id="submit-button" value="Register" disabled>
    </div>
</form>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const humanCheckField = document.getElementById('human_check');
    const termsCheckbox = document.getElementById('terms');
    const submitButton = document.getElementById('submit-button');
    const confirmPasswordSection = document.getElementById('confirm-password-section');
    const humanCheckSection = document.getElementById('human-check-section');
    const submitSection = document.getElementById('submit-section');
    const makerErrorInvalid = document.getElementById('maker-error-invalid');

    passwordField.addEventListener('input', function() {
        if (passwordField.value.length >= 6) {
            confirmPasswordSection.style.display = 'block';
        } else {
            confirmPasswordSection.style.display = 'none';
            humanCheckSection.style.display = 'none';
            submitSection.style.display = 'none';
        }
    });

    confirmPasswordField.addEventListener('input', function() {
        if (passwordField.value === confirmPasswordField.value) {
            makerErrorInvalid.style.display = 'none';
            humanCheckSection.style.display = 'block';
        } else {
            makerErrorInvalid.style.display = 'block';
            humanCheckSection.style.display = 'none';
            submitSection.style.display = 'none';
        }
    });

    function updateSubmitButtonState() {
        if (humanCheckField.value.toLowerCase() === 'ecobrick' && termsCheckbox.checked) {
            submitButton.disabled = false;
            submitButton.style.backgroundColor = 'green';
        } else {
            submitButton.disabled = true;
            submitButton.style.backgroundColor = 'grey';
        }
    }

    humanCheckField.addEventListener('input', updateSubmitButtonState);
    termsCheckbox.addEventListener('change', updateSubmitButtonState);
});
</script>

</body>
</html>
