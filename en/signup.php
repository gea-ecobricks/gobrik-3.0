<?php

include 'lang.php';
$version = '0.343';
$page = 'signup';
include '../buwana_env.php';
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

ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SERVER["REQUEST_METHOD"] == "POST") {


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

    // Insert user into users_tb
    $sql_user = "INSERT INTO users_tb (first_name, full_name, created_at, last_login, account_status, role, terms_of_service, earthen_newsletter_join, notes)
                 VALUES ('$first_name', '$full_name', '$created_at', '$last_login', '$account_status', '$role', '$terms_of_service', '$earthen_newsletter_join', '$notes')";

    if ($conn->query($sql_user) === TRUE) {
        $user_id = $conn->insert_id;

        // Insert credential into credentials_tb
        $sql_credential = "INSERT INTO credentials_tb (user_id, credentials_name, credential_type, times_used, times_failed, last_login)
                           VALUES ('$user_id', '$credential', '$credential', 0, 0, '$last_login')";

        if ($conn->query($sql_credential) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql_credential . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $sql_user . "<br>" . $conn->error;
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

    <div id="form-submission-box">
        <div class="form-container">
            <div class="form-top-header" style="display:flex;flex-flow:row;">
                <div class="step-graphic">
                    <img src="../svgs/step1-log-project.svg" style="height:25px;" loading="eager">
                </div>
                <div id="language-code" onclick="showLangSelector()" aria-label="Switch languages"><span data-lang-id="000-language-code">🌐 EN</span></div>
            </div>

            <div class="splash-form-content-block">
                <div class="splash-box">
                    <div class="splash-heading" data-lang-id="001-splash-title">Register</div>
                </div>
                <div class="splash-image" data-lang-id="003-splash-image-alt">
                    <img src="../webps/eb-sky-400px.webp" style="width:65%; text-align:center;" alt="There are many ways to make an ecobrick">
                </div>
            </div>

            <div class="lead-page-paragraph">
                <p data-lang-id="004-form-description">Take a moment to create your account..</p>
            </div>


            <!--LOG FORM-->
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="first_name">What is your name?</label><br>
        <input type="text" id="first_name" name="first_name" required><br><br>

        <label for="credential">What credential would you like to use to register with?</label><br>
        <select id="credential" name="credential" required>
            <option value="sms">SMS</option>
            <option value="email">Email</option>
            <option value="mail">Mail</option>
        </select><br><br>

        <label for="terms_of_service">
            <input type="checkbox" id="terms_of_service" name="terms_of_service" required>
            Do you agree to our terms of service?
        </label><br><br>

        <label for="earthen_newsletter_join">
            <input type="checkbox" id="earthen_newsletter_join" name="earthen_newsletter_join" checked>
            Receive our Earthen newsletter
        </label><br><br>

        <input type="submit" value="Sign Up">
    </form>

        </div><!--closes Landing content-->
    </div>

</div><!--closes main and starry background-->

	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2024.php");?>

</div><!--close page content-->

</body>

</html>

