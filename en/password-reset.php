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
$version = '0.585';
$page = 'reset';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Database credentials
$buwana_servername = "localhost";
$buwana_username = "ecobricks_gobrik_app";
$buwana_password = "1EarthenAuth!";
$buwana_dbname = "ecobricks_earthenAuth_db";

// Establish connection to the database
$buwana_conn = new mysqli($buwana_servername, $buwana_username, $buwana_password, $buwana_dbname);

// Check connection
if ($buwana_conn->connect_error) {
    die("Connection failed: " . $buwana_conn->connect_error);
}

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($token) {
    // Check if token is valid
    $stmt = $buwana_conn->prepare("SELECT email FROM users_tb WHERE password_reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    if (!$email) {
        echo '<script>alert("Invalid token. Please try again."); window.location.href = "login.php";</script>';
        exit();
    }
} else {
    echo '<script>alert("No token provided. Please try again."); window.location.href = "login.php";</script>';
    exit();
}

// Echo the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
<title>Password Reset</title>
';

require_once ("../includes/reset-inc.php");

echo '
<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="credentials-banner top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h3>Let\'s Reset Your Password</h3>
            <h4 style="margin-top:12px; margin-bottom:8px;">Enter your new password for your Buwana account.</h4>
        </div>

        <!-- Reset password form -->
        <form id="resetForm" method="post" action="process_reset.php">
            <input type="hidden" name="token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">
            <div class="form-item">
                <p>New password:</p>
                <input type="password" id="password" name="password" required placeholder="Your new password...">
                <p class="form-caption" data-lang-id="011-six-characters">Password must be at least 6 characters long.</p>
                <div id="password-error" class="form-field-error" style="display:none;margin-top:5px;">👉 Not long enough!</div>
            </div>

            <div class="form-item">
                <p>Re-enter password to confirm:</p>
                <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Re-enter password...">
                <div id="confirm-password-error" class="form-field-error" style="display:none;margin-top:5px;">👉 Passwords do not match.</div>
            </div>

            <div style="text-align:center;">
                <input type="submit" style="text-align:center;margin-top:15px;width:30%; min-width: 175px;" id="submit-button" value="🔑 Reset Password" class="submit-button enabled">
            </div>
        </form>
    </div>
    <div style="text-align:center;width:100%;margin:auto;margin-top:34px;"><p style="font-size:medium;">Already have an account? <a href="login.php">Login</a></p></div>
</div>
</div>';

require_once ("../footer-2024.php");

echo '

<script>
document.getElementById("resetForm").addEventListener("submit", function(event) {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirmPassword").value;
    var isValid = true;

    if (password.length < 6) {
        document.getElementById("password-error").style.display = "block";
        isValid = false;
    } else {
        document.getElementById("password-error").style.display = "none";
    }

    if (password !== confirmPassword) {
        document.getElementById("confirm-password-error").style.display = "block";
        isValid = false;
    } else {
        document.getElementById("confirm-password-error").style.display = "none";
    }

    if (!isValid) {
        event.preventDefault();
    }
});

window.onscroll = function() {
    scrollLessThan30();
    scrollMoreThan30();
};

function scrollLessThan30() {
    if (window.pageYOffset <= 30) {
        var topPageImage = document.querySelector(".top-page-image");
        if (topPageImage) {
            topPageImage.style.zIndex = "35";
        }
    }
}

function scrollMoreThan30() {
    if (window.pageYOffset >= 30) {
        var topPageImage = document.querySelector(".top-page-image");
        if (topPageImage) {
            topPageImage.style.zIndex = "25";
        }
    }
}
</script>

</body>
</html>';
?>
