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
$version = '0.652';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Echo the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';

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

require_once '../buwanaconn_env.php'; // Sets up buwana_conn database connection

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
    $stmt_user = $buwana_conn->prepare($sql_user);

    // Bind the data to the user_tb (s = string)
    if ($stmt_user) {
        $stmt_user->bind_param("sssssss", $first_name, $full_name, $created_at, $last_login, $account_status, $role, $notes);

        if ($stmt_user->execute()) {
            $buwana_id = $buwana_conn->insert_id;

            // Prepare the SQL statement for inserting credential data into credentials_tb
            $sql_credential = "INSERT INTO credentials_tb (buwana_id, credential_type, times_used, times_failed, last_login) VALUES (?, ?, 0, 0, ?)";
            $stmt_credential = $buwana_conn->prepare($sql_credential);

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
                error_log("Error preparing credential statement: " . $buwana_conn->error);
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
        error_log("Error preparing user statement: " . $buwana_conn->error);
        echo "An error occurred while creating your account. Please try again.";
    }

    $buwana_conn->close();
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


<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="signup-team top-page-image"></div>

<div id="form-submission-box" class="landing-page-form" style="display:flex;flex-direction: column; justify-content: space-between;">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <div id="status-message" data-lang-id="001-signup-heading">Create Your Account</div>
            <div id="sub-status-message" data-lang-id="002-signup-subtext" style="margin-bottom:15px;">Join us on GoBrik with a Buwana account— an open source, for-Earth alternative to corporate logins.</div>
        </div>

       <!--SIGNUP FORM-->
<form id="user-signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

    <div class="form-item">
        <label for="first_name" data-lang-id="003-firstname">What is your first name?</label><br>
        <span data-lang-id="004-name-placeholder">
            <input type="text" id="first_name" name="first_name" aria-label="Your first name" title="Required. Max 255 characters." required placeholder="Your name...">
        </span>


        <!--ERRORS-->
        <div id="maker-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
        <div id="maker-error-long" class="form-field-error" data-lang-id="000-name-field-too-long-error">The name is too long. Max 255 characters.</div>
        <div id="maker-error-invalid" class="form-field-error" data-lang-id="005b-name-error">The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
    </div>

    <div class="form-item">
        <label for="credential" data-lang-id="006-credential-choice">Your preferred login:</label><br>
        <select id="credential" name="credential" aria-label="Preferred Credential" required placeholder="Select...">
            <option value="" disabled selected data-lang-id="000-select">Select...</option>
            <option value="email">E-mail</option>
            <option value="mail" disabled>Phone</option>
            <option value="sms" disabled>SMS</option>
            <option value="peer" disabled>Peer</option>
        </select>
        <p class="form-caption" data-lang-id="007-way-to-contact">You'll use this credential to login and receive GoBrik messages.</p>
        <!--ERRORS-->
        <div id="credential-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
    </div>


<div style="margin:auto;text-align: center;">
        <button  type="submit" id="submit-button" aria-label="Submit Form" class="submit-button enabled">
        <span data-lang-id="016-submit-to-password" id="submit-button-text">Next ➡️</span>
        </button>
    </div>
</form>

</div>

    <div style="font-size: medium; text-align: center; margin: auto 0 0 0; align-self: center;">
        <p style="font-size: medium;" data-lang-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
    </div>






    </div><!--closes Landing content-->






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




    window.onscroll = function() {
        scrollLessThan30();
        scrollMoreThan30();
        // showHideHeader();
    };

    function scrollLessThan30() {
        if (window.pageYOffset <= 30) {
    var topPageImage = document.querySelector('.top-page-image');
                if (topPageImage) {
                topPageImage.style.zIndex = "35";
            }
        }
    }

    function scrollMoreThan30() {
        if (window.pageYOffset >= 30) {
    var topPageImage = document.querySelector('.top-page-image');
                if (topPageImage) {
                topPageImage.style.zIndex = "25";
            }
        }
    }
</script>


</body>

</html>
