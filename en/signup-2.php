<?php
include 'lang.php';
$version = '0.346';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

include '../ecobricks_env.php'; // this file provides the database server, user, dbname information to access the server

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $credential = $_POST['credential'];
    $credential_value = $_POST['credential_value'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $account_status = 'registered no login';

    // Update credentials_tb
    $sql_update_credential = "UPDATE credentials_tb SET credential_value = ? WHERE user_id = ? AND credential_type = ?";
    $stmt_update_credential = $conn->prepare($sql_update_credential);

    if ($stmt_update_credential) {
        $stmt_update_credential->bind_param("sis", $credential_value, $user_id, $credential);

        if ($stmt_update_credential->execute()) {
            // Update user_tb
            $sql_update_user = "UPDATE users_tb SET password = ?, account_status = ? WHERE id = ?";
            $stmt_update_user = $conn->prepare($sql_update_user);

            if ($stmt_update_user) {
                $stmt_update_user->bind_param("ssi", $password, $account_status, $user_id);

                if ($stmt_update_user->execute()) {
                    echo "Registration complete!";
                    exit();
                } else {
                    echo "Error updating users_tb: " . $stmt_update_user->error;
                }
                $stmt_update_user->close();
            } else {
                echo "Error preparing statement for users_tb: " . $conn->error;
            }
        } else {
            echo "Error updating credentials_tb: " . $stmt_update_credential->error;
        }
        $stmt_update_credential->close();
    } else {
        echo "Error preparing statement for credentials_tb: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>Signup Step 2 | GoBrik 3.0</title>
    <style>
        .form-container {
            width: 80%;
            background-color: var(--form-background);
            border: 1px solid var(--divider-line);
            border-radius: 15px;
            margin: 0 auto;
            max-width: 1000px;
            z-index: 20;
            font-family: "Mulish", sans-serif;
            position: relative;
            padding-top: 100px;
        }
        .signup-team {
            text-align: center;
            width: 100%;
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 22;
        }
        .signup-team img {
            width: 60%;
        }
        .hidden {
            display: none;
        }
        .form-field-error {
            color: red;
            display: none;
        }
        .disabled {
            background-color: grey;
            pointer-events: none;
        }
        .enabled {
            background-color: green;
            pointer-events: auto;
        }
    </style>
<title>Signup 2 | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/signup-inc.php");?>

<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->

<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">
        <div class="signup-team">
            <img src="../svgs/signup-team.svg?v=2" width="60%">
        </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2>Complete Your Registration</h2>
        </div>

        <!-- SIGNUP FORM -->
        <form id="user-signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <input type="hidden" name="user_id" value="<?php echo $_GET['id']; ?>">
            <input type="hidden" name="credential" value="<?php echo $_GET['credential']; ?>">

            <div id="credential-field" class="form-item">
                <label for="credential_value">Please provide your <?php echo htmlspecialchars($_GET['credential']); ?></label><br>
                <input type="text" id="credential_value" name="credential_value" required>
                <div id="credential-error-required" class="form-field-error">This field is required.</div>
            </div>

            <div class="form-item">
                <label for="password">Set Your Password</label><br>
                <input type="password" id="password" name="password" required minlength="6">
                <div id="password-error-required" class="form-field-error">Password is required and must be at least 6 characters long.</div>
            </div>

            <div id="confirm-password-field" class="form-item hidden">
                <label for="confirm_password">Confirm Your Password</label><br>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <div id="confirm-password-error-match" class="form-field-error">Passwords do not match.</div>
            </div>

            <div id="human-check-field" class="form-item hidden">
                <label for="human_check">Please prove you are human by typing the word "ecobrick" below</label><br>
                <input type="text" id="human_check" name="human_check" required>
                <div id="human-check-error" class="form-field-error">Please type "ecobrick" to proceed.</div>
            </div>

            <div id="terms-field" class="form-item hidden">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">By registering today, I agree to the GoBrik terms of service</label><br>
                <input type="checkbox" id="newsletter" name="newsletter" checked>
                <label for="newsletter">I agree to receive the Earthen newsletter for app, ecobrick, and earthen updates</label><br>
                <div id="terms-error" class="form-field-error">You must agree to the terms of service.</div>
            </div>

            <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;">
                <input type="submit" id="submit-btn" value="🔑 Complete Registration" aria-label="Submit Form" class="disabled" disabled>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('user-signup-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const humanCheck = document.getElementById('human_check');
    const terms = document.getElementById('terms');
    const submitBtn = document.getElementById('submit-btn');

    // Show confirm password field when password is valid
    password.addEventListener('input', function () {
        if (password.value.length >= 6) {
            document.getElementById('confirm-password-field').classList.remove('hidden');
        } else {
            document.getElementById('confirm-password-field').classList.add('hidden');
        }
    });

    // Show human check and terms fields when passwords match
    confirmPassword.addEventListener('input', function () {
        if (password.value === confirmPassword.value) {
            document.getElementById('confirm-password-error-match').style.display = 'none';
            document.getElementById('human-check-field').classList.remove('hidden');
            document.getElementById('terms-field').classList.remove('hidden');
        } else {
            document.getElementById('confirm-password-error-match').style.display = 'block';
            document.getElementById('human-check-field').classList.add('hidden');
            document.getElementById('terms-field').classList.add('hidden');
        }
    });

    // Enable submit button when human check and terms are valid
    function checkFormValidity() {
        if (humanCheck.value === 'ecobrick' && terms.checked) {
            submitBtn.classList.remove('disabled');
            submitBtn.classList.add('enabled');
            submitBtn.disabled = false;
        } else {
            submitBtn.classList.add('disabled');
            submitBtn.classList.remove('enabled');
            submitBtn.disabled = true;
        }
    }

    humanCheck.addEventListener('input', checkFormValidity);
    terms.addEventListener('change', checkFormValidity);

    // Form submission event listener
    form.addEventListener('submit', function (event) {
        if (!submitBtn.disabled) {
            alert("Registration complete!");
        } else {
            event.preventDefault();
        }
    });
});
</script>

</body>
</html>
