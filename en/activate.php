<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['buwana_id'])) {
    header("Location: login.php");
    exit();
}

// Get the ecobricker_id from the URL
$ecobricker_id = $_GET['id'] ?? null;

// Check if ecobricker_id is valid
if (is_null($ecobricker_id)) {
    echo '<script>
        alert("No ecobricker ID was provided. Please try again.");
        window.location.href = "activate.php";
    </script>';
    exit();
}

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

// Prepare and execute SQL statement to delete the user
$sql_delete_user = "DELETE FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_delete_user = $gobrik_conn->prepare($sql_delete_user);
if ($stmt_delete_user) {
    $stmt_delete_user->bind_param('i', $ecobricker_id);
    $stmt_delete_user->execute();
    $stmt_delete_user->close();

    // Destroy session and redirect to confirmation page
    session_destroy();
    echo '<script>
        alert("Your account has been permanently deleted.");
        window.location.href = "goodbye.php";
    </script>';
    exit();
} else {
    die('Error preparing statement for deleting user: ' . $gobrik_conn->error);
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
<div id="top-page-image" class="credentials-banner top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
            <h2><?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="003-explanation">since you've last logged in, we've made a massive upgrade to GoBrik.</span></h2>

            <p data-lang-id="003-explanation-2">We've ditched our old corporate server and migrated all our data to our own.  Our new GoBrik 3.0 is running on fully <a href="https://github.com/gea-ecobricks/gobrik-3.0" targ="_blank">revamped open source code base</a> and our own database!  We've also developed our own Buwana login system as an alternative to Google and Apple login.</p>

            <p data-lang-id="003-explanation-3" style="font-weight:500">To keep using GoBrik with <?php echo htmlspecialchars($email_addr); ?>, please take a minute to upgrade to a Buwana account. If you're not interested and would like your old <?php echo htmlspecialchars($email_addr); ?> account completely deleted, you can do that too.</p>
        </div>

       <!--SIGNUP FORM-->
<form id="activate-confirmation" method="post" action="activate-2.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">
    <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
        <div id="submit-section" style="text-align:center;margin-top:15px;" title="Start Activation process">
            <input type="submit" id="submit-button" value="Activate!" class="submit-button activate">
        </div>
    </div>
</form>

<!-- DELETE ACCOUNT FORM -->
<form id="delete-account-form" method="post" action="delete_account.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">
    <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
        <button type="button" class="submit-button delete" onclick="confirmDeletion()">Delete my account</button>
    </div>
</form>


        <p data-lang-id="005-links" style="font-size:small; text-align: center;">Buwana accounts are designed with ecology, security, and privacy in mind. Soon, you'll be able to login to other great regenerative apps movement in the same way you login to GoBrik! Check out our easy to read <a href="#" onclick="showModalInfo('terms')" class="underline-link">GoBrik Terms of Service</a>. Get our <a href="#" onclick="showModalInfo('earthen')" class="underline-link">Earthen monthly newsletter</a>.</p>

        <p data-lang-id="003-read-more" style="font-size:medium; text-align: center;">Read our blog about the <a href="https://earthen.io/gobrik-regen" target="_blank">GoBrik regeneration</a> process.</p>

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
