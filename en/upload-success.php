<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

startSecureSession(); // Start a secure session with regeneration to prevent session fixation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.43';
$page = 'log';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Initialize user variables
$first_name = '';
$buwana_id = '';
$country_icon = '';
$watershed_id = '';
$watershed_name = '';
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function


// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    if (isset($_GET['id'])) {
        $ecobrick_unique_id = (int)$_GET['id'];

        // Fetch the ecobrick details from the database
        $sql = "SELECT serial_no, ecobrick_full_photo_url, ecobrick_thumb_photo_url, selfie_photo_url, selfie_thumb_url
                FROM tb_ecobricks
                WHERE ecobrick_unique_id = ?";
        $stmt = $gobrik_conn->prepare($sql);
        $stmt->bind_param("i", $ecobrick_unique_id);
        $stmt->execute();
        $stmt->bind_result($serial_no, $ecobrick_full_photo_url, $ecobrick_thumb_photo_url, $selfie_photo_url, $selfie_thumb_url);
        $stmt->fetch();
        $stmt->close();
    } else {
        echo "No ecobrick ID provided.";
        exit;
    }
} else {
    // Redirect to login page with the redirect parameter set to the current page
    header('Location: login.php?redirect=' . urlencode($page));
    exit();
}

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>


   <?php require_once ("../includes/log-inc.php");?>

  <div class="splash-title-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT-->
<div id="top-page-image" class="log-step-3" style="margin-top: 105px;z-index: 35;position: absolute;
  text-align:center;width:100% ; height: 36px;"></div>

    <div id="form-submission-box" style="margin-top:80px;">
    <div class="form-container" style="margin-top:-50px;" >
        <div class="splash-form-content-block" style="text-align:center; display:flex;flex-flow:column;">
            <div class="splash-image-2" data-lang-id="003-weigh-plastic-image-alt">
                <img src="../svgs/Happy-turtle-dolphin-opti.svg" style="width:39%; margin:auto; margin-top:-100px;" alt="The Earth Thanks You">
            </div>
            <div>
                <h2 data-lang-id="001-form-title">Your Ecobrick <?php echo $serial_no; ?> has been successfully logged!</h2>
            </div>
            <div id="upload-success-message">
                <?php if ($ecobrick_full_photo_url): ?>
                    <div class="photo-container">
                        <img src="<?php echo $ecobrick_full_photo_url; ?>" alt="Basic Ecobrick Photo" style="max-width:500px;">
                        <p class="photo-caption" style="font-size:0.9em;color:grey;text-align:center;">Basic Ecobrick Photo</p>
                    </div>
                <?php endif; ?>
                <?php if ($selfie_photo_url): ?>
                    <div class="photo-container">
                        <img src="<?php echo $selfie_thumb_url; ?>" alt="Ecobrick Selfie Photo">
                        <p class="photo-caption" style="font-size:1em;text-align:center;">Ecobrick Selfie Photo</p>
                    </div>
                <?php endif; ?>
            </div>
            <p data-lang-id="002-form-description2" style="text-align: center;">The Earth thanks You for your plastic sequestration and plastic transition!</p>
            <a class="confirm-button" href="brik.php?serial_no=<?php echo $serial_no; ?>" data-lang-id="013-view-ecobrick-post" style="width:300px;">🎉 View Ecobrick Post</a>
            <form id="deleteForm" action="" method="POST">
                <input type="hidden" name="ecobrick_unique_id" value="<?php echo htmlspecialchars($ecobrick_unique_id); ?>">
                <input type="hidden" name="action" value="delete_ecobrick">
                <a class="confirm-button" style="background:red; cursor:pointer;width:300px;" id="deleteButton" data-lang-id="014-delete-ecobrick">❌ Delete Ecobrick</a>
            </form>
            <a class="confirm-button" href="log.php" data-lang-id="015-log-another-ecobrick" style="width:300px;">➕ Log another ecobrick</a>
            <br>
        </div>
    </div>
    <br><br>
</div>

</div>
<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php");?>


    <script>

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('deleteButton').addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default action
                if (confirm('Are you sure you want to delete this ecobrick from the database? This cannot be undone.')) {
                    const ecobrickUniqueId = document.querySelector('input[name="ecobrick_unique_id"]').value;

                    fetch('delete-ecobrick.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'ecobrick_unique_id': ecobrickUniqueId
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Your ecobrick has been successfully deleted. You may now log another ecobrick...');
                                window.location.href = 'log.php';
                            } else {
                                alert('There was an error deleting the ecobrick: ' + data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('There was an error processing your request.');
                        });
                }
            });
        });

    </script>

</body>
</html>
