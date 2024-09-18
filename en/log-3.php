<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

startSecureSession(); // Start a secure session with regeneration to prevent session fixation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.441';
$page = 'log-3';
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

    // Fetch the user's continent icon
    $country_icon = getUserContinent($buwana_conn, $buwana_id);
    $watershed_name = getWatershedName($buwana_conn, $buwana_id, $lang); // Corrected to include the $lang parameter

    // Fetch the user's first name from the database
    $first_name = getUserFirstName($buwana_conn, $buwana_id);

    if (isset($_GET['id'])) {
        $ecobrick_unique_id = (int)$_GET['id'];

        // Fetch the ecobrick details from the database
        $sql = "SELECT serial_no, ecobrick_full_photo_url, ecobrick_thumb_photo_url, selfie_photo_url, selfie_thumb_url
                FROM tb_ecobricks
                WHERE ecobrick_unique_id = ?";
        $stmt = $gobrik_conn->prepare($sql);
        $stmt->bind_param("i", $ecobrick_unique_id);
        $stmt->execute();
        $stmt->store_result(); // Store result to check number of rows

        if ($stmt->num_rows > 0) {
            // Ecobrick found, fetch its data
            $stmt->bind_result($serial_no, $ecobrick_full_photo_url, $ecobrick_thumb_photo_url, $selfie_photo_url, $selfie_thumb_url);
            $stmt->fetch();
            $stmt->close();
        } else {
            // No ecobrick found, show alert and redirect
            $alert_message = getNoEcobrickAlert($lang);
            echo "<script>
                alert('" . addslashes($alert_message) . "');
                window.location.href = 'log.php';
            </script>";
            exit;
        }
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

<?php require_once ("../includes/log-3-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT-->
<div id="top-page-image" class="top-page-image log-step-3" style="margin-top: 105px; z-index: 35; position: absolute; text-align:center;width:100% ; height: 36px;"></div>

<div id="form-submission-box" style="margin-top:83px;">
    <div class="form-container" style="padding-top:75px;">
        <div class="splash-form-content-block" style="text-align:center; display:flex;flex-flow:column;">
            <div id="upload-success-message">
                <?php if (!empty($ecobrick_full_photo_url) && $ecobrick_full_photo_url !== 'url missing'): ?>
                    <div class="photo-container">
                        <img src="<?php echo htmlspecialchars($ecobrick_full_photo_url); ?>" alt="Basic Ecobrick Photo" style="max-width:500px;" title="Basic ecobrick photo - full">
                    </div>
                <?php endif; ?>
                <?php if ($selfie_photo_url): ?>
                    <div class="photo-container">
                        <img src="<?php echo htmlspecialchars($selfie_photo_url); ?>" alt="Ecobrick Selfie Photo" title="Ecobrick selfie photo" style="max-width:500px;">
                    </div>
                <?php endif; ?>

            </div>

            <h2 id="ecobrick-logged-title" data-lang-id="001-form-title">Ecobrick <?php echo $serial_no; ?> is logged! 🎉</h2>


            <!-- Vision Form -->
            <form id="add-vision-form">
                <p>Optionally, you may now add a vision to your ecobrick. This is a short message: a vision, a wish, or a prayer for the future. The message will be added to your ecobrick's record on the brikchain and visible to anyone who reviews your ecobrick's data.</p>

                <textarea name="vision_message" id="vision_message" rows="4" style="width:100%;font-size:1.3em; border-radius:15px;" placeholder="Your vision for this ecobrick and its future..."></textarea>
                <input type="hidden" name="ecobrick_unique_id" value="<?php echo htmlspecialchars($ecobrick_unique_id); ?>">
                <div style="display: flex; gap: 10px; width: 100%;">
                    <button type="submit" class="confirm-button" style="flex-grow: 1; margin-top: 10px;">Save</button>
                    <a class="confirm-button" style="background:grey; cursor:pointer; flex-grow: 1; margin-top: 10px; text-align: center;" id="skip-button" data-lang-id="014-skip-button">Skip: Complete Logging</a>
                </div>
            </form>




            <div id="next-options" style="display:none;">
                <div class="conclusion-message"  style="font-family:'Mulish',sans-serif; font-size:1.4em;color:var(--h1);margin-top:20px;"><span data-lang-id="003-logging-is">Logging of Ecobrick </span><?php echo $serial_no; ?><span data-lang-id="003-complete">is Completed. 👍</span></div>
                <h2 data-lang-id="003-recorded-ready">We and the earth thank you.</h2>
                <h3>🙏 💚 🌏</h3><br>

                <a class="confirm-button" href="brik.php?serial_no=<?php echo $serial_no; ?>" data-lang-id="013-view-ecobrick-post" style="width:300px;">View Ecobrick Post</a>
                <a class="confirm-button" href="log.php" data-lang-id="015-log-another-ecobrick" style="width:300px;">➕ Log another ecobrick</a>
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="ecobrick_unique_id" value="<?php echo htmlspecialchars($ecobrick_unique_id); ?>">
                    <input type="hidden" name="action" value="delete_ecobrick">
                    <a class="confirm-button" style="background:red; cursor:pointer;width:300px;" id="deleteButton" data-lang-id="014-delete-ecobrick">❌ Delete Ecobrick</a>
                </form>
                <br>
                <div id="vision-added-failure" style="display:none;font-size:1.2em;">
                <p>😭 Hmmm... something went wrong adding your vision to <?php echo $ecobrick_unique_id; ?>'s record. Let us know on the beta test or bug review form, please!</p>
                <p id="post-error-message"></p>
            </div>
                <div id="vision-added-success" style="display:none;font-size:1.2em;">
                <p><span style="color:green;">✔</span> Vision successfully added to ecobrick <?php echo $ecobrick_unique_id; ?>'s record.</p>
            </div>
                <div id="conclusion-message" data-lang-id="003-recorded-ready" style="font-family:'Mulish',sans-serif; font-size:1.2em;color:var(--text-color);"><span style="color:green;">✔</span> Your ecobrick is now in the validation queue now pending peer review.</div>
            </div>

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
            const action = document.querySelector('input[name="action"]').value;

            fetch('delete-ecobrick.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'ecobrick_unique_id': ecobrickUniqueId,
                    'action': action // Include the action field
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }
                return response.json(); // Expecting JSON from the server
            })
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


<!-- JavaScript to handle form submission -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const visionForm = document.getElementById('add-vision-form');
    const visionMessage = document.getElementById('vision_message');
    const ecobrickLoggedTitle = document.getElementById('ecobrick-logged-title');
    const visionAddedSuccess = document.getElementById('vision-added-success');
    const visionAddedFailure = document.getElementById('vision-added-failure');
    const nextOptions = document.getElementById('next-options');
    const skipButton = document.getElementById('skip-button');
    const postErrorMessage = document.getElementById('post-error-message');

    // Function to hide the form and show the next steps
    function showNextOptions() {
        ecobrickLoggedTitle.style.display = 'none';
        visionForm.style.display = 'none';
        nextOptions.style.display = 'block';
    }

    // Event listener for the 'Skip: Complete Logging' button
    skipButton.addEventListener('click', function (event) {
        event.preventDefault();
        showNextOptions();
    });

    // Event listener for the form submission
    visionForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        // Check if the vision message is empty
        if (visionMessage.value.trim() === '') {
            alert("Seems you forgot to actually add a vision! Please try again or hit Skip.");
            return;
        }

        // Send form data to log_vision.php
        const formData = new FormData(visionForm);

        fetch('log_vision.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    visionAddedSuccess.style.display = 'block';
                } else {
                    visionAddedFailure.style.display = 'block';
                    postErrorMessage.textContent = data.error;
                }
                showNextOptions();
            })
            .catch(error => {
                console.error('Error:', error);
                visionAddedFailure.style.display = 'block';
                postErrorMessage.textContent = 'A network error occurred. Please try again later.';
                showNextOptions();
            });
    });
});


</script>



</body>
</html>
