<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$response = ['success' => false];
$ecobricker_id = $_GET['user_id'] ?? null;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.467';
$page = 'activate';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$first_name = '';
$email_addr = '';

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
<div id="top-page-image" class="regen-top top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h2><?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="003-explanation">since you've last logged in, we've made a massive upgrade to GoBrik.</span></h2>

            <p data-lang-id="003-explanation-2">Our old version of GoBrik ran on corporate servers and code.   We've let this pass pass away! In its place, we have migrated all our data to our own independent, self-run server.  Our new GoBrik 3.0 is now 100% open source fully focused on ecological accountability.  We've also developed our own Buwana login system as an alternative to Google and Apple login.  To join us on the regenerated GoBrik with <?php echo htmlspecialchars($email_addr); ?>, please take a minute to upgrade to a Buwana account.</p>
        </div>

        <!--SIGNUP FORM-->
        <form id="activate-confirmation" method="post" action="activate-2.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">
            <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                <div id="submit-section" style="text-align:center;margin-top:20px;padding-right:15px;padding-left:15px" title="Start Activation process">
                    <input type="submit" id="submit-button" value="🍃 Upgrade Account!" class="submit-button activate">
                </div>
            </div>
        </form>


        <p data-lang-id="005-links" style="font-size:1em; text-align: center;">Buwana accounts are designed with ecology, security, and privacy in mind. Soon, you'll be able to login to other great regenerative apps movement in the same way you login to GoBrik!.</p>
        <div style="display:flex;flex-flow:row;justify-content:center;width:100%;margin-top:10px">
            <div><a href="#" onclick="showModalInfo('terms')" class="underline-link" style="margin:auto;padding: 15px;text-align:center;display:block;background: #ffffff47;
  border-radius: 10px;">New Buwana & GoBrik Terms of Service</a></div>

            <div><a href="https://earthen.io/gobrik-regen" class="underline-link" target="_blank" style="margin:auto;padding: 15px;text-align:center;display:block;background: #ffffff47;
  border-radius: 10px;">Why?  Read our 'Great GoBrik Regeneration' blog post.</a></div>
            <div><a href="https://github.com/gea-ecobricks/gobrik-3.0" class="underline-link" target="_blank" style="margin:auto;padding: 15px;text-align:center;display:block;background: #ffffff47;
  border-radius: 10px;">New Github Source Code Repository</a></div>
       </div>

         <div class="form-item" style="margin: 70px 10px 40px 10px;">
            <p style="text-align:center;">If you're not interested and would like your old <?php echo htmlspecialchars($email_addr); ?> account completely deleted, you can do that too.</p>
            <!-- DELETE ACCOUNT FORM -->
            <form id="delete-account-form" method="post" action="delete_account.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">
                <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                    <button type="button" class="submit-button delete" onclick="confirmDeletion()">Delete my account</button>
                </div>

            </form>
            <p data-lang-id="003-warning" style="font-size:medium; text-align: center;">WARNING: This cannot be undone.</p>
            <br>
         </div>
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

<script>
function confirmDeletion() {
    if (confirm("Are you certain you wish to delete your account? This cannot be undone.")) {
        if (confirm("Ok. We will delete your account! Note that this does not affect ecobrick data that has been permanently archived in the brikchain. Note that currently our Earthen newsletter is separate from GoBrik-- which has its own easy unsubscribe mechanism.")) {
            document.getElementById('delete-account-form').submit();
        }
    }
}
</script>


</body>
</html>
