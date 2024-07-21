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

<script type="text/javascript">
    function showSuccessMessage() {
        alert("Yeay! First step done.");
    }
</script>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection details
    $servername = "localhost";
    $username = "ecobricks_gobrik_app";
    $password = "1EarthenAuth!";
    $dbname = "ecobricks_earthenAuth_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve form data
    $first_name = $_POST['first_name'];
    $credential = $_POST['credential'];
    $terms_of_service = isset($_POST['terms_of_service']) ? 1 : 0;
    $earthen_newsletter_join = isset($_POST['earthen_newsletter_join']) ? 1 : 0;

    // Set other required fields
    $full_name = $first_name;
    $created_at = date("Y-m-d H:i:s");
    $last_login = date("Y-m-d H:i:s");
    $account_status = 'registering';
    $role = 'ecobricker';
    $notes = "beta testing the first signup form";

    // Use prepared statements for inserting user data
    $sql_user = "INSERT INTO users_tb (first_name, full_name, created_at, last_login, account_status, role, notes, terms_of_service, earthen_newsletter_join) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_user = $conn->prepare($sql_user);

    if ($stmt_user) {
        $stmt_user->bind_param("sssssssii", $first_name, $full_name, $created_at, $last_login, $account_status, $role, $notes, $terms_of_service, $earthen_newsletter_join);

        if ($stmt_user->execute()) {
            $user_id = $conn->insert_id;

            // Use prepared statements for inserting credential data
            $sql_credential = "INSERT INTO credentials_tb (user_id, credentials_name, credential_type, times_used, times_failed, last_login) VALUES (?, ?, ?, 0, 0, ?)";
            $stmt_credential = $conn->prepare($sql_credential);

            if ($stmt_credential) {
                $stmt_credential->bind_param("isss", $user_id, $credential, $credential, $last_login);

                if ($stmt_credential->execute()) {
                    $success = true;
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

<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">
         <!--<div class="form-top-header" style="display:flex;flex-flow:row;">
          <div class="step-graphic">
                <img src="../svgs/step1-log-project.svg" style="height:25px;" loading="eager">
            </div>
            <div id="language-code" onclick="showLangSelector()" aria-label="Switch languages"><span data-lang-id="000-language-code">🌐 EN</span></div>
        </div>-->

        <div class="signup-team" style="text-align:center;width:90%; background: url(../svgs/signup-team.svg?v=2) no-repeat center;
            background-size: auto;
          background-size: contain;"><img src="../webps/ecobrick-team-blank.webp" width="90%"></div>

        <div style="text-align:center;width:90%;">
            <h2>Create Your Account</h2>
            <p>GoBrik is developed by volunteers just as passionate about plastic transition as you!</p>

            <p style="font-size:small;">Already have an account? <a href="login.php">Login</a></p>
        </div>



        <!--LOG FORM-->
        <form id="user-signup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

            <div class="form-item" style="margin-top: 25px;">
                <label for="first_name" data-lang-id="005-first-name">What is your first name?</label><br>
                    <input type="text" id="first_name" name="first_name" aria-label="Your first name" title="Required. Max 255 characters." required type="name">
                    <p class="form-caption" data-lang-id="005b-ecobricker-maker-caption">By what name do we address you?</p>

                    <!--ERRORS-->
                    <div id="maker-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                    <div id="maker-error-long" class="form-field-error" data-lang-id="000-maker-field-too-long-error">The name is too long. Max 255 characters.</div>
                    <div id="maker-error-invalid" class="form-field-error" data-lang-id="005b-maker-error">The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
                </div>

            <div class="form-item">
                    <label for="credential" data-lang-id="006-credential">With which credentials would you like to register?</label><br>
                    <select id="credential" name="credential" aria-label="Preferred Credential" required type="credential">
                        <option value="" disabled selected>Select credential...</option>
                        <option value="sms">By SMS</option>
                        <option value="email">By Email</option>
                        <option value="mail">By Mail</option>
                    </select>
                    <p class="form-caption" data-lang-id="006-volume-ml-caption">This is the way we will contact you to confirm your account</p>
                    <!--ERRORS-->
                    <div id="volume-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>

            <div class="form-item">
                <label for="terms_of_service">
                    <input type="checkbox" id="terms_of_service" name="terms_of_service" required>
                    Do you agree to our terms of service?
                </label><br><br>
            </div>

            <div class="form-item">
                <label for="earthen_newsletter_join">
                    <input type="checkbox" id="earthen_newsletter_join" name="earthen_newsletter_join" checked>
                    Receive our Earthen newsletter
                </label><br><br>
            </div>

          <div data-lang-id="016-submit-button" style="max-width:300px;">
                    <input type="submit" value="Next" aria-label="Submit Form">
                </div>
        </form>
    </div><!--closes Landing content-->
</div>

</div><!--closes main and starry background-->

<!--FOOTER STARTS HERE-->

<?php require_once ("../footer-2024.php");?>

</div><!--close page content-->

</body>

</html>
