<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$response = ['success' => false];
$ecobricker_id = $_GET['user_id'] ?? null;
$directory = basename(dirname($_SERVER['SCRIPT_NAME']));
$lang = $directory;
$version = '0.46';
$page = 'activate';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$first_name = '';
$email_addr = '';

// Include database credentials
include '../buwana_env.php'; // This file provides the database server, user, dbname information to access the server

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

// GoBrik database credentials (we'll hide this soon!)
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
$sql_user_info = "SELECT first_name, email_addr FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_user_info = $gobrik_conn->prepare($sql_user_info);
if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $ecobricker_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($first_name, $email_addr);
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
<title>Sign Up | Step 2 | GoBrik</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/activate-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="credentials-banner top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-signup-heading2">Welcome to GoBrik 3.0</h2>
            <h4>Activate your Buwana Account</h4>
            <p><?php echo htmlspecialchars($first_name); ?>, we've completed the transfer of GoBrik from corporate database servers to our own! In order to use the new GoBrik, your account requires an upgrade.</p>
            <p>The new GoBrik uses Buwana accounts to login: this is our own login alternative to Google Login, Facebook Connect, and Apple accounts. Like the new GoBrik 3.0, it's simple to start. However, soon it will provide a way to login to other great apps in the regenerative movement!</p>
        </div>

        <!--SIGNUP FORM-->
        <form id="activate-confirmation" method="post" action="activate-1.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">

            <div id="submit-section" style="display:none;text-align:center;margin-top:15px;" title="Be sure you wrote ecobrick correctly!">
                <input type="submit" id="submit-button" value="Activate!" class="submit-button enabled">
            </div>
        </form>

        <p>Buwana accounts are designed with ecology, security, and privacy in mind. Check out our easy to read <a href="#" onclick="showModalInfo('terms')" class="underline-link">GoBrik Terms of Service</a>. Get our <a href="#" onclick="showModalInfo('earthen')" class="underline-link">Earthen monthly newsletter</a>. To keep using GoBrik, please activate your Buwana account.</p>

    </div>

    <div style="text-align:center;width:100%;margin:auto;margin-top: 20px;">
        <p style="font-size:medium;" data-land-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
    </div>

</div>

<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>

<script>

/*SHOW MODALS*/

function showModalInfo(type) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');
    let content = '';
    photobox.style.display = 'none';
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
