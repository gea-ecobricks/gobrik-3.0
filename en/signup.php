<?php
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.45';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>

<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    echo "<script>
        alert('Looks like you already have an account and are logged in! Let\'s take you to your dashboard.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include '../buwana_env.php'; // This file provides the database server, user, dbname information

    // Retrieve form data and sanitize inputs
    $first_name = trim($_POST['first_name']);
    $credential = trim($_POST['credential']);

    // Set other required fields
    $full_name = $first_name;
    $created_at = date("Y-m-d H:i:s");
    $last_login = date("Y-m-d H:i:s");
    $account_status = 'name set only';
    $role = 'ecobricker';
    $notes = "beta testing the first signup form";

    // Prepare the SQL statement for inserting user data into the Buwana user_tb
    $sql_user = "INSERT INTO users_tb (first_name, full_name, created_at, last_login, account_status, role, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_user = $conn->prepare($sql_user);

    // Bind the data to the user_tb (s = string)
    if ($stmt_user) {
        $stmt_user->bind_param("sssssss", $first_name, $full_name, $created_at, $last_login, $account_status, $role, $notes);

        if ($stmt_user->execute()) {
            $buwana_id = $conn->insert_id;

            // Prepare the SQL statement for inserting credential data into credentials_tb
            $sql_credential = "INSERT INTO credentials_tb (buwana_id, credential_type, times_used, times_failed, last_login) VALUES (?, ?, 0, 0, ?)";
            $stmt_credential = $conn->prepare($sql_credential);

            if ($stmt_credential) {
                $stmt_credential->bind_param("iss", $buwana_id, $credential, $last_login);

                if ($stmt_credential->execute()) {
                    $success = true;
                    // Redirect to signup-2.php with the buwana_id in the URL
                    header("Location: signup-2.php?id=$buwana_id");
                    exit();
                } else {
                    // Log error
                    error_log("Error executing credential statement: " . $stmt_credential->error);
                    echo "An error occurred while creating your account. Please try again.";
                }
                $stmt_credential->close();
            } else {
                // Log error
                error_log("Error preparing credential statement: " . $conn->error);
                echo "An error occurred while creating your account. Please try again.";
            }
        } else {
            // Log error
            error_log("Error executing user statement: " . $stmt_user->error);
            echo "An error occurred while creating your account. Please try again.";
        }
        $stmt_user->close();
    } else {
        // Log error
        error_log("Error preparing user statement: " . $conn->error);
        echo "An error occurred while creating your account. Please try again.";
    }

    $conn->close();
}
?>


<title>Signup | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/signup-inc.php");?>

<?php if ($success): ?>
    <script type="text/javascript">
        showSuccessMessage();
    </script>
<?php endif; ?>

<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->
   <div id="top-page-image" class="signup-team" style="margin-top:60px;margin-bottom: 50px;z-index:35;position: absolute;
  text-align: center;
  width: 100%;
  height: 150px;">
      <!-- <img src="../webps/earth-community.webp" style="width:65%;">-->
    </div>

<div id="form-submission-box" style="height:100vh;padding-top:65px;">
    <div class="form-container">

   <!-- <div class="signup-team">
        <img src="../webps/ecobrick-team-blank.webp" width="60%">
    </div> -->

        <div style="text-align:center;width:100%;margin:auto;">
            <h3 data-lang-id="001-signup-heading">Create Your Account</h3>
            <h4 data-lang-id="002-gobrik-subtext2">GoBrik uses Buwana accounts-- an open source, Earth-first alternative to corporate logins.  Buwana will soon work for other regenerative apps.</h4>
        </div>

       <!--SIGNUP FORM-->
<form id="user-signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

    <div class="form-item" style="margin-top:0px;">
        <label for="first_name" data-lang-id="003-first-name">What is your first name?</label><br>
        <span data-lang-id="000-name-placeholder">
            <input type="text" id="first_name" name="first_name" aria-label="Your first name" title="Required. Max 255 characters." required placeholder="Your name...">
        </spn>


        <!--ERRORS-->
        <div id="maker-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
        <div id="maker-error-long" class="form-field-error" data-lang-id="000-name-field-too-long-error">The name is too long. Max 255 characters.</div>
        <div id="maker-error-invalid" class="form-field-error" data-lang-id="005b-name-error">The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
    </div>

    <div class="form-item">
        <label for="credential" data-lang-id="006-credential">With which credentials would you like to register?</label><br>
        <select id="credential" name="credential" aria-label="Preferred Credential" required placeholder="Select...">
            <option value="" disabled selected data-lang-id="000-select">Select...</option>

            <option value="email">E-mail</option>
            <option value="mail" disabled>Mail</option>
            <option value="sms" disabled>SMS</option>
        </select>
        <p class="form-caption" data-lang-id="006-way-to-contact">How we'll contact you to confirm your account.</p>
        <!--ERRORS-->
        <div id="credential-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
    </div>
    <!-- <div style="margin:auto;text-align: center;">
        <input type="submit" id="submit-button" value="🔑 Next: Set Password" aria-label="Submit Form" class="enabled">
    </div> -->


<div style="margin:auto;text-align: center;">
        <button  type="submit" id="submit-button" aria-label="Submit Form" class="submit-button enabled">
        🔑 <span data-lang-id="016-submit-to-password" id="submit-button-text">Next: Set Password</span>
        </button>
    </div>
</form>



    </div><!--closes Landing content-->


    <div style="text-align:center;width:100%;margin:auto;margin-top:34px;"><p style="font-size:medium;" data-land-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
        </div>


</div>

</div><!--closes main and starry background-->

<!--FOOTER STARTS HERE-->

<?php require_once ("../footer-2024.php");?>

</div><!--close page content-->

<script>

document.getElementById('user-signup-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting until validation is complete
    var isValid = true; // Flag to determine if the form should be submitted

    // Helper function to display error messages
    function displayError(elementId, showError) {
        var errorDiv = document.getElementById(elementId);
        if (showError) {
            errorDiv.style.display = 'block'; // Show the error message
            isValid = false; // Set form validity flag
        } else {
            errorDiv.style.display = 'none'; // Hide the error message
        }
    }

    // Helper function to check for invalid characters
    function hasInvalidChars(value) {
        const invalidChars = /[\'\"><]/; // Regex for invalid characters
        return invalidChars.test(value);
    }

    // 1. First Name Validation
    var firstName = document.getElementById('first_name').value.trim();
    displayError('maker-error-required', firstName === '');
    displayError('maker-error-long', firstName.length > 255);
    displayError('maker-error-invalid', hasInvalidChars(firstName));

    // 2. Credential Validation
    var credential = document.getElementById('credential').value;
    displayError('credential-error-required', credential === '');

    // If all validations pass, submit the form
    if (isValid) {
        this.submit();
    } else {
        // Scroll to the first error message and center it in the viewport
        var firstError = document.querySelector('.form-field-error[style="display: block;"]');
        if (firstError) {
            firstError.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
            // Optionally, find the related input and focus it
            var relatedInput = firstError.closest('.form-item').querySelector('input, select, textarea');
            if (relatedInput) {
                relatedInput.focus();
            }
        }
    }
});

</script>


</body>

</html>
