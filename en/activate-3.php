<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.4';
$page = 'signup';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

$is_logged_in = false; // Ensure not logged in for this page

// Check if the user is logged in
if (isLoggedIn()) {
    echo "<script>
        alert('Looks like you already have an account and are logged in! Let\'s take you to your dashboard.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}

// Initialize variables
$buwana_id = $_GET['id'] ?? null;  // Correctly initializing buwana_id
$page = 'activate';
$first_name = '';
$pre_community = '';  // Ensure pre_community is initialized

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

// PART 4: Fetch Ecobricker's community from GoBrik database
require_once("../gobrikconn_env.php");

$sql_ecobricker_community = "SELECT community FROM tb_ecobrickers WHERE buwana_id = ?";
$stmt_ecobricker_community = $gobrik_conn->prepare($sql_ecobricker_community);

if ($stmt_ecobricker_community) {
    $stmt_ecobricker_community->bind_param('i', $buwana_id);
    $stmt_ecobricker_community->execute();
    $stmt_ecobricker_community->bind_result($pre_community);
    $stmt_ecobricker_community->fetch();
    $stmt_ecobricker_community->close();
} else {
    die('Error preparing statement for fetching ecobricker community: ' . $gobrik_conn->error);
}

// PART 5: Fetch all communities from the communities_tb table in Buwana database
$communities = [];
$sql_communities = "SELECT com_name FROM communities_tb";
$result_communities = $buwana_conn->query($sql_communities);

if ($result_communities && $result_communities->num_rows > 0) {
    while ($row = $result_communities->fetch_assoc()) {
        $communities[] = $row['com_name'];
    }
}

// PART 6: Handle form submission (if needed)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_location_full = $_POST['location_full'];
    $user_lat = $_POST['latitude'];
    $user_lon = $_POST['longitude'];
    $location_watershed = $_POST['watershed_select']; // Capture the selected watershed
    $selected_community_name = $_POST['community_name']; // Get the selected community name from the form

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

    // Fetch the community_id from communities_tb using the selected community name
    $sql_community = "SELECT com_id FROM communities_tb WHERE com_name = ?";
    $stmt_community = $buwana_conn->prepare($sql_community);

    if ($stmt_community) {
        $stmt_community->bind_param('s', $selected_community_name);
        $stmt_community->execute();
        $stmt_community->bind_result($community_id);
        $stmt_community->fetch();
        $stmt_community->close();
    } else {
        die('Error preparing statement for fetching community info: ' . $buwana_conn->error);
    }

    // Check if the country and community were found in the database
    if (empty($set_country_id) || empty($set_continent_code) || empty($community_id)) {
        echo '<script>alert("Could not determine your country, continent, or community based on your location or community selection. Please refine your details.");</script>';
    } else {
        // Update the Buwana user's continent, country, location, watershed, and community using buwana_id
        $sql_update_buwana = "UPDATE users_tb SET continent_code = ?, country_id = ?, location_full = ?, location_lat = ?, location_long = ?, location_watershed = ?, community_id = ? WHERE buwana_id = ?";
        $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
        if ($stmt_update_buwana) {
            $stmt_update_buwana->bind_param('sissdsii', $set_continent_code, $set_country_id, $user_location_full, $user_lat, $user_lon, $location_watershed, $community_id, $buwana_id);
            $stmt_update_buwana->execute();
            $stmt_update_buwana->close();

            // PART 7: Open GoBrik connection and update tb_ecobrickers to set buwana_activated to 1
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
            <p style="color:green;">✔ <?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="001-password-set"> your password is set!</p>
            <div id="status-message"><span data-lang-id="012-status-heading2"> Now let's get you localized.</span></div>
            <div id="sub-status-message" data-lang-id="013-sub-ecozone" style="font-size:1.3em;padding-top:10px;padding-bottom:10px;">GoBrik is all about ecological action. Please help us determine your ecological zone:  the water shed or riverbasin where you live.</div>
        </div>

        <!-- ACTIVATE 3 FORM -->

      <form id="user-info-form" method="post" action="activate-3.php?id=<?php echo htmlspecialchars($buwana_id); ?>">

    <!-- LOCATION FULL -->
    <div class="form-item">
        <label for="location_full" data-lang-id="011-your-local-area">What is your local area?</label><br>
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
    <div class="form-item" id="watershed-map-section" style="display: none; margin-top:20px;">
        <label for="watershed_select" data-lang-id="011-watershed-select">To what river/stream watershed does your local water flow?</label><br>
        <select id="watershed_select" name="watershed_select" aria-label="Watershed Select" style="width: 100%; padding: 10px;">
            <option value="" disabled selected data-lang-id="011b-select-river">👉 Select river/stream...</option>

        </select>
        <div id="map" style="height: 350px; border-radius: 0px 0px 12px 12px; margin-top: 8px;"></div>
        <p class="form-caption" data-lang-id="012-river-basics" style="margin-top:10px;">ℹ️ <a href="#" onclick="showModalInfo('watershed', '<?php echo $lang; ?>')" class="underline-link">Watersheds</a> provide a great non-political way to localize our users by ecological region!  The map shows rivers and streams around you.  Choose the one to which your water flows.</p>

    </div>

    <!-- COMMUNITY FIELD -->
    <div class="form-item" id="community-section" style="display: none; margin-top:20px;">
        <label for="community_name" data-lang-id="012-community-name">Select and confirm your GoBrik community:</label><br>
        <input type="text" id="community_name" name="community_name" aria-label="Community Name" list="community_list"
               placeholder="Type your community" style="width: 100%; padding: 10px;"
               value="<?php echo htmlspecialchars($pre_community); ?>">
        <datalist id="community_list">
            <?php foreach ($communities as $community) : ?>
                <option value="<?php echo htmlspecialchars($community); ?>" <?php echo ($community === $pre_community) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($community); ?>
                </option>
            <?php endforeach; ?>
        </datalist>
        <p class="form-caption" data-lang-id="012-community-caption">Start typing to see and select a community.  Only GoBrik 2.0 currently available.  Soon you'll be able to add a new community!</p>
    </div>

    <!-- SUBMIT SECTION -->
    <div id="submit-section" style="text-align: center; margin-top: 25px; display: none;" data-lang-id="016-next-button">
        <input type="submit" id="submit-button" value="Next ➡️" class="submit-button enabled">

    </div>

</form>


<!-- Include Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>



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

    // --- SECTION 1: Location Pin Icon Visibility ---

    // Function to toggle the visibility of the pin icon
    // Shows the pin when input is empty or if loading spinner is hidden
    function updatePinIconVisibility() {
        if ($("#location_full").val().trim() === "" || $("#loading-spinner").is(":hidden")) {
            $("#location-pin").show();
        } else {
            $("#location-pin").hide();
        }
    }

    // --- SECTION 2: Location Search using OpenStreetMap Nominatim API ---

    // This initializes the location search input with autocomplete functionality
    // It uses the Nominatim API to fetch location suggestions and update the map when a location is selected
    $("#location_full").autocomplete({
        source: function (request, response) {
            $("#loading-spinner").show(); // Show loading spinner while searching
            $("#location-pin").hide(); // Hide pin icon while typing

            // Debounce to prevent too many requests
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                $.ajax({
                    url: "https://nominatim.openstreetmap.org/search",
                    dataType: "json",
                    headers: {
                        'User-Agent': 'ecobricks.org'
                    },
                    data: {
                        q: request.term, // Term entered by the user
                        format: "json"
                    },
                    success: function (data) {
                        $("#loading-spinner").hide(); // Hide spinner after results
                        updatePinIconVisibility(); // Show pin when results are loaded

                        // Process and map the data for the autocomplete dropdown
                        response($.map(data, function (item) {
                            return {
                                label: item.display_name,
                                value: item.display_name,
                                lat: item.lat,
                                lon: item.lon
                            };
                        }));
                    },
                    error: function () {
                        $("#loading-spinner").hide(); // Hide spinner on error
                        updatePinIconVisibility(); // Show pin on error
                        response([]); // Return no results
                    }
                });
            }, 300); // Delay of 300ms before making the API request
        },
        // Event triggered when a location is selected from the dropdown
        select: function (event, ui) {
            console.log('Selected location:', ui.item);
            $('#lat').val(ui.item.lat);
            $('#lon').val(ui.item.lon);

            // Initialize the map with the selected location's coordinates
            initializeMap(ui.item.lat, ui.item.lon);

            // Show the watershed map and community sections
            $('#watershed-map-section').fadeIn();
            $('#community-section').fadeIn();
            showSubmitButton();

            updatePinIconVisibility(); // Show pin icon after selection
        },
        minLength: 3 // Minimum number of characters required to trigger search
    });

    // --- SECTION 3: Pin Visibility During Input ---

    // Listen for input changes to update the pin icon visibility in real-time
    $("#location_full").on("input", function () {
        updatePinIconVisibility();
    });

    // --- SECTION 4: Submit Button and Page Layout ---

    // Function to display the submit button and adjust the height of the main container
    function showSubmitButton() {
        $('#submit-section').fadeIn(); // Show the submit section

        // Set the height of the main div to 1500px to accommodate the map and other content
        $('#main').css('height', '1500px');
    }

    // --- SECTION 5: Map Initialization using Leaflet ---

    // Function to initialize the map with Leaflet, centered on the selected location
    function initializeMap(lat, lon) {
        if (map) {
            map.remove(); // Remove existing map if any
        }

        // Create a new map centered on the selected coordinates
        map = L.map('map', { preferCanvas: true }).setView([lat, lon], 13);

        // Load OpenStreetMap tiles for the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add a marker at the user's location
        userMarker = L.marker([lat, lon]).addTo(map).bindPopup("Your Location").openPopup();

        // Fix map display issues when loaded in hidden or resized containers
        setTimeout(() => {
            map.invalidateSize(); // Adjust the map size after rendering
        }, 200);

        // Fetch and display nearby rivers or watersheds on the map
        fetchNearbyRivers(lat, lon);
    }

   // --- SECTION 6: Fetching Nearby Rivers using Overpass API ---

// Function to fetch nearby rivers and watersheds using the Overpass API
function fetchNearbyRivers(lat, lon) {
    riverLayerGroup.clearLayers(); // Clear any previously displayed rivers
    $("#watershed_select").empty().append(`<option value="" disabled selected>${Page_Translations['011b-select-river']}</option>`);

    // Overpass API query to search for rivers around the selected location (within a 5km radius)
    const overpassUrl = `https://overpass-api.de/api/interpreter?data=[out:json];(way["waterway"="river"](around:5000,${lat},${lon});relation["waterway"="river"](around:5000,${lat},${lon}););out geom;`;

    $.get(overpassUrl, function (data) {
        let rivers = data.elements;
        let uniqueRivers = new Set(); // Set to keep track of unique river names

        // Loop through fetched river data
        rivers.forEach((river) => {
            let riverName = river.tags.name;

            // Only display rivers with names and filter out unnamed rivers
            if (riverName && !uniqueRivers.has(riverName) && !riverName.toLowerCase().includes("unnamed")) {
                uniqueRivers.add(riverName); // Add river name to the unique set

                // Get the coordinates of the river and add it to the map
                let coordinates = river.geometry.map(point => [point.lat, point.lon]);
                let riverPolyline = L.polyline(coordinates, { color: 'blue' }).addTo(riverLayerGroup).bindPopup(riverName);
                riverLayerGroup.addTo(map); // Add to the map layer

                // Add river name to the dropdown menu
                $("#watershed_select").append(new Option(riverName, riverName));
            }
        });

        if (uniqueRivers.size === 0) {
            // Show message if no rivers or watersheds are found nearby
            $("#watershed_select").append(`<option value="" disabled>${Page_Translations['011d-unseen']}</option>`);
        }

        // Add the additional watershed options every time the dropdown is populated
        $("#watershed_select").append(
            $('<option>', {
                value: "watershed unknown",
                text: Page_Translations['011c-unknown'],
                'data-lang-id': "011c-unknown"
            })
        );
        $("#watershed_select").append(
            $('<option>', {
                value: "watershed unseen",
                text: Page_Translations['011d-unseen'],
                'data-lang-id': "011d-unseen"
            })
        );
        $("#watershed_select").append(
            $('<option>', {
                value: "no watershed",
                text: Page_Translations['011e-no-watershed'],
                'data-lang-id': "011e-no-watershed"
            })
        );

    }).fail(function () {
        // Handle any errors that occur during the API request
        console.error("Failed to fetch data from Overpass API.");
        $("#watershed_select").append(`<option value="" disabled>${Page_Translations['000-field-required-error']}</option>`);
    });
}


    // --- SECTION 7: Form Submission Handling ---

    // Event handler for the form submission
    $('#user-info-form').on('submit', function () {
        // Log the latitude and longitude when the form is submitted
        console.log('Latitude:', $('#lat').val());
        console.log('Longitude:', $('#lon').val());
        // Additional submit handling logic can be added here if needed
    });
});



</script>





</body>
</html>
