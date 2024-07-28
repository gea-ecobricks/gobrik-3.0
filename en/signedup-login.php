<?php
$directory = basename(dirname($_SERVER['SCRIPT_NAME']));
$lang = $directory;
$version = '0.365';
$page = 'signedup-login';
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

include '../buwana_env.php'; // This file provides the database server, user, dbname information to access the server

$buwana_id = $_GET['id'] ?? null;

// Initialize variables
$credential_type = '';
$credential_key = '';
$first_name = '';

if ($buwana_id) {
    // Prepare the SQL statement for credentials_tb
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE buwana_id = ?";
    if ($stmt_lookup_credential = $conn->prepare($sql_lookup_credential)) {
        $stmt_lookup_credential->bind_param("i", $buwana_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        echo "Error preparing statement for credentials_tb: " . $conn->error;
    }

    // Prepare the SQL statement for users_tb
    $sql_lookup_user = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
    if ($stmt_lookup_user = $conn->prepare($sql_lookup_user)) {
        $stmt_lookup_user->bind_param("i", $buwana_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        echo "Error preparing statement for users_tb: " . $conn->error;
    }
}

$conn->close();
?>
<script>
    function validatePassword(isValid) {
        const passwordErrorDiv = document.getElementById('password-error');
        if (!isValid) {
            passwordErrorDiv.style.display = 'block';
        } else {
            passwordErrorDiv.style.display = 'none';
        }
    }
</script>

<title>Login | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/signedup-login-inc.php");?>

<?php require_once ("../includes/signedup-login-inc.php"); ?>

<div class="splash-content-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="form-submission-box" style="height:100vh;">
    <div class="form-container">
        <div class="dolphin-pic" style="margin-top:-65px;background-size:contain;" alt="Yeay!">
            <img src="../webps/earth-community.webp" width="65%">
        </div>

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="100-login-heading-signed-up">Your account is ready! 🎉</h2>
            <p>Ok <?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="101-login-subheading-signed-up">now please use your <?php echo htmlspecialchars($credential_type); ?> to login for the first time to start setting up your account:</span></p>
        </div>

        <!-- LOGIN FORM -->
        <form id="signed-up-login" method="post" action="signedup_login_process.php">
            <input type="hidden" name="buwana_id" value="<?php echo htmlspecialchars($buwana_id); ?>">
            <div class="form-item">
                <label for="credential_value"><span data-lang-id="000-your">Your</span> <?php echo htmlspecialchars($credential_type); ?> :</label><br>
                <input type="text" id="credential_value" name="credential_value" value="<?php echo htmlspecialchars($credential_key); ?>" required>
            </div>
            <div class="form-item">
                <label for="password" data-lang-id="000-your-password">Your password:</label><br>
                <input type="password" id="password" name="password" required>
                <p class="form-caption" data-lang-id="000-forgot-your-password">Forgot your password? <a href="#" onclick="showModalInfo('reset')" class="underline-link">Reset it.</a></p>
                <div id="password-error" class="form-field-error" style="display:none;" data-lang-id="000-password-wrong">👉 Password is wrong.</div>
            </div>

            <div style="text-align:center;">
                <input type="submit" style="text-align:center;margin-top:15px;width:30%" id="submit-button" value="Login" class="enabled">
            </div>
        </form>
    </div>
</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php");?>

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
                <div class="preview-text">Oops! This function is not yet operational. Create another account for the moment as all accounts will be deleted once we migrate from beta to live.</div>
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

// Check if there's an error message and show the error div if needed
document.addEventListener("DOMContentLoaded", function() {
    const errorType = "<?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : ''; ?>";
    if (errorType === "wrong_password") {
        validatePassword(false);
    }
});
</script>
</body>
</html>
