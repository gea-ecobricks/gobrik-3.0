<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$ecobricker_id = $_GET['id'] ?? null;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.452';
$page = 'activate';
$first_name = '';
$last_name = '';
$full_name = '';
$email_addr = '';
$brk_balance = 0;
$user_roles = '';
$birth_date = '';
$password_hash = '';
$terms_of_service = 1;  // Default to 1 as the checkbox is required
$earthen_newsletter_join = 1;  // Default to 1, but will be updated based on form input

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

// PART 2: Check if ecobricker_id is passed in the URL
if (is_null($ecobricker_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No ecobricker ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 3: Look up user information using ecobricker_id provided in URL

// GoBrik database credentials
$gobrik_servername = "localhost";
$gobrik_username = "ecobricks_brikchain_viewer";
$gobrik_password = "desperate-like-the-Dawn";
$gobrik_dbname = "ecobricks_gobrik_msql_db";

// Create connection to GoBrik database
$gobrik_conn = new mysqli($gobrik_servername, $gobrik_username, $gobrik_password, $gobrik_dbname);
if ($gobrik_conn->connect_error) {
    die("Connection failed: " . $gobrik_conn->connect_error);
}
$gobrik_conn->set_charset("utf8mb4");

// Prepare and execute SQL statement to fetch user details
$sql_user_info = "SELECT first_name, last_name, full_name, email_addr, brk_balance, user_roles, birth_date FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_user_info = $gobrik_conn->prepare($sql_user_info);
if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $ecobricker_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($first_name, $last_name, $full_name, $email_addr, $brk_balance, $user_roles, $birth_date);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
} else {
    die('Error preparing statement for fetching user info: ' . $gobrik_conn->error);
}

$gobrik_conn->close();

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Activate your Buwana Account | Step 2 | GoBrik</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/signup-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="credentials-banner top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-signup-heading2">Set your new password</h2>
            <p><span data-lang-id="002-alright">Alright </span> <?php echo htmlspecialchars($first_name); ?>: <span data-lang-id="002-let-use-you"> to get going with your new Buwana GoBrik account please set a new password.</span></p>
        </div>

        <!--SIGNUP FORM-->
        <form id="password-confirm-form" method="post" action="activate-2.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">

            <div class="form-item" id="set-password">
                <label for="password_hash" data-lang-id="007-set-your-pass">Set your password:</label><br>
                <input type="password" id="password_hash" name="password_hash" required minlength="6">
                <p class="form-caption" data-lang-id="008-password-advice">🔑 Your password must be at least 6 characters.</p>
            </div>

            <div class="form-item" id="confirm-password-section">
                <label for="confirm_password" data-lang-id="009-confirm-pass">Confirm Your Password:</label><br>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <div id="maker-error-invalid" class="form-field-error" style="margin-top:10px;display:none;" data-lang-id="010-pass-error-no-match">👉 Passwords do not match.</div>
            </div>

            <div class="form-item" id="human-check-section">
                <div>
                    <input type="checkbox" id="terms" name="terms" required checked>
                    <label for="terms" style="font-size:medium;" class="form-caption" data-lang-id="013-by-registering">By registering today, I agree to the <a href="#" onclick="showModalInfo('terms')" class="underline-link">GoBrik Terms of Service</a></label>
                </div>
                <div>
                    <input type="checkbox" id="newsletter" name="newsletter" checked>
                    <label for="newsletter" style="font-size:medium;" class="form-caption" data-lang-id="014-i-agree-newsletter">I agree to receive the <a href="#" onclick="showModalInfo('earthen')" class="underline-link">Earthen newsletter</a> for app, ecobrick, and earthen updates</label>
                </div>
            </div>

            <div id="submit-section" style="text-align:center;margin-top:15px;">
                <input type="submit" id="submit-button" value="Register" class="submit-button disabled">
            </div>
        </form>

    </div>

    <div style="text-align:center;width:100%;margin:auto;margin-top: 20px;">
        <p style="font-size:medium;" data-land-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
    </div>

</div>
</div>
    <!--FOOTER STARTS HERE-->
    <?php require_once ("../footer-2024.php"); ?>


<script>
$(document).ready(function() {
    // Initialize form elements
    const passwordField = document.getElementById('password_hash');
    const confirmPasswordField = document.getElementById('confirm_password');
    const makerErrorInvalid = document.getElementById('maker-error-invalid');
    const submitButton = document.getElementById('submit-button');
    const termsCheckbox = document.getElementById('terms');

    // Show confirm password field when password length is at least 6 characters
    passwordField.addEventListener('input', function() {
        // Check if password length is at least 6 characters
        if (passwordField.value.length >= 6) {
            confirmPasswordField.style.display = 'block'; // Show confirm password field
        } else {
            confirmPasswordField.style.display = 'none'; // Hide confirm password field
            makerErrorInvalid.style.display = 'none'; // Hide mismatch error message
            updateSubmitButtonState(); // Update submit button state
        }
    });

    // Enable submit button when passwords match and terms are checked
    confirmPasswordField.addEventListener('input', function() {
        // Check if passwords match
        if (passwordField.value === confirmPasswordField.value) {
            makerErrorInvalid.style.display = 'none'; // Hide mismatch error message
            updateSubmitButtonState(); // Update submit button state
        } else {
            makerErrorInvalid.style.display = 'block'; // Show mismatch error message
            submitButton.disabled = true; // Disable submit button
            submitButton.classList.add('disabled'); // Add disabled class
            submitButton.classList.remove('enabled'); // Remove enabled class
        }
    });

    // Function to update the submit button state based on form validity
    function updateSubmitButtonState() {
        // Check if passwords match, are at least 6 characters, and terms are checked
        if (
            passwordField.value.length >= 6 &&
            passwordField.value === confirmPasswordField.value &&
            termsCheckbox.checked
        ) {
            submitButton.disabled = false; // Enable submit button
            submitButton.classList.remove('disabled'); // Remove disabled class
            submitButton.classList.add('enabled'); // Add enabled class
        } else {
            submitButton.disabled = true; // Disable submit button
            submitButton.classList.add('disabled'); // Add disabled class
            submitButton.classList.remove('enabled'); // Remove enabled class
        }
    }

    // Update button state when terms checkbox is clicked
    termsCheckbox.addEventListener('change', updateSubmitButtonState);

    // Handle form submission
    $('#password-confirm-form').on('submit', function(e) {
        e.preventDefault(); // Prevent the form from submitting normally

        // Send form data via AJAX to the server
        $.ajax({
            url: $(this).attr('action'), // Use form's action attribute as URL
            type: 'POST', // Send data via POST method
            data: $(this).serialize(), // Serialize the form data
            success: function(response) {
                var res = JSON.parse(response); // Parse the JSON response
                if (res.success) {
                    // Redirect to the next activation step if successful
                    window.location.href = 'activate-3.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>';
                } else {
                    alert('An unexpected error occurred. Please try again.'); // Show error alert
                }
            },
            error: function() {
                alert('An error occurred while processing the form. Please try again.'); // Show error alert
            }
        });
    });
});

// Function to show modal information
function showModalInfo(type) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');
    let content = '';
    photobox.style.display = 'none';

    // Set modal content based on the type of information requested
    switch (type) {
        case 'terms':
            content = `
                <div style="font-size: small;">
                    <?php include "../files/terms-$lang.php"; ?>
                </div>
            `;
            modal.style.position = 'absolute';
            modal.style.overflow = 'auto';
            modalBox.style.textAlign = 'left';
            modalBox.style.maxHeight = 'unset';
            modalBox.style.marginTop = '30px';
            modalBox.style.marginBottom = '30px';
            modalBox.scrollTop = 0;
            modal.style.alignItems = 'flex-start';

            break;
        case 'earthen':
            content = `
                <img src="../svgs/earthen-newsletter-logo.svg" alt="Earthen Newsletter" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Earthen Newsletter</div>
                <div class="preview-text">We use our Earthen email newsletter to keep our users informed of the latest developments in the plastic transition movement and the world of ecobricking. Free with your GoBrik account or unclick to opt-out. We use ghost.org's open source newsletter platform that makes it easy to unsubscribe anytime.</div>
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

    // Insert the content into the modal
    messageContainer.innerHTML = content;

    // Show the modal and blur the background
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

</script>


</body>
</html>
