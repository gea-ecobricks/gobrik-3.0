<?php


session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// PART 1: Setup
// Initialize variables
$ecobricker_id = $_GET['id'] ?? null;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.455';
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

// Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Check if ecobricker_id is passed in the URL
if (is_null($ecobricker_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No ecobricker ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 2: Look up user information using ecobricker_id provided in URL

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
    // Clean buffer and log error if statement preparation fails
    ob_clean();
    error_log('Error preparing statement for fetching user info: ' . $gobrik_conn->error);
    echo json_encode(['success' => false, 'error' => 'db_query_failed']);
    ob_end_flush();
    exit();
}

$gobrik_conn->close();

// PART 3: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start(); // Start output buffering

    // Validate passwords
    $password = $_POST['form_password'];
    $confirm_password = $_POST['confirm_password'];
    $terms_accepted = isset($_POST['terms']);
    $newsletter_opt_in = isset($_POST['newsletter']) ? 1 : 0;

    if ($password !== $confirm_password) {
        ob_clean(); // Clear the output buffer before sending JSON response
        echo json_encode(['success' => false, 'error' => 'password_mismatch']);
        ob_end_flush();
        exit();
    }

    if (strlen($password) < 6) {
        ob_clean(); // Clear the output buffer before sending JSON response
        echo json_encode(['success' => false, 'error' => 'password_too_short']);
        ob_end_flush();
        exit();
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Buwana database credentials
    $buwana_servername = "localhost";
    $buwana_username = "ecobricks_gobrik_app";
    $buwana_password = "1EarthenAuth!";
    $buwana_dbname = "ecobricks_earthenAuth_db";

    // Create connection for Buwana database
    $buwana_conn = new mysqli($buwana_servername, $buwana_username, $buwana_password, $buwana_dbname);
    if ($buwana_conn->connect_error) {
        ob_clean(); // Clear the output buffer before sending JSON response
        error_log("Connection failed: " . $buwana_conn->connect_error);
        echo json_encode(['success' => false, 'error' => 'db_connection_failed']);
        ob_end_flush();
        exit();
    }
    $buwana_conn->set_charset("utf8mb4");

    // Check if the email already exists in the Buwana database
    $sql_check_email = "SELECT buwana_id FROM users_tb WHERE email = ?";
    $stmt_check_email = $buwana_conn->prepare($sql_check_email);
    if ($stmt_check_email) {
        $stmt_check_email->bind_param("s", $email_addr);
        $stmt_check_email->execute();
        $stmt_check_email->bind_result($existing_buwana_id);
        $stmt_check_email->fetch();
        $stmt_check_email->close();

     if ($existing_buwana_id) {
    ob_clean(); // Clear the output buffer
    echo json_encode([
        'success' => false,
        'error' => 'duplicate_process',
        'redirect' => 'activate-3.php?id=' . $existing_buwana_id
    ]);
    ob_end_flush();
    exit();

        } else {

            // PART 4
            // Update credentials_tb with the new credential key
            $sql_update_credential = "UPDATE credentials_tb SET credential_key = ? WHERE buwana_id = ?";
            $stmt_update_credential = $buwana_conn->prepare($sql_update_credential);
            if ($stmt_update_credential) {
                $stmt_update_credential->bind_param("si", $email_addr, $ecobricker_id);
                if ($stmt_update_credential->execute()) {
                    // Insert new user into Buwana database
                    $sql_insert_buwana = "INSERT INTO users_tb (first_name, last_name, full_name, email, password_hash, brikcoin_balance, role, account_status, created_at, terms_of_service, notes, validation_credits, earthen_newsletter_join, birth_date)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'Just migrated from GoBrik, step 2 only', NOW(), 1, 'First experimental activations', 3, ?, ?)";
                    $stmt_insert_buwana = $buwana_conn->prepare($sql_insert_buwana);
                    if ($stmt_insert_buwana) {
                        $stmt_insert_buwana->bind_param('ssssissis', $first_name, $last_name, $full_name, $email_addr, $password_hash, $brk_balance, $user_roles, $newsletter_opt_in, $birth_date);
                        if ($stmt_insert_buwana->execute()) {
                            $buwana_id = $stmt_insert_buwana->insert_id;
                        } else {
                            ob_clean(); // Clear the output buffer before sending JSON response
                            error_log('Error executing insert into Buwana users_tb: ' . $stmt_insert_buwana->error);
                            echo json_encode(['success' => false, 'error' => 'db_insert_failed']);
                            ob_end_flush();
                            exit();
                        }
                        $stmt_insert_buwana->close();
                    } else {
                        ob_clean(); // Clear the output buffer before sending JSON response
                        error_log('Error preparing statement for inserting Buwana user: ' . $buwana_conn->error);
                        echo json_encode(['success' => false, 'error' => 'db_insert_failed']);
                        ob_end_flush();
                        exit();
                    }
                } else {
                    ob_clean(); // Clear the output buffer before sending JSON response
                    error_log('Error executing credential update: ' . $stmt_update_credential->error);
                    echo json_encode(['success' => false, 'error' => 'db_update_failed']);
                    ob_end_flush();
                    exit();
                }
                $stmt_update_credential->close();
            } else {
                ob_clean(); // Clear the output buffer before sending JSON response
                error_log('Error preparing credential update: ' . $buwana_conn->error);
                echo json_encode(['success' => false, 'error' => 'db_update_failed']);
                ob_end_flush();
                exit();
            }

            // PART 5: Send welcome email and redirect to next step
            // Send welcome email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'mail.ecobricks.org';
                $mail->SMTPAuth = true;

                $mail->Username = 'gobrik@ecobricks.org';
                $mail->Password = '1Welcome!';
                $mail->SMTPSecure = false;
                $mail->Port = 26;

                $mail->setFrom('no-reply@ecobricks.org', 'GoBrik Welcome');
                $mail->addAddress($email_addr);

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to GoBrik!';
                $mail->Body    = 'Dear ' . $first_name . ',<br><br>Thank you for registering with GoBrik. We are excited to have you on board.<br><br>Best Regards,<br>GEA | Buwana Team';
                $mail->AltBody = 'Dear ' . $first_name . ',\n\nThank you for registering with GoBrik. We are excited to have you on board.\n\nBest Regards,\nGEA | Buwana Team';

                $mail->send();
            } catch (Exception $e) {
                error_log("Message could not be sent. Mailer Error: " . $mail->ErrorInfo);
            }

            // Redirect to the next activation step
            header("Location: activate-3.php?id=$buwana_id");
            ob_end_flush();
            exit();

        } // Close the else block of existing_buwana_id check
    } // Close the if block of $stmt_check_email
} // Close the if block checking $_SERVER['REQUEST_METHOD']
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
            <h2 data-lang-id="001-signup-heading2">Set Your New Password</h2>
            <p><span data-lang-id="002-alright">Alright </span> <?php echo htmlspecialchars($first_name); ?>: <span data-lang-id="002-let-use-you"> to get going with your upgraded account please set a new password.</span></p>
        </div>

        <!--ACTIVATE 2 FORM-->
<form id="password-confirm-form" method="post" action="activate-2.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">
   <div class="form-item" id="set-password">
    <label for="form_password" data-lang-id="007-set-your-pass">Set your password:</label><br>
    <div class="password-wrapper">
        <input type="password" id="form_password" name="form_password" required minlength="6">
        <span class="toggle-password" toggle="#form_password">🔒</span>
    </div>
    <p class="form-caption" data-lang-id="008-password-advice">🔑 Your password must be at least 6 characters.</p>
</div>

<div class="form-item" id="confirm-password-section" style="display:none;">
    <label for="confirm_password" data-lang-id="009-confirm-pass">Confirm Your Password:</label><br>
    <div class="password-wrapper">
        <input type="password" id="confirm_password" name="confirm_password" required>
        <span class="toggle-password" toggle="#confirm_password">🔒</span>
    </div>
    <div id="maker-error-invalid" class="form-field-error" style="margin-top:10px;display:none;" data-lang-id="010-pass-error-no-match">👉 Passwords do not match.</div>
</div>


            <div class="form-item" id="human-check-section">
                <div>
                    <input type="checkbox" id="terms" name="terms" required checked>
                    <label for="terms" style="font-size:medium;" class="form-caption" data-lang-id="013-by-registering">By registering today, I agree to the <a href="#" onclick="showModalInfo('terms')" class="underline-link">GoBrik Terms of Service</a></label>
                </div>
                <div>
                    <input type="checkbox" id="newsletter" name="newsletter" checked>
                    <label for="newsletter" style="font-size:medium;" class="form-caption" data-lang-id="014-i-agree-newsletter">Please send me the <a href="#" onclick="showModalInfo('earthen')" class="underline-link">Earthen newsletter</a> for app, ecobrick, and earthen updates</label>
                </div>
            </div>

            <div id="submit-section" style="text-align:center;margin-top:15px;">
                <input type="submit" id="submit-button" value="🔑 Confirm" class="submit-button disabled">
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

    //EYE ON AND OFF
$(document).ready(function() {
    // Toggle password visibility and switch between the lock emojis
    $('.toggle-password').click(function() {
        let input = $($(this).attr('toggle'));
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).text('🔓'); // Change to unlocked emoji
        } else {
            input.attr('type', 'password');
            $(this).text('🔒'); // Change to locked emoji
        }
    });
});


$(document).ready(function() {
    // Form elements
    const passwordField = document.getElementById('form_password');
    const confirmPasswordSection = document.getElementById('confirm-password-section');
    const confirmPasswordField = document.getElementById('confirm_password');
    const makerErrorInvalid = document.getElementById('maker-error-invalid');
    const submitButton = document.getElementById('submit-button');
    const termsCheckbox = document.getElementById('terms');

    // Show confirm password field when password length is at least 6 characters
    passwordField.addEventListener('input', function() {
        if (passwordField.value.length >= 6) {
            confirmPasswordSection.style.display = 'block';
        } else {
            confirmPasswordSection.style.display = 'none';
            makerErrorInvalid.style.display = 'none';
            updateSubmitButtonState();
        }
    });

    // Enable submit button when passwords match and terms are checked
    confirmPasswordField.addEventListener('input', function() {
        if (passwordField.value === confirmPasswordField.value) {
            makerErrorInvalid.style.display = 'none';
            updateSubmitButtonState();
        } else {
            makerErrorInvalid.style.display = 'block';
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
            submitButton.classList.remove('enabled');
        }
    });

    // Function to update the submit button state
    function updateSubmitButtonState() {
        if (
            passwordField.value.length >= 6 &&
            passwordField.value === confirmPasswordField.value &&
            termsCheckbox.checked
        ) {
            submitButton.disabled = false;
            submitButton.classList.remove('disabled');
            submitButton.classList.add('enabled');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
            submitButton.classList.remove('enabled');
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
                console.log('Server response:', response); // Log the raw response
                var res = JSON.parse(response); // Parse the JSON response
                console.log('Parsed response:', res); // Log the parsed response

                if (res.success) {
                    console.log('Success: Redirecting to the next activation step.');
                    // Redirect to the next activation step with the correct buwana_id
                    window.location.href = 'activate-3.php?id=' + res.buwana_id;
                } else if (res.error === 'duplicate_process' && res.redirect) {
                    console.log('Duplicate process detected: Redirecting to update core information.');
                    // Handle the case where the process has already been done
                    alert("Whoops! Looks like you've already done this process. Continue now by updating your account's core information...");
                    window.location.href = 'login.php'; // Redirect based on the provided URL
                } else {
                    console.log('Error: Unexpected error occurred.');
                    alert('An unexpected error occurred. Please try again.'); // Show error alert
                }
            },
            error: function() {
                console.log('Error: An error occurred while processing the form.');
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
