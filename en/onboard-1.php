<?php
include 'lang.php';
$version = '0.35';
$page = 'login';
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

include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

// Look up these fields from credentials_tb and users_tb using the user_id
$credential_type = '';
$credential_key = '';
$first_name = '';

if (isset($user_id)) {
    // First, look up the credential_type and credential_key from credentials_tb
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE user_id = ?";
    $stmt_lookup_credential = $conn->prepare($sql_lookup_credential);

    if ($stmt_lookup_credential) {
        $stmt_lookup_credential->bind_param("i", $user_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        die("Error preparing statement for credentials_tb: " . $conn->error);
    }

    // Then, look up the first_name from users_tb
    $sql_lookup_user = "SELECT first_name FROM users_tb WHERE user_id = ?";
    $stmt_lookup_user = $conn->prepare($sql_lookup_user);

    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $user_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement for users_tb: " . $conn->error);
    }
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($user_id)) {
    // Retrieve and sanitize form data
    $entered_credential = htmlspecialchars($_POST['credential_value']);
    $entered_password = $_POST['password'];

    // Check if entered credential matches the credential_key in the database
    if ($entered_credential === $credential_key) {
        // Retrieve the hashed password from users_tb
        $sql_get_password = "SELECT password_hash FROM users_tb WHERE user_id = ?";
        $stmt_get_password = $conn->prepare($sql_get_password);

        if ($stmt_get_password) {
            $stmt_get_password->bind_param("i", $user_id);
            $stmt_get_password->execute();
            $stmt_get_password->bind_result($hashed_password);
            $stmt_get_password->fetch();
            $stmt_get_password->close();

            // Verify the entered password
            if (password_verify($entered_password, $hashed_password)) {
                // Successful login, update the user's last_login in users_tb
                $sql_update_user = "UPDATE users_tb SET last_login = NOW() WHERE user_id = ?";
                $stmt_update_user = $conn->prepare($sql_update_user);

                if ($stmt_update_user) {
                    $stmt_update_user->bind_param("i", $user_id);
                    $stmt_update_user->execute();
                    $stmt_update_user->close();
                } else {
                    die("Error preparing statement for updating users_tb: " . $conn->error);
                }

                // Update times_used and last_login in credentials_tb
                $sql_update_credentials = "UPDATE credentials_tb SET times_used = times_used + 1, last_login = NOW() WHERE user_id = ?";
                $stmt_update_credentials = $conn->prepare($sql_update_credentials);

                if ($stmt_update_credentials) {
                    $stmt_update_credentials->bind_param("i", $user_id);
                    $stmt_update_credentials->execute();
                    $stmt_update_credentials->close();
                } else {
                    die("Error preparing statement for updating credentials_tb: " . $conn->error);
                }

                // Redirect to the dashboard or any other page
                header("Location: onboard-1.php?id=$user_id");
                exit();
            } else {
                $error_message = "password";
            }
        } else {
            die("Error preparing statement for getting password: " . $conn->error);
        }
    } else {
        $error_message = "credential";
    }
}

$conn->close();
?>




<title>Login | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/login-inc.php");?>


<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->

<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">

        <div class="dolphin-pic" style="margin-top:-45px;background-size:contain;">
        <img src="../webps/earth-community.webp" width="80%">
    </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-login-heading-signed-up">Your account is Ready! 🎉</h2>
            <p data-lang-id="002-login-subheading">Ok <?php echo $first_name; ?>, now please use your <?php echo $credential_type; ?> to login for the first time to start setting up your account:</p>
        </div>

       <!--SIGNUP FORM-->


   <form id="signed-up-login" method="post" action="">
        <div class="form-item">
            <label for="credential_value">Ok <?php echo $first_name; ?>, now please use your <?php echo $credential_type; ?> to login for the first time:</label><br>
            <input type="text" id="credential_value" name="credential_value" value="<?php echo $credential_key; ?>" required>
        </div>
        <div class="form-item">
            <label for="password">Your password:</label><br>
            <input type="password" id="password" name="password" required>
            <p class="form-caption">Forget your password? <a href="#" onclick="showModalInfo('reset')" class="underline-link">Reset it.</a></p>
            <div id="password-error" class="form-field-error">👉 Password is wrong.</div>
        </div>
        <div class="form-item" id="submit-section" style="text-align:center;margin-top:15px;" title="And login!">
        <input type="submit" id="submit-button" value="Login" class="enabled">
    </div>
        <input type="hidden" id="error-type" value="<?php echo $error_message; ?>">
    </form>

        </div>




<div style="text-align:center;width:100%;margin:auto;margin-top:50px;margin-bottom:50px;"><p style="font-size:medium;">Don't have an account yet? <a href="signup.php">Signup!</a></p>


    </div><!--closes Landing content-->
</div>

</div><!--closes main and starry background-->

<!--FOOTER STARTS HERE-->

<?php require_once ("../footer-2024.php");?>

</div><!--close page content-->







    <script type="text/javascript">


function showModalInfo(type) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');
    let content = '';
    photobox.style.display = 'none';
    switch (type) {

        case 'reset':
            content = `
                <img src="../pngs/exchange-bird.png" alt="Reset Password" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Reset Password</div>
                <div class="preview-text">Oops!  This function is not yet operational.  Create another account for the moment as all accounts will be deleted once we migrate from beta to live.</div>
            `;
            break;

        default:
            content = '<p>Invalid term selected.</p>';
    }

    messageContainer.innerHTML = content;


    // Show the modal and update other page elements
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}


</script>





</body>
</html>
