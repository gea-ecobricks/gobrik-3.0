<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (isLoggedIn()) {
    header('Location: dashboard.php'); // Redirect to dashboard if the user is logged in
    exit();
}

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.68';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = false; // Ensure not logged in for this page

// Initialize variables
$buwana_id = $_GET['id'] ?? null;  // Correctly initializing buwana_id
$page = 'activate';
$first_name = '';

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

// PART 2: Check if buwana_id is passed in the URL
if (is_null($buwana_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No buwana ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 3: Look up user information using buwana_id provided in URL
require_once("../buwanaconn_env.php");

// Fetch user information using buwana_id from the Buwana database
$sql_user_info = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
$stmt_user_info = $buwana_conn->prepare($sql_user_info);

if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $buwana_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($first_name);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
} else {
    die('Error preparing statement for fetching user info: ' . $buwana_conn->error);
}

// Ensure $first_name is set and not empty
if (empty($first_name)) {
    $first_name = 'User'; // Fallback if first name is not set
}

// PART 4: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_location_full = $_POST['location_full'];
    $user_lat = $_POST['latitude'];
    $user_lon = $_POST['longitude'];

    // Extract country from the last term in the location string (after the last comma)
    $location_parts = explode(',', $user_location_full);
    $selected_country = trim(end($location_parts));

    // Fetch the country_id from the countries_tb using the extracted country name
    $sql_country = "SELECT country_id, continent_code FROM countries_tb WHERE country_name = ?";
    $stmt_country = $buwana_conn->prepare($sql_country);

    if ($stmt_country) {
        $stmt_country->bind_param('s', $selected_country);
        $stmt_country->execute();
        $stmt_country->bind_result($set_country_id, $set_continent_code);
        $stmt_country->fetch();
        $stmt_country->close();
    } else {
        die('Error preparing statement for fetching country info: ' . $buwana_conn->error);
    }

    // Check if the country was found in the database
    if (empty($set_country_id) || empty($set_continent_code)) {
        echo '<script>alert("Could not determine your country or continent based on your location. Please refine your location details.");</script>';
    } else {
        // Update the Buwana user's continent, country using buwana_id
        $sql_update_buwana = "UPDATE users_tb SET continent_code = ?, country_id = ?, location_full = ?, location_lat = ?, location_long = ? WHERE buwana_id = ?";
        $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
        if ($stmt_update_buwana) {
            $stmt_update_buwana->bind_param('sissdi', $set_continent_code, $set_country_id, $user_location_full, $user_lat, $user_lon, $buwana_id);
            $stmt_update_buwana->execute();
            $stmt_update_buwana->close();

            // PART 5: Open GoBrik connection and update tb_ecobrickers to set buwana_activated to 1
            require_once("../gobrikconn_env.php");

            $sql_update_gobrik = "UPDATE tb_ecobrickers SET buwana_activated = 1 WHERE buwana_id = ?";
            $stmt_update_gobrik = $gobrik_conn->prepare($sql_update_gobrik);

            if ($stmt_update_gobrik) {
                $stmt_update_gobrik->bind_param('i', $buwana_id); // Update based on the ecobricker's unique identifier
                if ($stmt_update_gobrik->execute()) {
                    // Successfully updated GoBrik
                    $stmt_update_gobrik->close();
                } else {
                    error_log('Error executing update on tb_ecobrickers: ' . $stmt_update_gobrik->error);
                    echo "Failed to update GoBrik record.";
                }
            } else {
                error_log('Error preparing GoBrik statement: ' . $gobrik_conn->error);
                echo "Failed to prepare GoBrik update statement.";
            }

            // Close the GoBrik connection
            $gobrik_conn->close();

            // Redirect to the next step
            header("Location: login.php?status=firsttime&id=" . urlencode($buwana_id));
            exit();
        } else {
            error_log('Error preparing statement for updating Buwana user: ' . $buwana_conn->error);
            header("Location: activate-3.php?id=" . urlencode($buwana_id) . "&error=db_update_failed");
            exit();
        }
    }
}

// Close the Buwana database connection after all operations are done
$buwana_conn->close();
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

<?php require_once ("../includes/signup-inc.php");?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="welcome-casandra top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;">
            <div id="status-message"><?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="012-status-heading">your password is set! Now let's get you localized.</span></div>
            <div id="sub-status-message" data-lang-id="013-sub-status-tell" style="font-size:1.3em;padding-top:10px;padding-bottom:10px;">Your new Buwana and GoBrik account is all about local and global ecological action. Please tell us about where you live...</div>
        </div>

        <!-- ACTIVATE 3 FORM -->

       <form id="user-info-form" method="post" action="activate-3.php?id=<?php echo htmlspecialchars($buwana_id); ?>">

    <!-- LOCATION FULL -->
    <div class="form-item">
        <label for="location_full" data-lang-id="011-location-full">What is your local area?</label><br>
        <div class="input-container">
            <input type="text" id="location_full" name="location_full" aria-label="Location Full" required style="padding-left:45px;">
            <div id="loading-spinner" class="spinner" style="display: none;"></div>
        </div>
        <p class="form-caption" data-lang-id="011-location-full-caption">Start typing your local area name, and we'll fill in the rest using the open source, non-corporate OpenStreetMap API.</p>

        <!-- ERRORS -->
        <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
    </div>

    <input type="hidden" id="lat" name="latitude">
    <input type="hidden" id="lon" name="longitude">

    <!-- WATERSHED SEARCH -->
    <div class="form-item" id="watershed-search-section" style="display: none;">
        <label for="watershed_search" data-lang-id="011-watershed-search">Find Your Local Watershed</label><br>
        <div class="input-container">
            <input type="text" id="watershed_search" name="watershed_search" aria-label="Watershed Search" style="padding-left:45px;">
            <div id="watershed-loading-spinner" class="spinner" style="display: none;"></div>
        </div>
        <p class="form-caption" data-lang-id="011-watershed-caption">Type in your local river or watershed to find official names using the HydroShare API.</p>
        <div id="watershed-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
    </div>

    <!-- SUBMIT SECTION -->
    <div id="submit-section" style="text-align:center;margin-top:25px;display:none;" data-lang-id="016-submit-complete-button">
        <input type="submit" id="submit-button" value="Complete Setup" class="submit-button enabled">
    </div>

</form>


    </div>
</div>
</div>
<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php"); ?>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


<script>
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

            // Show the submit button and the watershed search when location is selected
            showSubmitButton();
            showWatershedSearch();
        },
        minLength: 3
    });

    // Function to show the submit button
    function showSubmitButton() {
        $('#submit-section').fadeIn(); // Smoothly shows the submit button section
    }

    // Function to show the watershed search field
    function showWatershedSearch() {
        $('#watershed-search-section').fadeIn(); // Smoothly shows the watershed search field
    }

    // Watershed search function using HydroShare API
    $('#watershed_search').autocomplete({
        source: function(request, response) {
            $("#watershed-loading-spinner").show();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                $.ajax({
                    url: "https://www.hydroshare.org/hsapi/resource/", // Example API endpoint (adjust if needed)
                    dataType: "json",
                    data: {
                        q: request.term, // Query parameter for the HydroShare search
                        format: "json"
                    },
                    success: function(data) {
                        $("#watershed-loading-spinner").hide();
                        response($.map(data.results, function(item) { // Adjust based on HydroShare response structure
                            return {
                                label: item.title, // Assuming the watershed title is in 'title'
                                value: item.title,
                                id: item.id // Additional data can be added here if needed
                            };
                        }));
                    },
                    error: function(xhr, status, error) {
                        $("#watershed-loading-spinner").hide();
                        console.error("HydroShare autocomplete error:", error);
                        response([]);
                    }
                });
            }, 300);
        },
        select: function(event, ui) {
            console.log('Selected watershed:', ui.item); // Debugging line
            // Handle selected watershed if needed in the future
        },
        minLength: 3
    });

    $('#user-info-form').on('submit', function() {
        console.log('Latitude:', $('#lat').val());
        console.log('Longitude:', $('#lon').val());
        // Add any additional submit handling if necessary
    });
});


</script>





</body>
</html>
