<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

startSecureSession(); // Start a secure session with regeneration to prevent session fixation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PART 1: Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.541';
$page = 'log';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Initialize user variables
$first_name = '';
$buwana_id = '';
$watershed_id = '';
$location_watershed = '';
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function

// PART 2: Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's continent icon from Buwana
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);

    // Fetch the user's country_id from Buwana
    $country_id = null;
    $sql_country_id = "SELECT country_id FROM users_tb WHERE buwana_id = ?";
    $stmt_country_id = $buwana_conn->prepare($sql_country_id);

    if ($stmt_country_id) {
        $stmt_country_id->bind_param("s", $buwana_id);
        $stmt_country_id->execute();
        $stmt_country_id->bind_result($country_id);
        $stmt_country_id->fetch();
        $stmt_country_id->close();
    }

    // Fetch communities based on the user's country_id
// Fetch the user's current community_id, location_full, and location_watershed from users_tb
$current_community_id = null;
$location_full = null;
$location_watershed = null;
$sql_user_info = "SELECT community_id, location_full, location_watershed FROM users_tb WHERE buwana_id = ?";
$stmt_user_info = $buwana_conn->prepare($sql_user_info);

if ($stmt_user_info) {
    $stmt_user_info->bind_param("i", $buwana_id); // Assuming you have $buwana_id in session or defined
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($current_community_id, $location_full, $location_watershed);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
}

// Fetch communities based on the user's country_id
$communities = [];
$sql_communities = "SELECT com_id, com_name FROM tb_communities WHERE country_id = ?";
$stmt_communities = $gobrik_conn->prepare($sql_communities);

if ($stmt_communities) {
    $stmt_communities->bind_param("i", $country_id); // Bind the fetched country_id
    $stmt_communities->execute();
    $stmt_communities->bind_result($com_id, $com_name);

    while ($stmt_communities->fetch()) {
        $communities[$com_id] = $com_name; // Store both the com_id and com_name for dropdown
    }
    $stmt_communities->close();
}




  // PART 3: POST ECOBRICK DATA to GOBRIK DATABASE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Function to set serial number and ecobrick_unique_id
    function setSerialNumber($gobrik_conn) {
        $query = "SELECT MAX(ecobrick_unique_id) as max_unique_id FROM tb_ecobricks";
        $result = $gobrik_conn->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $max_unique_id = $row['max_unique_id'];
            $new_unique_id = $max_unique_id + 1;
            return [
                'ecobrick_unique_id' => $new_unique_id,
                'serial_no' => $new_unique_id
            ];
        } else {
            throw new Exception('No records found in the database.');
        }
    }

    try {
        // Set serial number and ecobrick ID
        $ids = setSerialNumber($gobrik_conn);
        $ecobrick_unique_id = $ids['ecobrick_unique_id'];
        $serial_no = $ids['serial_no'];
        $brik_notes = "Directly logged on beta.GoBrik.com";
        $date_published_ts = date("Y-m-d H:i:s");

        // Gather form data
        $ecobricker_maker = trim($_POST['ecobricker_maker']);
        $volume_ml = (int)trim($_POST['volume_ml']);
        $weight_g = (int)trim($_POST['weight_g']);
        $sequestration_type = trim($_POST['sequestration_type']);
        $plastic_from = trim($_POST['plastic_from']);
        $brand_name = trim($_POST['brand_name']);
        $community_id = (int)trim($_POST['community_select']); // Get the selected community ID

        // Debugging output to check the community_id value
        error_log("Community ID: " . $community_id);

        // Background settings
        $owner = $ecobricker_maker;
        $status = "not ready";
        $universal_volume_ml = $volume_ml;
        $density = $weight_g / $volume_ml;
        $date_logged_ts = date("Y-m-d H:i:s");
        $CO2_kg = ($weight_g * 6.1) / 1000;
        $last_ownership_change = date("Y-m-d");
        $actual_maker_name = $ecobricker_maker;

        // Location and watershed details
        $location_full = $user_location_full ?? 'Default Location';
        $location_watershed = $user_location_watershed;

        // Prepare the SQL statement
        $sql = "INSERT INTO tb_ecobricks (
            ecobrick_unique_id, serial_no, ecobricker_maker, volume_ml, weight_g, sequestration_type,
            plastic_from, location_full, brand_name, owner, status, universal_volume_ml, density,
            date_logged_ts, CO2_kg, last_ownership_change, actual_maker_name, brik_notes, date_published_ts,
            location_watershed, community_id, country_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $gobrik_conn->prepare($sql)) {
            error_log("Statement prepared successfully.");

            // Bind parameters including the country_id
            $stmt->bind_param(
                "issiisssssssdsdsssssii",
                $ecobrick_unique_id, $serial_no, $ecobricker_maker, $volume_ml, $weight_g,
                $sequestration_type, $plastic_from, $location_full, $brand_name, $owner, $status,
                $universal_volume_ml, $density, $date_logged_ts, $CO2_kg, $last_ownership_change,
                $actual_maker_name, $brik_notes, $date_published_ts,
                $location_watershed, $community_id, $country_id
            );

            error_log("Parameters bound successfully.");

            if ($stmt->execute()) {
                error_log("Statement executed successfully.");

                // Close the statement after execution
                $stmt->close();

                // Close the connection
                $gobrik_conn->close();

                // Redirect to log-2.php with the correct ecobrick_unique_id
                echo "<script>window.location.href = 'log-2.php?id=" . $serial_no . "';</script>";
            } else {
                error_log("Error executing statement: " . $stmt->error);
                echo "Error: " . $stmt->error . "<br>";
            }
        } else {
            error_log("Prepare failed: " . $gobrik_conn->error);
            echo "Prepare failed: " . $gobrik_conn->error;
        }

        // Close the connection only once
        if ($gobrik_conn) $gobrik_conn->close();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo "Error: " . $e->getMessage() . "<br>";
    }
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

 // Fetch first and last name from the Buwana database
    $sql_user = "SELECT first_name, last_name FROM users_tb WHERE buwana_id = ?";
    $stmt_user = $buwana_conn->prepare($sql_user);

    if ($stmt_user) {
        $stmt_user->bind_param("s", $buwana_id); // Assuming buwana_id is a string
        $stmt_user->execute();
        $stmt_user->bind_result($first_name, $last_name);
        $stmt_user->fetch();
        $stmt_user->close();

        $log_full_name = $first_name . ' ' . $last_name;

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('ecobricker_maker').value = '" . htmlspecialchars($log_full_name, ENT_QUOTES) . "';
            });
        </script>";

        if (empty($last_name)) {
            echo "<script>
                setTimeout(function() {
                    const modal = document.getElementById('form-modal-message');
                    const messageContainer = modal.querySelector('.modal-message');
                    messageContainer.innerHTML = `
                        <h3 style=\"text-align:center;\">Oops! We're missing your last name.</h3>
                        <p style=\"text-align:center;\">Looks like your GoBrik account is missing your last name. Ecobricks are best logged with your full name for posterity. Please save your last name here to make ecobrick logging faster:</p>
                        <form id='update-name-form' method='post' action='update_last_name.php'>
                            <label for='first_name'>First Name:</label>
                            <input type='text' id='first_name' name='first_name' value='" . htmlspecialchars($first_name, ENT_QUOTES) . "' required><br>
                            <label for='last_name'>Last Name:</label>
                            <input type='text' id='last_name' name='last_name' required><br>
                            <input type='checkbox' id='update_buwana' name='update_buwana' checked>
                            <label for='update_buwana' style=\"font-size:0.9em\">Update my Buwana account too</label><br>
                            <div style=\"text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;\">
                                <button type='submit' class=\"submit-button enabled\">Save</button>
                                <button type='button' onclick='closeInfoModal()' class=\"submit-button cancel\">Cancel</button>
                            </div>
                        </form>
                    `;
                    modal.style.display = 'flex';
                    document.getElementById('page-content').classList.add('blurred');
                    document.getElementById('footer-full').classList.add('blurred');
                    document.body.classList.add('modal-open');
                }, 5000);
            </script>";
        }  // This displays the last name modal after 5 seconds
    } else {
        echo "Error fetching user information: " . $buwana_conn->error;
    }
    // Close the Buwana connection
    if ($buwana_conn) $buwana_conn->close();

require_once ("../includes/log-inc.php");
?>



<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="log-ecobrick top-page-image" style="height: 30px; margin-top: 140px;"></div>
<div id="form-submission-box" class="landing-page-form" style="height:auto !important">
    <div class="form-container">

    <div id="log-1-banner" class="log-one-ecobrick" style="height:160px;width:100%;margin-top:-20px;"></div>

           <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-log-title">Log an Ecobrick</h2>
            <p style="color:red;font-weight:500;" data-lang-id="002-log-warning">Important: Beta testers do not actually log and serialize an ecobrick.  All ecobricks logged at this stage will be deleted once we launch. Use generic data and photos.</p>
            <h3 data-lang-id="002-log-subheading">Record your ecobrick to the brikchain for projects, posterity and posting!</h3>
        </div>
            <!--LOG FORM-->

            <form id="submit-form" method="post" action="" enctype="multipart/form-data" novalidate>

                <div class="form-item" style="margin-top: 25px;">
                    <label for="ecobricker_maker" data-lang-id="005-ecobricker-maker">Who made this ecobrick?</label><br>
                    <input type="text" id="ecobricker_maker" name="ecobricker_maker" aria-label="Ecobricker Maker" title="Required. Max 255 characters." required>
                    <p class="form-caption" data-lang-id="005b-ecobricker-maker-caption">Provide the name of the ecobricker. Avoid special characters.</p>

                    <!--ERRORS-->
                    <div id="maker-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                    <div id="maker-error-long" class="form-field-error" data-lang-id="000-maker-field-too-long-error">The name is too long. Max 255 characters.</div>
                    <div id="maker-error-invalid" class="form-field-error" data-lang-id="005b-maker-error">The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
                </div>

                <div class="form-item">
                    <label for="volume_ml" data-lang-id="006-volume-ml">Volume of the Ecobrick (in milliliters):</label><br>
                    <select id="volume_ml" name="volume_ml" aria-label="Volume in Milliliters" required>
                        <option value="" disabled selected data-lang-id="006-select-volume">Select volume...</option>
                        <option value="200">250 ml</option>
                        <option value="250">250 ml</option>
                        <option value="300">300 ml</option>
                        <option value="330">330 ml</option>
                        <option value="350">350 ml</option>
                        <option value="360">360 ml</option>
                        <option value="380">380 ml</option>
                        <option value="400">400 ml</option>
                        <option value="450">450 ml</option>
                        <option value="500">500 ml</option>
                        <option value="525">525 ml</option>
                        <option value="550">550 ml</option>
                        <option value="600">600 ml</option>
                        <option value="650">650 ml</option>
                        <option value="700">700 ml</option>
                        <option value="750">750 ml</option>
                        <option value="800">800 ml</option>
                        <option value="900">900 ml</option>
                        <option value="1000">1000 ml</option>
                        <option value="1100">1100 ml</option>
                        <option value="1250">1250 ml</option>
                        <option value="1500">1500 ml</option>
                        <option value="1750">1750 ml</option>
                        <option value="2000">2000 ml</option>
                        <option value="2250">2250 ml</option>
                        <option value="3000">3000 ml</option>
                        <option value="3100">3100 ml</option>
                        <option value="4000">4000 ml</option>
                        <option value="5000">5000 ml</option>
                        <option value="10000">10000 ml</option>
                    </select>
                    <p class="form-caption" data-lang-id="006-volume-ml-caption">Please provide the volume of the ecobrick in milliliters.</p>

                    <!--ERRORS-->
                    <div id="volume-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>

                <div class="form-item">
                    <label for="weight_g" data-lang-id="007-weight-g">Weight of the Ecobrick (in grams):</label><br>
                    <input type="number" id="weight_g" name="weight_g" aria-label="Weight in Grams" min="1" required>
                    <p class="form-caption" data-lang-id="007-weight-g-caption">Please provide the weight of the ecobrick in grams. Round up to the nearest gram.</p>

                    <!--ERRORS-->
                    <div id="weight-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>


                <div class="form-item">
                    <label for="brand_name" data-lang-id="007-brand_name">What brand of bottle is used for this ecobrick?</label><br>
                    <input type="text" id="brand_name" name="brand_name" aria-label="Brand of bottle" required>
                    <p class="form-caption" data-lang-id="007-weight-g-caption">Write the name of the bottle brand</p>
                    <!--ERRORS-->
                    <div id="brand-name-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                    <div id="brand-name-error-long" class="form-field-error" data-lang-id="000-field-too-long-error">This entry should be under 100 characters. All we need is the bottle brand name i.e. "Max Water".</div>
                    <div id="brand-name-error-invalid" class="form-field-error" data-lang-id="000-field-invalid-error">The entry contains invalid characters. Avoid quotes, slashes, and greater-than signs please.</div>
                </div>


                <div class="form-item">
                    <label for="bottom_color" data-lang-id="008-bottom-color">Bottom color of the Ecobrick:</label><br>
                    <select id="bottom_color" name="bottom_color" aria-label="Bottom Color" required>
                        <option value="" disabled selected data-lang-id="009-select-bottom-color">Select bottom color...</option>
                        <option value="No deliberate color set" data-lang-id="010-no-color-set">No deliberate color set</option>
                        <option value="clear" data-lang-id="011-clear">Clear</option>
                        <option value="white" data-lang-id="012-white">White</option>
                        <option value="black" data-lang-id="013-black">Black</option>
                        <option value="yellow" data-lang-id="014-yellow">Yellow</option>
                        <option value="orange" data-lang-id="015-orange">Orange</option>
                        <option value="red" data-lang-id="016-red">Red</option>
                        <option value="pink" data-lang-id="017-pink">Pink</option>
                        <option value="purple" data-lang-id="018-purple">Purple</option>
                        <option value="violet" data-lang-id="019-violet">Violet</option>
                        <option value="dark blue" data-lang-id="020-dark-blue">Dark blue</option>
                        <option value="sky blue" data-lang-id="021-sky-blue">Sky blue</option>
                        <option value="brown" data-lang-id="022-brown">Brown</option>
                        <option value="grey" data-lang-id="023-grey">Grey</option>
                        <option value="silver" data-lang-id="024-silver">Silver</option>
                        <option value="gold" data-lang-id="025-gold">Gold</option>
                        <option value="cigbrick beige" data-lang-id="026-cigbrick-beige">Cigbrick beige</option>
                    </select>
                    <p class="form-caption" data-lang-id="008-bottom-color-caption">Please select the bottom color of the ecobrick.</p>

                    <!--ERRORS-->
                    <div id="color-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>


                <div class="form-item">
                    <label for="sequestration_type" data-lang-id="009-sequestration-type">What kind of ecobrick is this?</label><br>
                    <select id="sequestration_type" name="sequestration_type" aria-label="Sequestration Type" required>
                        <option value="" disabled selected data-lang-id="011-select-ecobrick-type">Select ecobrick type...</option>
                        <option value="Regular ecobrick" data-lang-id="012-regular-ecobrick">Regular ecobrick</option>
                        <option value="cigbrick" data-lang-id="013-cigbrick">Cigbrick</option>
                        <option value="ocean ecobrick" data-lang-id="014-ocean-ecobrick">Ocean ecobrick</option>
                    </select>
                    <p class="form-caption" data-lang-id="009-sequestration-type-caption">Please select the type of ecobrick. Learn more about <a href="#" onclick="showModalInfo('ocean')" class="underline-link">Ocean Ecobricks</a>, <a href="#" onclick="showModalInfo('cigbrick')" class="underline-link">Cigbricks</a> and <a href="#" onclick="showModalInfo('regular')" class="underline-link">Regular ecobricks</a>.</p>


                    <!--ERRORS-->
                    <div id="type-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>

                <div class="form-item">
                    <label for="plastic_from" data-lang-id="010-plastic-from">Where is the plastic from?</label><br>
                    <select id="plastic_from" name="plastic_from" aria-label="Plastic From" required>
                        <option value="" disabled selected data-lang-id="015-select-plastic-source">Select plastic source...</option>
                        <option value="Home" data-lang-id="016-home">Home</option>
                        <option value="Business" data-lang-id="017-business">Business</option>
                        <option value="Community" data-lang-id="018-community">Neighbourhood</option>
                        <option value="Factory" data-lang-id="019-factory">Factory</option>
                        <option value="Beach" data-lang-id="020-beach">Beach</option>
                        <option value="Ocean" data-lang-id="021-ocean">Ocean</option>
                        <option value="River" data-lang-id="022-river">River</option>
                        <option value="Forest" data-lang-id="023-forest">Forest</option>
                        <option value="Field" data-lang-id="024-field">Field</option>
                    </select>
                    <p class="form-caption" data-lang-id="010-plastic-from-caption">From where was your ecobrick's plastic sourced?</p>

                    <!--ERRORS-->
                    <div id="plastic-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                </div>



                <div id="localize-box" class="advanced-box" aria-expanded="false" role="region" aria-labelledby="advancedBoxLabel-1">
                    <div class="advanced-box-header"  id="advancedBoxLabel-1">
                        <div class="advanced-title" data-lang-id="013-advanced-options">Edit Localization</div>
                        <div class="advanced-open-icon">+</div>
                    </div>
                    <div class="advanced-box-content">

                        <p>Whenever you log an ecobrick it is tagged with your own Buwana account localization.  You can edit these defaults here:</p>>

                       <div class="form-item">
                            <label for="community_select">Select Your Community:</label><br>
                            <select id="community_select" name="community_select" required>
                                <option value="" disabled selected>Select a community...</option>
                                <?php foreach ($communities as $com_id => $com_name): ?>
                                    <option value="<?= htmlspecialchars($com_id, ENT_QUOTES); ?>"
                                        <?php if ($com_id == $current_community_id) echo 'selected'; ?>>
                                        <?= htmlspecialchars($com_name, ENT_QUOTES); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-caption">Select your community from the list based on your country.</p>
                        </div>

                    <div class="form-item">
                        <label for="location_full" data-lang-id="011-location-full">Where is this ecobrick based?</label><br>
                        <div class="input-container">
                            <input type="text" id="location_full" name="location_full"
                                   value="<?= htmlspecialchars($location_full, ENT_QUOTES); ?>"
                                   aria-label="Location Full" required style="padding-left:45px;">
                            <div id="loading-spinner" class="spinner" style="display: none;"></div>
                        </div>
                        <p class="form-caption" data-lang-id="011-location-full-caption">Start typing the name of your town or city, and we'll fill in the rest using the open source, non-corporate openstreetmaps API. Avoid using your exact address for privacy-- just your town, city or country is fine.</p>

                        <!--ERRORS-->
                        <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
                    </div>


                    <!-- Location Watershed -->
                    <div class="form-item">
                        <label for="location_watershed" data-lang-id="011-watershed-location">Your local river:</label><br>
                        <div class="input-container">
                            <input type="text" id="location_watershed" name="location_watershed"
                                   value="<?= htmlspecialchars($location_watershed, ENT_QUOTES); ?>"
                                   aria-label="Location Watershed" style="padding-left:45px;">
                            <div id="loading-spinner-watershed" class="spinner" style="display: none;"></div>
                            <div id="watershed-pin" class="pin-icon">💧</div>
                        </div>
                        <p class="form-caption">💚 Rivers and their basins provide a great non-political way to localize our users by ecological region!</p>
                    </div>


                    <!--<div class="form-item">
                            <label for="project_id" data-lang-id="014-project-id">Is this ecobrick part of a project?</label><br>
                            <input type="number" id="project_id" name="project_id" aria-label="Project ID">
                            <p class="form-caption" data-lang-id="014-project-id-caption">Optional: Provide the project ID if this ecobrick is part of a project.</p>
                            <div id="project-error-long" class="form-field-error" data-lang-id="000-field-too-long-error">Entry is too long.</div>
                        </div>

                        <div class="form-item">
                            <label for="training_id" data-lang-id="015-training-id">Was this ecobrick made in a training?</label><br>
                            <input type="number" id="training_id" name="training_id" aria-label="Training ID">
                            <p class="form-caption" data-lang-id="015-training-id-caption">Optional: Provide the training ID if this ecobrick was made in a training.</p>
                            <div id="training-error-long" class="form-field-error" data-lang-id="000-field-too-long-error">Entry is too long.</div>
                        </div>-->


                    </div>
                </div>





                <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;margin-top:30px;">
                    <input type="submit" class="submit-button enabled" value="Next: Density Check" aria-label="Submit Form">
                </div>

                <input type="hidden" id="location_country" name="location_country">
                <input type="hidden" id="location_full" name="location_full">


            </form>


            <!--END OF FORM-->

        </div>

    </div>

</div>

</div> <!-- main? -->



<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php");?>

</div> <!--page content-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const localizeBox = document.getElementById('localize-box');
    const advancedBoxHeader = document.querySelector('.advanced-box-header');
    const advancedBoxContent = document.querySelector('.advanced-box-content');
    const advancedOpenIcon = document.querySelector('.advanced-open-icon');

    advancedBoxHeader.addEventListener('click', function () {
        // Toggle aria-expanded attribute
        const isExpanded = localizeBox.getAttribute('aria-expanded') === 'true';
        localizeBox.setAttribute('aria-expanded', !isExpanded);

        // Toggle visibility of advanced-box-content
        if (isExpanded) {
            advancedBoxContent.style.display = 'none';  // Collapse content
            advancedOpenIcon.textContent = '+';  // Change icon to plus
        } else {
            advancedBoxContent.style.display = 'block';  // Expand content
            advancedOpenIcon.textContent = '-';  // Change icon to minus
        }
    });
});





    document.getElementById('submit-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the form from submitting until validation is complete
        var isValid = true; // Flag to determine if the form should be submitted

        // Helper function to display error messages
        function displayError(elementId, showError) {
            var errorDiv = document.getElementById(elementId);
            if (showError) {
                errorDiv.style.display = 'block'; // Show the error message
                isValid = false; // Set form validity flag
            } else {
                errorDiv.style.display = 'none'; // Hide the error message
            }
        }

        // Helper function to check for invalid characters
        function hasInvalidChars(value) {
            const invalidChars = /[\'\"><]/; // Regex for invalid characters
            return invalidChars.test(value);
        }

        // 1. Ecobricker Maker Validation
        var ecobrickerMaker = document.getElementById('ecobricker_maker').value.trim();
        displayError('maker-error-required', ecobrickerMaker === '');
        displayError('maker-error-long', ecobrickerMaker.length > 255);
        displayError('maker-error-invalid', hasInvalidChars(ecobrickerMaker));

        // 2. Volume (ml) Validation
        var volumeML = parseInt(document.getElementById('volume_ml').value, 10);
        displayError('volume-error-required', isNaN(volumeML) || volumeML < 1);

        // 3. Weight (g) Validation
        var weightG = parseInt(document.getElementById('weight_g').value, 10);
        displayError('weight-error-required', isNaN(weightG) || weightG < 1);

        // 4. Sequestration Type Validation
        var sequestrationType = document.getElementById('sequestration_type').value.trim();
        displayError('type-error-required', sequestrationType === '');

        // 5. Plastic From Validation
        var plasticFrom = document.getElementById('plastic_from').value.trim();
        displayError('plastic-error-required', plasticFrom === '');



        // 10. Brand Name Validation
        var brandName = document.getElementById('brand_name').value.trim();
        displayError('brand-name-error-required', brandName === '');
        displayError('brand-name-error-long', brandName.length > 100);
        displayError('brand-name-error-invalid', hasInvalidChars(brandName));

        // If all validations pass, submit the form
        if (isValid) {
            this.submit();
        } else {
            // Scroll to the first error message and center it in the viewport
            var firstError = document.querySelector('.form-field-error[style="display: block;"]');
            if (firstError) {
                firstError.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
                // Optionally, find the related input and focus it
                var relatedInput = firstError.closest('.form-item').querySelector('input, select, textarea');
                if (relatedInput) {
                    relatedInput.focus();
                }
            }
        }
    });



    $(function() {
        let debounceTimer;
        $("#location_full").autocomplete({
            source: function(request, response) {
                $("#loading-spinner").show();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "https://nominatim.openstreetmap.org/search",
                        dataType: "json",
                        headers: {
                            'User-Agent': 'ecobricks.org'
                        },
                        data: {
                            q: request.term,
                            format: "json"
                        },
                        success: function(data) {
                            $("#loading-spinner").hide();
                            response($.map(data, function(item) {
                                return {
                                    label: item.display_name,
                                    value: item.display_name,
                                    lat: item.lat,
                                    lon: item.lon
                                };
                            }));
                        },
                        error: function(xhr, status, error) {
                            $("#loading-spinner").hide();
                            console.error("Autocomplete error:", error);
                            response([]);
                        }
                    });
                }, 300);
            },
            select: function(event, ui) {
                console.log('Selected location:', ui.item); // Debugging line
                $('#lat').val(ui.item.lat);
                $('#lon').val(ui.item.lon);
            },
            minLength: 3
        });

        $('#submit-form').on('submit', function() {
            console.log('Latitude:', $('#lat').val());
            console.log('Longitude:', $('#lon').val());
            // alert('Latitude: ' + $('#lat').val() + ', Longitude: ' + $('#lon').val());
        });
    });

</script>




</body>
</html>
