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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

    // Retrieve form data
    $credential_value = $_POST['credential_value'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Update the credentials_tb with the credential_value
    $sql_update_credential = "UPDATE credentials_tb SET credentials_name = ?, credential_type = ? WHERE user_id = ?";
    $stmt_update_credential = $conn->prepare($sql_update_credential);

    if ($stmt_update_credential) {
        $stmt_update_credential->bind_param("ssi", $credential_value, $_POST['credential'], $user_id);

        if ($stmt_update_credential->execute()) {
            // Update the user_tb with the password and change the account status
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
            <h2 data-lang-id="001-signup-heading">Create Your Account</h2>
            <p style="font-size:medium;" data-lang-id="002-gobrik-subtext">GoBrik is developed by volunteers just as passionate about plastic transition as you!</p>
        </div>

       <!--SIGNUP FORM-->


    <form id="user-signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . htmlspecialchars($user_id); ?>">

        <div class="form-item" id="credential-section">
            <label for="credential_value">Please provide your registration credential:</label><br>
            <input type="text" id="credential_value" name="credential_value" required>
        </div>

        <div class="form-item">
            <label for="password">Set Your Password:</label><br>
            <input type="password" id="password" name="password" required minlength="6">
        </div>

        <div class="form-item" id="confirm-password-section" style="display: none;">
            <label for="confirm_password">Confirm Your Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="form-item" id="human-check-section" style="display: none;">
            <label for="human_check">Please prove you are human by typing the word "ecobrick" below:</label><br>
            <input type="text" id="human_check" name="human_check" required>
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
</div>

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
            humanCheckSection.style.display = 'block';
        } else {
            humanCheckSection.style.display = 'none';
            submitSection.style.display = 'none';
        }
    });

    humanCheckField.addEventListener('input', function() {
        if (humanCheckField.value.toLowerCase() === 'ecobrick' && termsCheckbox.checked) {
            submitButton.disabled = false;
            submitButton.style.backgroundColor = 'green';
        } else {
            submitButton.disabled = true;
            submitButton.style.backgroundColor = 'grey';
        }
    });

    termsCheckbox.addEventListener('change', function() {
        if (humanCheckField.value.toLowerCase() === 'ecobrick' && termsCheckbox.checked) {
            submitButton.disabled = false;
            submitButton.style.backgroundColor = 'green';
        } else {
            submitButton.disabled = true;
            submitButton.style.backgroundColor = 'grey';
        }
    });
});
</script>

</body>
</html>
