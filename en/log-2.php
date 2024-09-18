<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

startSecureSession(); // Start a secure session with regeneration to prevent session fixation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.493';
$page = 'log-2';
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

    $error_message = '';
    $full_urls = [];
    $thumbnail_paths = [];
    $main_file_sizes = [];
    $thumbnail_file_sizes = [];

    if (isset($_GET['id'])) {
        $ecobrick_unique_id = (int)$_GET['id'];

        // Fetch the ecobrick details from the database
        if ($stmt = $gobrik_conn->prepare("SELECT universal_volume_ml, serial_no, density, weight_g FROM tb_ecobricks WHERE ecobrick_unique_id = ?")) {
            $stmt->bind_param("i", $ecobrick_unique_id);
            $stmt->execute();
            $stmt->bind_result($universal_volume_ml, $serial_no, $density, $weight_g);
            $stmt->fetch();
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $gobrik_conn->error;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ecobrick_unique_id'])) {
        $ecobrick_unique_id = (int)$_POST['ecobrick_unique_id'];
        $serial_no = $_POST['serial_no']; // Ensure serial_no is passed from the previous step
        include '../scripts/photo-functions.php';

        $upload_dirs = [
            "basic" => '../briks/2024/basic/',
            "basic-thumb" => '../briks/2024/basic-thumb/',
            "selfie" => '../briks/2024/selfie/',
            "selfie-thumb" => '../briks/2024/selfie-thumb/'
        ];

        $db_fields = [];
        $db_values = [];
        $db_types = "";

        $photo_fields = [
            ["input" => "ecobrick_photo_main", "full" => "ecobrick_full_photo_url", "thumb" => "ecobrick_thumb_photo_url", "dir" => "basic", "thumb_dir" => "basic-thumb"],
            ["input" => "selfie_photo_main", "full" => "selfie_photo_url", "thumb" => "selfie_thumb_url", "dir" => "selfie", "thumb_dir" => "selfie-thumb"]
        ];

        foreach ($photo_fields as $index => $fields) {
            $file_input_name = $fields["input"];
            if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
                $file_extension = strtolower(pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION));
                $new_file_name_webp = "ecobrick-{$serial_no}.webp";
                $thumbnail_file_name_webp = "ecobrick-{$serial_no}.webp";
                $targetPath = $upload_dirs[$fields["dir"]] . $new_file_name_webp;
                $thumbnailPath = $upload_dirs[$fields["thumb_dir"]] . $thumbnail_file_name_webp;

                if (resizeAndConvertToWebP($_FILES[$file_input_name]['tmp_name'], $targetPath, 1000, 88)) {
                    createTrainingThumbnail($targetPath, $thumbnailPath, 250, 250, 77);
                    $full_urls[] = $targetPath;
                    $thumbnail_paths[] = $thumbnailPath;
                    $main_file_sizes[] = filesize($targetPath) / 1024;
                    $thumbnail_file_sizes[] = filesize($thumbnailPath) / 1024;

                    array_push($db_fields, $fields["full"], $fields["thumb"]);
                    array_push($db_values, $targetPath, $thumbnailPath);
                    $db_types .= "ss";
                } else {
                    $error_message .= "Error processing image {$index}. Please try again.<br>";
                }
            }
        }

        if (!empty($db_fields) && empty($error_message)) {
            $fields_for_update = implode(", ", array_map(function($field) { return "{$field} = ?"; }, $db_fields));
            $update_sql = "UPDATE tb_ecobricks SET {$fields_for_update} WHERE ecobrick_unique_id = ?";
            $db_values[] = $ecobrick_unique_id;
            $db_types .= "i";

            $update_stmt = $gobrik_conn->prepare($update_sql);
            $update_stmt->bind_param($db_types, ...$db_values);
            if (!$update_stmt->execute()) {
                $error_message .= "Database update failed: " . $update_stmt->error;
            }
            $update_stmt->close();
        }

        if (!empty($error_message)) {
            ob_end_clean(); // Clean the output buffer before sending headers
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => "An error has occurred: " . $error_message . " END"]);
            exit;
        } else {
            ob_end_clean(); // Clean the output buffer before sending headers
            $response = [
                'ecobrick_unique_id' => $ecobrick_unique_id,
                'full_urls' => $full_urls,
                'thumbnail_paths' => $thumbnail_paths,
                'main_file_sizes' => $main_file_sizes,
                'thumbnail_file_sizes' => $thumbnail_file_sizes
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }

    echo "<script>var density = $density, volume = '$universal_volume_ml', weight = '$weight_g';</script>";
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




   <?php require_once ("../includes/log-2-inc.php");?>



<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="snap-ecobrick top-page-image" style="height: 30px; margin-top: 150px;"></div>

<div id="form-submission-box" class="landing-page-form" style="height:auto !important">
    <div class="form-container">
            <div class="splash-form-content-block" style="text-align:center; display:flex;flex-flow:column;">

                <div class="splash-image-2" data-lang-id="003-weigh-plastic-image-alt">
                    <img src="../svgs/snapit.svg?v=3" style="width:35%; margin:auto" alt="Please take a square photo">
                </div>
                <div><h2 data-lang-id="001-form-title">Record Serial & Take Photo</h2></div>

            </div>

            <p style="text-align: center;"><span data-lang-id="002-form-description-1">Your ecobrick has been logged with a weight of </span><?php echo $weight_g; ?>g, <span data-lang-id="003-form-description-2">a volume of </span><?php echo $universal_volume_ml; ?>ml, <span data-lang-id="004-form-description-3">and a density of </span><?php echo $density; ?>g/ml.<span data-lang-id="005-form-description-4"> Your ecobrick has been allocated the serial number:</span></p>
            <h1 style="text-align: center;"><?php echo $serial_no; ?></h1>

            <br>

            <form id="photoform" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="ecobrick_unique_id" value="<?php echo $ecobrick_unique_id; ?>">
                <input type="hidden" name="serial_no" value="<?php echo $serial_no; ?>">

                <!-- Eenscribe Field -->
              <div class="form-item">
                <label for="enscribe" data-lang-id="006-enscribe-label">How would you like to inscribe the serial number on your ecobrick?</label><br>
                <select id="enscribe" name="enscribe" required>
                    <option value="" disabled selected data-lang-id="007-enscribe-option-1">Select one...</option>
                    <option value="Permanent marker" data-lang-id="008-enscribe-option-2">Permanent marker</option>
                    <option value="Impermanent marker" data-lang-id="009-enscribe-option-3">Impermanent marker</option>
                    <option value="Enamel paint" data-lang-id="010-enscribe-option-4">Enamel paint</option>
                    <option value="Nail polish" data-lang-id="011-enscribe-option-5">Nail polish</option>
                    <option value="Plastic insert" data-lang-id="012-enscribe-option-6">Plastic insert</option>
                    <option value="Other" data-lang-id="013-enscribe-option-7">Other</option>
                </select>
            </div>


                <!-- Photo Options Field -->
               <div class="form-item" id="photo-options-container" style="display: none;">
                    <label for="photo-options" data-lang-id="014-photo-options-label">What kind of photo would you like to log of your ecobrick?</label><br>
                    <select id="photo-options" name="photo-options" required>
                        <option value="" disabled selected data-lang-id="015-photo-options-option-1">Select one...</option>
                        <option value="basic" data-lang-id="016-photo-options-option-2">A basic ecobrick photo</option>
                        <option value="selfie" data-lang-id="017-photo-options-option-3">A selfie photo</option>
                        <option value="both" data-lang-id="018-photo-options-option-4">A basic photo and a selfie photo</option>
                    </select>
                </div>


                <!-- Photo 1 Main & Thumbnail -->
                <div class="form-item" id="basic-photo" style="display: none;">
                    <div style="max-width:500px;margin:auto;">
                        <div style="text-align:center;">
                            <img src="../svgs/basic.svg?v=2" style="width:240px;margin-bottom:15px;">
                        </div>
                        <label for="ecobrick_photo_main" data-lang-id="019-feature-photo">Upload a basic ecobrick photo:</label><br>
                        <ol style="text-align:left;">
                            <li data-lang-id="020-feature-photo-step-1">Take a vertical portrait photo</li>
                            <li data-lang-id="021-feature-photo-step-2">Be sure your photo shows the serial & weight clearly</li>
                            <li data-lang-id="022-feature-photo-step-3">Be sure your photo shows your ecobricks bottom color</li>
                            <li data-lang-id="023-feature-photo-step-4">Be sure your photo shows your ecobricks top</li>
                            <li data-lang-id="024-feature-photo-step-5">Be sure your data is permanently enscribed!</li>
                            <li data-lang-id="025-feature-photo-step-6">Do not use an external label to mark the ecobrick</li>
                        </ol>
                    </div>

                   <div class="photo-upload-container">
                        <label for="ecobrick_photo_main" class="custom-file-upload" data-lang-id="025-basic-photo-labelx">📷 Take Basic Photo
                            <input type="file" id="ecobrick_photo_main" name="ecobrick_photo_main" onchange="displayFileName()">
                        </label>
                        <span id="file-name" class="file-name" data-lang-id="035b-no-file-chosen">No file chosen</span>
                        <p class="form-caption" data-lang-id="026-basic-feature-desc">Take or select a photo of your serialized ecobrick.</p>
                    </div>
                </div>

                <!-- Selfie Photo Main & Thumbnail -->
                <div class="form-item" id="selfie-photo" style="display: none;">
                    <div style="max-width:500px;margin:auto;">
                        <div style="text-align:center;">
                            <img src="../svgs/selfie.svg?v=2" style="height:240px;margin-bottom:15px;">
                        </div>
                        <label for="selfie_photo_main" data-lang-id="027-label-selfie">Upload an ecobrick selfie:</label><br>
                        <ol style="text-align:left;">
                            <li data-lang-id="028-selfie-photo-step-1">Be sure your photo is a horizontal landscape</li>
                            <li data-lang-id="029-selfie-photo-step-2">Be sure your photo shows the serial & weight clearly</li>
                            <li data-lang-id="030-selfie-photo-step-3">Be sure your photo shows your ecobricks bottom color</li>
                            <li data-lang-id="031-selfie-photo-step-4">Be sure your photo shows your ecobricks top</li>
                            <li data-lang-id="032-selfie-photo-step-5">Be sure your data is permanently enscribed!</li>
                            <li data-lang-id="033-selfie-photo-step-6">Do not use an external label to mark the ecobrick</li>
                            <li data-lang-id="034-selfie-photo-step-7">And smile!</li>
                        </ol>
                    </div>

                    <div class="photo-upload-container">
                        <label for="selfie_photo_main" class="custom-file-upload" data-lang-id="035x-selfie-upload">
                            📷 Take Selfie Photo
                            <input type="file" id="selfie_photo_main" name="selfie_photo_main">
                        </label>
                        <span id="file-name-selfie" class="file-name" data-lang-id="035b-no-file-chosen">No file chosen</span>
                        <p class="form-caption" data-lang-id="036-another-photo-optional">Upload your ecobrick selfie.</p>
                    </div>
                </div>

                <div style="display:flex;flex-flow:row;width:100%;justify-content:center;" data-lang-id="037-submit-upload-button">
                    <input type="submit" value="⬆️ Upload Photos" id="upload-progress-button" aria-label="Submit photos for upload">
                </div>

            </form>
        </div>

    </div>

<div style="margin: auto; margin-bottom:100px; margin-top: 50px; text-align:center;">
            <a href="#" onclick="goBack()" aria-label="Go back to re-enter data" class="back-link" data-lang-id="015-go-back-link">↩ Back to Step 1</a>
        </div>

</div>

</div>




	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2024.php");?>

</div>


<script>


document.addEventListener('DOMContentLoaded', function () {
    const enscribeField = document.getElementById('enscribe');
    const photoOptionsField = document.getElementById('photo-options');
    const photoOptionsContainer = document.getElementById('photo-options-container');
    const basicPhotoField = document.getElementById('basic-photo');
    const selfiePhotoField = document.getElementById('selfie-photo');
    const submitButton = document.getElementById('upload-progress-button');

    function showHidePhotoFields() {
        // Show or hide the photo options container based on the enscribe field value
        if (enscribeField.value) {
            photoOptionsContainer.style.display = 'block';
        } else {
            photoOptionsContainer.style.display = 'none';
            basicPhotoField.style.display = 'none';
            selfiePhotoField.style.display = 'none';
            submitButton.style.display = 'none';
            return; // Exit the function early if enscribe field is empty
        }

        // Show or hide photo fields based on photo options field value
        switch (photoOptionsField.value) {
            case 'basic':
                basicPhotoField.style.display = 'block';
                selfiePhotoField.style.display = 'none';
                break;
            case 'selfie':
                selfiePhotoField.style.display = 'block';
                basicPhotoField.style.display = 'none';
                break;
            case 'both':
                basicPhotoField.style.display = 'block';
                selfiePhotoField.style.display = 'block';
                break;
            default:
                basicPhotoField.style.display = 'none';
                selfiePhotoField.style.display = 'none';
                break;
        }

        // Show the submit button if a valid photo option is selected
        if (photoOptionsField.value === 'basic' || photoOptionsField.value === 'selfie' || photoOptionsField.value === 'both') {
            submitButton.style.display = 'block';
        } else {
            submitButton.style.display = 'none';
        }
    }

    // Add event listeners
    enscribeField.addEventListener('input', showHidePhotoFields);
    photoOptionsField.addEventListener('change', showHidePhotoFields);
    document.getElementById('ecobrick_photo_main').addEventListener('change', showHidePhotoFields);
    document.getElementById('selfie_photo_main').addEventListener('change', showHidePhotoFields);

    // Initialize fields on page load
    showHidePhotoFields();
});





    //UPLOAD SUBMIT ACTION AND BUTTON
    document.querySelector('#photoform').addEventListener('submit', function(event) {
        event.preventDefault();

        var button = document.getElementById('upload-progress-button');
        var originalButtonText = button.value; // Save the original button text
        button.innerHTML = '<div class="spinner-photo-loading"></div>'; // Replace button text with spinner
        button.disabled = true; // Disable button to prevent multiple submissions

        var messages = {
            en: "Please choose a file.",
            es: "Por favor, elige un archivo.",
            fr: "Veuillez choisir un fichier.",
            id: "Silakan pilih sebuah berkas."
        };

        var currentLang = window.currentLanguage || 'en';
        var chooseFileMessage = messages[currentLang] || messages.en;

        var fileInput = document.getElementById('ecobrick_photo_main');
        var selfieInput = document.getElementById('selfie_photo_main');

        if ((fileInput.files.length === 0 && selfieInput.files.length === 0)) {
            showFormModal(chooseFileMessage);
            button.innerHTML = originalButtonText; // Restore button text if no file chosen
            button.disabled = false; // Enable button
            return;
        }

        var form = event.target;
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();

        xhr.upload.onprogress = function(event) {
            if (event.lengthComputable) {
                var progress = (event.loaded / event.total) * 100;
                document.getElementById('upload-progress-button').style.backgroundSize = progress + '%';
                document.getElementById('upload-progress-button').classList.add('progress-bar');
            }
        };

        xhr.onreadystatechange = function() {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                button.innerHTML = originalButtonText; // Restore button text after upload
                button.disabled = false; // Enable button
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        var ecobrick_unique_id = response.ecobrick_unique_id;
                        window.location.href = 'upload-success.php?id=' + ecobrick_unique_id; // Redirect to success page with ecobrick_unique_id
                    } catch (e) {
                        console.error('Error parsing server response:', e);
                        handleFormResponse(xhr.responseText); // Handle error response
                    }
                } else {
                    handleFormResponse(xhr.responseText); // Handle error response
                }
            }
        };


        xhr.open(form.method, form.action, true);
        xhr.send(formData);
    });

    function showFormModal(message) {
        const modal = document.getElementById('form-modal-message');
        const messageContainer = modal.querySelector('.modal-message');
        messageContainer.innerHTML = message;
        modal.style.display = 'flex';
    }


</script>

<script>
    // Function to show the density confirmation modal
    function showDensityConfirmation(density, volume, weight) {
        const modal = document.getElementById('form-modal-message');
        const messageContainer = modal.querySelector('.modal-message');

        // Hide all buttons with class "x-button"
        toggleButtonsVisibility(false);

        // Generate content for the modal
        const content = generateModalContent(density, volume, weight, '<?php echo ($lang); ?>'); // For French


        // Update modal content
        messageContainer.innerHTML = content;

        // Show the modal
        modal.style.display = 'flex';
    }

   // Function to generate modal content based on density
function generateModalContent(density, volume, weight, lang) {
    // Determine the translation object based on the selected language
    const translations = lang === 'fr' ? fr_Page_Translations :
                         lang === 'es' ? es_Page_Translations :
                         lang === 'id' ? id_Page_Translations :
                         en_Page_Translations; // Default to English if no match

    if (density < 0.33) {
        return `
            <h1>⛔</h1>
            <h4>${translations.underDensityTitle}</h4>
            <div class="preview-text">${translations.underDensityMessage.replace('${density}', density)}</div>
            <a class="preview-btn" href="/what">${translations.geaStandardsLinkText}</a>
        `;
    } else if (density >= 0.33 && density < 0.36) {
        return `
            <h1>⚠️</h1>
            <h4>${translations.lowDensityTitle}</h4>
            <div class="preview-text">${translations.lowDensityMessage.replace('${density}', density)}</div>
            <a class="module-btn" onclick="closeDensityModal()" aria-label="Click to close modal">${translations.nextRegisterSerial}</a>
        `;
    } else if (density >= 0.36 && density < 0.65) {
        return `
            <h1 style="text-align:center;">👍</h1>
            <h2 style="text-align:center;">${translations.greatJobTitle}</h2>
            <div class="preview-text" style="text-align:center;">${translations.greatJobMessage.replace('${density}', density)}</div>
            <a class="preview-btn" onclick="closeDensityModal()" aria-label="Click to close modal">${translations.nextRegisterSerial}</a>
        `;
    } else if (density >= 0.65 && density < 0.73) {
        return `
            <h1 style="text-align:center;">⚠️</h1>
            <h4 style="text-align:center;">${translations.highDensityTitle}</h4>
            <div class="preview-text" style="text-align:center;">${translations.highDensityMessage.replace('${density}', density).replace('${volume}', volume).replace('${weight}', weight)}</div>
            <a class="preview-btn" onclick="closeDensityModal()" aria-label="Click to close modal">${translations.nextRegisterSerial}</a>
        `;
    } else {
        return `
            <h1 style="text-align:center;">⛔</h1>
            <h4 style="text-align:center;">${translations.overMaxDensityTitle}</h4>
            <div class="preview-text">${translations.overMaxDensityMessage.replace('${density}', density)}</div>
            <a class="preview-btn" href="log.php">${translations.goBack}</a>
        `;
    }
}


    // Function to toggle visibility of "x-button" elements
    function toggleButtonsVisibility(visible) {
        const xButtons = document.querySelectorAll('.x-button');
        xButtons.forEach(button => button.style.display = visible ? 'inline-block' : 'none');
    }

   // Function to close the density confirmation modal
// Function to close the density confirmation modal
function closeDensityModal() {
    const modal = document.getElementById('form-modal-message');
    modal.style.display = 'none';

    // Reset blur effect directly with higher specificity
    document.getElementById('page-content').style.filter = 'none'; // Set to 'none' to remove blur

    // Re-enable body scrolling
    document.body.style.overflow = 'auto'; // Set overflow back to auto to enable scrolling

    // Remove any modal state class if needed
    document.body.classList.remove('modal-open');

    // Show all buttons with class "x-button" again
    toggleButtonsVisibility(true);
}



    // Show the modal on page load
    showDensityConfirmation(density, volume, weight);
</script>



<script>

    //funtion to add the file name under the photo upload button once file has been chosen.

function displayFileName(inputId, spanId) {
    const input = document.getElementById(inputId);
    const fileNameSpan = document.getElementById(spanId);

    if (input.files.length > 0) {
        fileNameSpan.textContent = input.files[0].name;
    } else {
        fileNameSpan.textContent = 'No file chosen';
    }
}

// Add event listeners for both input elements
document.getElementById('ecobrick_photo_main').addEventListener('change', function() {
    displayFileName('ecobrick_photo_main', 'file-name-basic');
});

document.getElementById('selfie_photo_main').addEventListener('change', function() {
    displayFileName('selfie_photo_main', 'file-name-selfie');
});

</script>



</body>
</html>