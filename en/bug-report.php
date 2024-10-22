<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.12';
$page = 'bug-report';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

//startSecureSession(); // Start a secure session with regeneration to prevent session fixation

// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Run messenger code here

    // Close the database connections
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    // Redirect to login page with the redirect parameter set to the current page
    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "login.php?redirect=' . urlencode($page) . '";
    </script>';
    exit();
}

// Output the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>



<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/bug-report-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>
<div id="top-page-image" class="bug-reported top-page-image"></div>

<!-- BUG REPORT FORM CONTENT -->
<div id="form-submission-box" style="height:fit-content;margin-top: 90px;">
    <div class="form-container">
        <div id="greeting" style="text-align:center;width:100%;margin:auto;">
            <h2 id="greeting" data-lang-id="001-bug-report-title">Report a Bug</h2>
            <p id="subgreeting" data-lang-id="002-bug-report-subtitle">GoBrik 3.0 has just launched. Help us catch all the bugs by reporting any problems you encounter. Messages go to our volunteer development team.</p>
        </div>

        <!-- Bug Report Form -->
        <form id="bugReportForm" data-lang-id="003-bug-form">
            <div class="bug-report-input-wrapper" style="position: relative;">
                <textarea id="bugReportInput" placeholder="What went wrong? Or... what could be better?" rows="6" required></textarea>
                <input type="file" id="imageUploadInput" accept="image/jpeg, image/jpg, image/png, image/webp" style="display: none;" />
                <span id="imageFileName" class="image-file-name"></span>
                <button type="button" id="uploadPhotoButton" class="upload-photo-button" title="Upload Photo" aria-label="Upload Photo">📸</button>
            </div>

            <button type="submit" id="bugReportSubmit" class="submit-button enabled">🐞 Submit Bug</button>
        </form>

        <!-- Feedback Message -->
        <div id="feedbackMessage" class="hidden"></div>


    </div>

<div style="padding:10px; text-align: center; margin: auto; align-self: center;margin-top: 0px;width:80%;">
        <p class="caption" data-lang-id="10-sending-info" style="font-size: 0.8em;color:var(--subdued-text);">When you send a bug report your message and details on your browser and OS will be sent to our development team over GoBrik messenger.</p>
    </div>
</div>


</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2024.php"); ?>
<script>
    const userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP
    const userContinentIcon = '<?php echo addslashes($user_continent_icon); ?>';
    const userLocationWatershed = '<?php echo addslashes($user_location_watershed); ?>';
    const userLocationFull = '<?php echo addslashes($user_location_full); ?>';
    const geaStatus = '<?php echo addslashes($gea_status); ?>';
    const userCommunityName = '<?php echo addslashes($user_community_name); ?>';

    $(document).ready(function() {
        $('#bugReportForm').on('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            const bugReport = $('#bugReportInput').val().trim();
            if (bugReport) {
                // Get the browser info and user info
                const browserInfo = getBrowserInfo();
                const userInfo = getUserData();

                // Append the browser info and user info to the user's bug report message
                const messageWithInfo = `${bugReport}<br><br><hr><br>${browserInfo}<br><br><hr><br>${userInfo}`;

                $.ajax({
                    url: '../messenger/create_bug_report.php',
                    method: 'POST',
                    data: {
                        created_by: userId, // Pass the user's ID from PHP
                        message: messageWithInfo
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#feedbackMessage').removeClass('hidden').text('Bug report submitted successfully.');
                            $('#bugReportInput').val(''); // Clear the input field
                        } else {
                            $('#feedbackMessage').removeClass('hidden').text('Failed to submit bug report. Please try again.');
                        }
                    },
                    error: function(error) {
                        console.error('Error submitting bug report:', error);
                        $('#feedbackMessage').removeClass('hidden').text('An error occurred while submitting your bug report. Please try again.');
                    }
                });
            }
        });

        const maxFileSize = 10 * 1024 * 1024; // 10 MB

            // Handle photo upload button click
            $('#uploadPhotoButton').on('click', function() {
                $('#imageUploadInput').click(); // Trigger the hidden file input
            });

            // Handle file selection
            $('#imageUploadInput').on('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    if (validTypes.includes(file.type) && file.size <= maxFileSize) {
                        // Show the file name
                        $('#imageFileName').text(file.name);

                        // Change the button to show a spinner
                        $('#uploadPhotoButton').addClass('uploading').prop('disabled', true);

                        // Upload the file via AJAX
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('user_id', userId);

                        $.ajax({
                            url: '../messenger/upload_image_attachment.php',
                            method: 'POST',
                            data: formData,
                            processData: false, // Don't process the files
                            contentType: false, // Set content type to false as jQuery will tell the server it's a form data
                            success: function(response) {
                                // Handle successful upload
                                $('#uploadPhotoButton').removeClass('uploading').prop('disabled', false);
                                // You can store the file URL or handle the response as needed
                                $('#feedbackMessage').removeClass('hidden').text('Image uploaded successfully.');
                            },
                            error: function(error) {
                                console.error('Error uploading image:', error);
                                $('#uploadPhotoButton').removeClass('uploading').prop('disabled', false);
                                $('#feedbackMessage').removeClass('hidden').text('An error occurred while uploading the image. Please try again.');
                            }
                        });
                    } else {
                        alert('🤔 Hmmm... looks like this isn\'t an image file, or else it\'s over 10MB. Please try another file.');
                        $('#imageUploadInput').val(''); // Reset the input if validation fails
                        $('#imageFileName').text(''); // Clear any displayed file name
                    }
                }
            });

        function getBrowserInfo() {
            const userAgent = navigator.userAgent;
            const platform = navigator.platform;
            const appVersion = navigator.appVersion;
            const appName = navigator.appName;
            const appCodeName = navigator.appCodeName;
            const screenWidth = window.screen.width;
            const screenHeight = window.screen.height;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const language = navigator.language || navigator.userLanguage;

            return `Browser Info:
- App Name: ${appName}
- App Version: ${appVersion}
- App Code Name: ${appCodeName}
- User Agent: ${userAgent}
- Platform: ${platform}
- Language: ${language}
- Screen Size: ${screenWidth}x${screenHeight}
- Viewport Size: ${viewportWidth}x${viewportHeight}`;
        }

        function getUserData() {
            return `User Info:
- Continent Icon: ${userContinentIcon}
- Location Watershed: ${userLocationWatershed}
- Full Location: ${userLocationFull}
- GEA Status: ${geaStatus}
- Community Name: ${userCommunityName}`;
        }
    });
</script>



</body>
</html>