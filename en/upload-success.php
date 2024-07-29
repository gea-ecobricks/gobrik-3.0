<?php
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));  //grabs language directory from url
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// GoBrik database credentials
$gobrik_servername = "localhost";
$gobrik_username = "ecobricks_brikchain_viewer";
$gobrik_password = "desperate-like-the-Dawn";
$gobrik_dbname = "ecobricks_gobrik_msql_db";


// Create connection for GoBrik database
$gobrik_conn = new mysqli($gobrik_servername, $gobrik_username, $gobrik_password, $gobrik_dbname);
if ($gobrik_conn->connect_error) {
    die("Connection failed: " . $gobrik_conn->connect_error);
}
$gobrik_conn->set_charset("utf8mb4");

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

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php $lang='en';?>
    <?php $version='2.43';?>
    <?php $page='log';?>


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

   <?php require_once ("../includes/log-inc.php");?>

  <div class="splash-content-block"></div>
    <div id="splash-bar"></div>

    <!-- PAGE CONTENT-->
<div id="top-page-image" class="log-step-3" style="margin-top: 105px;z-index: 35;position: absolute;
  text-align:center;width:100% ; height: 36px;"></div>

    <div id="form-submission-box" style="margin-top:80px;">
    <div class="form-container" style="margin-top:-50px;" >
        <div class="splash-form-content-block" style="text-align:center; display:flex;flex-flow:column;">
            <div class="splash-image-2" data-lang-id="003-weigh-plastic-image-alt">
                <img src="../svgs/Happy-turtle-dolphin-opti.svg" style="width:55%; margin:auto" alt="The Earth Thanks You">
            </div>
            <div>
                <h2 data-lang-id="001-form-title">Your Ecobrick <?php echo $serial_no; ?> has been successfully logged!</h2>
            </div>
            <div id="upload-success-message">
                <?php if ($ecobrick_full_photo_url): ?>
                    <div class="photo-container">
                        <img src="https://ecobricks.org/<?php echo $ecobrick_thumb_photo_url; ?>" alt="Basic Ecobrick Photo">
                        <p>Basic Ecobrick Photo</p>
                    </div>
                <?php endif; ?>
                <?php if ($selfie_photo_url): ?>
                    <div class="photo-container">
                        <img src="https://ecobricks.org/<?php echo $selfie_thumb_url; ?>" alt="Ecobrick Selfie Photo">
                        <p>Ecobrick Selfie Photo</p>
                    </div>
                <?php endif; ?>
            </div>
            <p data-lang-id="002-form-description2" style="text-align: center;">The Earth thanks You for your plastic sequestration and plastic transition!</p>
            <a class="confirm-button" href="details-ecobrick-page.php?serial_no=<?php echo $serial_no; ?>" data-lang-id="013-view-ecobrick-post">🎉 View Ecobrick Post</a>
            <form id="deleteForm" action="" method="POST">
                <input type="hidden" name="ecobrick_unique_id" value="<?php echo htmlspecialchars($ecobrick_unique_id); ?>">
                <input type="hidden" name="action" value="delete_ecobrick">
                <a class="confirm-button" style="background:red; cursor:pointer;" id="deleteButton" data-lang-id="014-delete-ecobrick">❌ Delete Ecobrick</a>
            </form>
            <a class="confirm-button" href="log.php" data-lang-id="015-log-another-ecobrick">➕ Log another ecobrick</a>
            <br>
        </div>
    </div>
    <br><br>
</div>

</div>
<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php");?>
</body>
</html>
