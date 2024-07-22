<?php
include 'lang.php';
$version = '0.346';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
<title>Signup | GoBrik 3.0</title>
<!-- Add your other meta tags and stylesheets here -->
</head>
<body>';
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

    // Retrieve and sanitize form data
    $first_name = htmlspecialchars($_POST['first_name']);
    $credential = htmlspecialchars($_POST['credential']);

    // Set other required fields
    $full_name = $first_name;
    $created_at = date("Y-m-d H:i:s");
    $last_login = date("Y-m-d H:i:s");
    $account_status = 'registering';
    $role = 'ecobricker';
    $notes = "beta testing the first signup form";

    // Use prepared statements for inserting user data
    $sql_user = "INSERT INTO users_tb (first_name, full_name, created_at, last_login, account_status, role, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_user = $conn->prepare($sql_user);

    if ($stmt_user) {
        $stmt_user->bind_param("sssssss", $first_name, $full_name, $created_at, $last_login, $account_status, $role, $notes);

        if ($stmt_user->execute()) {
            $user_id = $conn->insert_id;

            // Use prepared statements for inserting credential data
            $sql_credential = "INSERT INTO credentials_tb (user_id, credential_type, times_used, times_failed, last_login) VALUES (?, ?, 0, 0, ?)";
            $stmt_credential = $conn->prepare($sql_credential);

            if ($stmt_credential) {
                $stmt_credential->bind_param("iss", $user_id, $credential, $last_login);

                if ($stmt_credential->execute()) {
                    $success = true;
                    // Redirect to signup-2.php with user_id
                    header("Location: signup-2.php?id=$user_id");
                    exit();
                } else {
                    echo "Error: " . $stmt_credential->error;
                }
                $stmt_credential->close();
            } else {
                echo "Error preparing statement for credentials_tb: " . $conn->error;
            }
        } else {
            echo "Error: " . $stmt_user->error;
        }
        $stmt_user->close();
    } else {
        echo "Error preparing statement for users_tb: " . $conn->error;
    }

    $conn->close();
}
?>

<!-- Page Content -->
<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">
        <div class="signup-team">
            <img src="../svgs/signup-team.svg?v=2" width="60%">
        </div>
        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-signup-heading">Create Your Account</h2>
            <p data-lang-id="002-gobrik-subtext">GoBrik is developed by volunteers just as passionate about plastic transition as you!</p>
        </div>

        <!-- Signup Form -->
        <form id="user-signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form-item" style="margin-top:0px;">
                <label for="first_name" data-lang-id="003-first-name">What is your first name?</label><br>
                <input type="text" id="first_name" name="first_name" aria-label="Your first name" title="Required. Max 255 characters." required>
                <!-- Errors -->
                <div id="maker-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                <div id="maker-error-long" class="form-field-error" data-lang-id="000-name-field-too-long-error">The name is too long. Max 255 characters.</div>
                <div id="maker-error-invalid" class="form-field-error" data-lang-id="005b-name-error">The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
            </div>
            <div class="form-item">
                <label for="credential" data-lang-id="006-credential">With which credentials would you like to register?</label><br>
                <select id="credential" name="credential" aria-label="Preferred Credential" required>
                    <option value="" disabled selected>Select credential...</option>
                    <option value="email">E-mail</option>
                    <option value="mail" disabled>Mail</option>
                    <option value="sms" disabled>SMS</option>
                </select>
                <p class="form-caption" data-lang-id="006-volume-ml-caption">This is the way we will contact you to confirm your account</p>
                <!-- Errors -->
                <div id="credential-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
            </div>
            <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;">
                <input type="submit" id="submit-button" value="🔑 Next: Set Password" aria-label="Submit Form" class="enabled">
            </div>
        </form>
    </div>

    <div style="text-align:center;width:100%;margin:auto;">
        <p style="font-size:medium;">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<?php require_once ("../footer-2024.php");?>

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
