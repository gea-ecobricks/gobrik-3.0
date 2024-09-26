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
$version = '0.691';
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
    $location_watershed = $_POST['watershed_select']; // Capture the selected watershed

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
        // Update the Buwana user's continent, country, location, and watershed using buwana_id
        $sql_update_buwana = "UPDATE users_tb SET continent_code = ?, country_id = ?, location_full = ?, location_lat = ?, location_long = ?, location_watershed = ? WHERE buwana_id = ?";
        $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
        if ($stmt_update_buwana) {
            $stmt_update_buwana->bind_param('sissdsi', $set_continent_code, $set_country_id, $user_location_full, $user_lat, $user_lon, $location_watershed, $buwana_id);
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
            header("Location: activate-subscriptions.php?id=" . urlencode($buwana_id));
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
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
            <p style="color:green;">✔ <?php echo htmlspecialchars($first_name); ?>, your password is set!</p>
            <div id="status-message"><span data-lang-id="012-status-heading2"> Now let's get you localized.</span></div>
            <div id="sub-status-message" data-lang-id="013-sub-status-tell" style="font-size:1.3em;padding-top:10px;padding-bottom:10px;">GoBrik is all about ecological action. Please help us determine your ecological zone:  the water shed or riverbasin where you live.</div>
        </div>

        <!-- ACTIVATE 3 FORM -->

       <form id="user-info-form" method="post" action="activate-3.php?id=<?php echo htmlspecialchars($buwana_id); ?>">

    <!-- LOCATION FULL -->
    <div class="form-item">
        <label for="location_full" data-lang-id="011-location-full">What is your local area?</label><br>
        <div class="input-container">
            <input type="text" id="location_full" name="location_full" aria-label="Location Full" required style="padding-left:45px;">
            <div id="loading-spinner" class="spinner" style="display: none;"></div>
            <div id="location-pin" class="pin-icon">📍</div>
        </div>
        <p class="form-caption" data-lang-id="011-location-full-caption">Start typing your local area name, and we'll fill in the rest using the open source, non-corporate OpenStreetMap API.</p>
        <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
    </div>

    <input type="hidden" id="lat" name="latitude">
    <input type="hidden" id="lon" name="longitude">

    <!-- MAP AND WATERSHED SEARCH SECTION -->
    <div class="form-item" id="watershed-map-section" style="display: none;">
            <label for="watershed_select" data-lang-id="011-watershed-select">Select Your Local River or Watershed</label><br>
            <select id="watershed_select" name="watershed_select" aria-label="Watershed Select" style="width: 100%; padding: 10px;">
                <option value="" disabled selected>Select a river or watershed</option>
            </select>
            <p class="caption">💚 River basins provide a new non-polical way to localize our users by ecological region!</p>
            <div id="map" style="height: 350px;border-radius: 15px; margin-top:10px;"></div>
    </div>


    <!-- SUBMIT SECTION -->
    <div id="submit-section" style="text-align:center;margin-top:25px;display:none;" data-lang-id="016X-submit-complete-button">
        <input type="submit" id="submit-button" value="Next" class="submit-button enabled">
    </div>
    <p>Can't find your watershed? No worries! We're still working on this functionality</p>

</form>

<!-- Include Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>


sakdjlfjklasdfkjl

    </div>
</div>
</div>
<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php"); ?>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


<script>
$(function () {
    let debounceTimer;
    let map, userMarker;
    let riverLayerGroup = L.layerGroup();

    // Show pin icon when the input is empty and when it's filled
    function updatePinIconVisibility() {
        if ($("#location_full").val().trim() === "" || $("#loading-spinner").is(":hidden")) {
            $("#location-pin").show();
        } else {
            $("#location-pin").hide();
        }
    }

    // Initialize location search using OpenStreetMap Nominatim API
    $("#location_full").autocomplete({
        source: function (request, response) {
            $("#loading-spinner").show();
            $("#location-pin").hide(); // Hide the pin icon when typing starts

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
                    success: function (data) {
                        $("#loading-spinner").hide();
                        updatePinIconVisibility(); // Show the pin when data has loaded

                        response($.map(data, function (item) {
                            return {
                                label: item.display_name,
                                value: item.display_name,
                                lat: item.lat,
                                lon: item.lon
                            };
                        }));
                    },
                    error: function (xhr, status, error) {
                        $("#loading-spinner").hide();
                        updatePinIconVisibility(); // Show the pin when an error occurs
                        console.error("Autocomplete error:", error);
                        response([]);
                    }
                });
            }, 300);
        },
        select: function (event, ui) {
            console.log('Selected location:', ui.item);
            $('#lat').val(ui.item.lat);
            $('#lon').val(ui.item.lon);

            // Show the map and watershed search section when a location is selected
            initializeMap(ui.item.lat, ui.item.lon);
            $('#watershed-map-section').fadeIn();
            showSubmitButton();

            updatePinIconVisibility(); // Show pin icon after selection
        },
        minLength: 3
    });

    // Show or hide the pin icon based on input value changes
    $("#location_full").on("input", function () {
        updatePinIconVisibility();
    });

    // Function to show the submit button
    function showSubmitButton() {
        $('#submit-section').fadeIn();
    }

    // Initialize the Leaflet map centered on the selected location
    function initializeMap(lat, lon) {
        if (map) {
            map.remove();
        }
        map = L.map('map', { preferCanvas: true }).setView([lat, lon], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add user location marker
        userMarker = L.marker([lat, lon]).addTo(map).bindPopup("Your Location").openPopup();

        // Fix map display issue when loaded in a hidden or resized container
        setTimeout(() => {
            map.invalidateSize();
        }, 200); // Delay to ensure the map fully loads and resizes correctly

        // Fetch nearby rivers or watersheds using Overpass API
        fetchNearbyRivers(lat, lon);
    }

    // Function to fetch nearby rivers or watersheds using Overpass API
    function fetchNearbyRivers(lat, lon) {
        riverLayerGroup.clearLayers(); // Clear previous rivers
        $("#watershed_select").empty().append('<option value="" disabled selected>Select a river or watershed</option>');

        const overpassUrl = `https://overpass-api.de/api/interpreter?data=[out:json];(way["waterway"="river"](around:5000,${lat},${lon});relation["waterway"="river"](around:5000,${lat},${lon}););out geom;`;

        $.get(overpassUrl, function (data) {
            let rivers = data.elements;
            let uniqueRivers = new Set(); // Set to keep track of unique river names

            rivers.forEach((river, index) => {
                let riverName = river.tags.name;

                // Filter out unnamed rivers from the dropdown
                if (riverName && !uniqueRivers.has(riverName) && !riverName.toLowerCase().includes("unnamed")) {
                    uniqueRivers.add(riverName); // Add river name to the set

                    let coordinates = river.geometry.map(point => [point.lat, point.lon]);
                    // Add river to the map
                    let riverPolyline = L.polyline(coordinates, { color: 'blue' }).addTo(riverLayerGroup).bindPopup(riverName);
                    riverLayerGroup.addTo(map);

                    // Add river to the select dropdown
                    $("#watershed_select").append(new Option(riverName, riverName));
                }
            });

            if (uniqueRivers.size === 0) {
                $("#watershed_select").append('<option value="" disabled>No rivers or watersheds found nearby</option>');
            }
        }).fail(function () {
            console.error("Failed to fetch data from Overpass API.");
            $("#watershed_select").append('<option value="" disabled>Error fetching rivers</option>');
        });
    }

    $('#user-info-form').on('submit', function () {
        console.log('Latitude:', $('#lat').val());
        console.log('Longitude:', $('#lon').val());
        // Additional submit handling if needed
    });
});


</script>





</body>
</html>
