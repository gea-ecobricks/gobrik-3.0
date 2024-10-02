<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

startSecureSession();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.392';
$page = 'profile';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Initialize user variables
$first_name = '';
$buwana_id = '';
$country_icon = '';
$watershed_name = '';
$continent_name = ''; // Initialize continent name variable
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function


// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';


  $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $first_name = getUserFirstName($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);

    // Fetch user information including community_id, location_watershed, location_full, latitude, and longitude
    $sql_user_info = "SELECT full_name, first_name, last_name, email, country_id, language_id, birth_date,
                      created_at, last_login, brikcoin_balance, role, account_status, notes,
                      terms_of_service, continent_code, location_watershed, location_full, community_id,
                      location_lat, location_long
                      FROM users_tb WHERE buwana_id = ?";
    $stmt_user_info = $buwana_conn->prepare($sql_user_info);

    if ($stmt_user_info) {
        $stmt_user_info->bind_param('i', $buwana_id);
        $stmt_user_info->execute();
        $stmt_user_info->bind_result($full_name, $first_name, $last_name, $email, $country_id, $language_id,
                                     $birth_date, $created_at, $last_login, $brikcoin_balance, $role, $account_status,
                                     $notes, $terms_of_service, $continent_code, $location_watershed,
                                     $location_full, $community_id, $latitude, $longitude);
        $stmt_user_info->fetch();
        $stmt_user_info->close();
    } else {
        die('Error preparing statement for fetching user info: ' . $buwana_conn->error);
    }





// Fetch active languages from Buwana database
$languages = [];
$sql_languages = "SELECT language_id, language_name_en, language_name_id, language_name_fr, language_name_es, language_active
                  FROM languages_tb
                  WHERE language_active = 1
                  ORDER BY language_name_en";

$result_languages = $buwana_conn->query($sql_languages);

if ($result_languages && $result_languages->num_rows > 0) {
    while ($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}



    // Fetch countries from Buwana database
    $countries = [];
    $sql_countries = "SELECT country_id, country_name FROM countries_tb ORDER BY country_name";
    $result_countries = $buwana_conn->query($sql_countries);
    if ($result_countries && $result_countries->num_rows > 0) {
        while ($row = $result_countries->fetch_assoc()) {
            $countries[] = $row;
        }
    }

    // Fetch continents from Buwana database
    $continents = [];
    $sql_continents = "SELECT continent_code, continent_name_en FROM continents_tb ORDER BY continent_name_en";
    $result_continents = $buwana_conn->query($sql_continents);
    if ($result_continents && $result_continents->num_rows > 0) {
        while ($row = $result_continents->fetch_assoc()) {
            $continents[] = $row;
        }
    }


$sql_communities = "SELECT com_id, com_name FROM communities_tb WHERE country_id = ?";

// Prepare and execute the query to get all communities for the user's country
if ($stmt_communities = $buwana_conn->prepare($sql_communities)) {
    $stmt_communities->bind_param("i", $country_id); // Bind the country_id
    if ($stmt_communities->execute()) {
        $stmt_communities->bind_result($com_id, $com_name);

        // Fetch and store all communities in an array
        while ($stmt_communities->fetch()) {
            $communities[] = ['com_id' => $com_id, 'com_name' => $com_name];
        }
    } else {
        error_log("Error executing statement for fetching communities: " . $stmt_communities->error);
    }
    $stmt_communities->close();
} else {
    error_log("Error preparing statement for fetching communities: " . $buwana_conn->error);
}





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

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>



<?php require_once("../includes/profile-inc.php"); ?>



<div class="splash-title-block"></div>
<div id="splash-bar"></div>
<div id="form-submission-box" style="height:fit-content;margin-top: 90px;">
    <div class="form-container" style="padding-top:20px">
        <div style="text-align:center;width:100%;margin:auto;">
            <h1>⚙️</h1>
            <div id="status-message"><?php echo htmlspecialchars($first_name); ?>'s <span data-lang-id="001-profile-settings-title">Profile Settings</span></div>
            <div id="sub-status-message" data-lang-id="002-review-update-message">Review and update your Buwana account profile here:</div>
            <div id="update-status" style="font-size:1.3em; color:green;padding:10px;margin-top:10px;"></div>
            <div id="update-error" style="font-size:1.3em; color:red;padding:10px;margin-top:10px;"></div>
        </div>

        <div id="buwana-account" style="background:var(--lighter); padding:10px; border-radius:12px; display:flex; flex-wrap: wrap;">
    <div class="left-column" style="font-size:0.9em; flex: 1 1 50%; padding: 10px;">

                <!-- Non-editable Fields -->
                <div class="form-item">
                    <p data-lang-id="004-full-name"><strong>Full Name:</strong></p>
                    <h3> <?php echo htmlspecialchars($full_name); ?></h3>
                </div>

            <!-- Email -->
                <div class="form-item">
                    <p data-lang-id="005-email"><strong>Email:</strong></p>
                    <p><?php echo htmlspecialchars($email); ?></p>
                    <!--<label for="email" data-lang-id="014-email">Email:</label>
                    <input type="email" value="<?php echo htmlspecialchars($email); ?>" readonly disabled>
                -->
                </div>
                <div class="form-item">
                    <p data-lang-id="005-account-created-at"><strong>Account Created At:</strong></p>
                    <p><?php echo htmlspecialchars($created_at); ?></p>
                </div>
                <div class="form-item">
                    <p data-lang-id="006-last-login"><strong>Last Login:</strong></p>
                    <p><?php echo htmlspecialchars($last_login); ?></p>
                </div>
                <div class="form-item">
                    <p data-lang-id="007-brikcoin-balance"><strong>Brikcoin Balance:</strong></p>
                    <p><?php echo htmlspecialchars(number_format($brikcoin_balance, 1)); ?> ß
</p>
                </div>
                <div class="form-item">
                    <p data-lang-id="008-roles"><strong>Role(s):</strong></p>
                    <p> <?php echo htmlspecialchars($role); ?></p>
                </div>
                <div class="form-item">
                    <p data-lang-id="009-account-status"><strong>Account Status:</strong></p>
                    <p><?php echo htmlspecialchars($account_status); ?></p>
                </div>
                <div class="form-item">
                    <p data-lang-id="010-account-notes"><strong>Account Notes:</strong> <?php echo htmlspecialchars($notes); ?></p>
                </div>
                <div class="form-item">
                    <p data-lang-id="011-agreed-terms"><strong>Agreed to Terms of Service:</strong> <?php echo $terms_of_service ? 'Yes' : 'No'; ?></p>
                </div>
                <div class="form-item">
                        <p data-lang-id="011a-latitude"><strong>Latitude:</strong> <?php echo htmlspecialchars($latitude); ?></p>
                    </div>
                <div class="form-item">
                        <p data-lang-id="011b-longitude"><strong>Latitude:</strong> <?php echo htmlspecialchars($longitude); ?></p>
                    </div>
                <div class="form-item">
                        <p data-lang-id="011b-longitude"><strong>Buwana ID:</strong> <?php echo htmlspecialchars($buwana_id); ?></p>
                    </div>

            </div>



            <div class="right-column" style="flex: 1 1 50%; padding: 10px;">
                <!-- Editable Fields -->



<!-- Profile Update Form -->
<form action="profile_update_process.php" method="POST">

    <!-- First Name -->
    <div class="form-item">
        <label for="first_name" data-lang-id="012-first-name">First Name:</label>
        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
    </div>

    <!-- Last Name -->
    <div class="form-item">
        <label for="last_name" data-lang-id="013-last-name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
    </div>

    <!-- Continent -->
    <div class="form-item">
        <label for="continent_code" data-lang-id="021-continent">Continent:</label>
        <select name="continent_code" id="continent_code">
            <option value="" data-lang-id="022-select-continent">Select Continent</option>
            <?php foreach ($continents as $continent): ?>
                <option value="<?php echo $continent['continent_code']; ?>" <?php if ($continent['continent_code'] == $continent_code) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($continent['continent_name_en']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Country -->
    <div class="form-item">
        <label for="country_id" data-lang-id="015-country">Country:</label>
        <select name="country_id" id="country_id">
            <option value="" data-lang-id="016-select-country">Select Country</option>
            <?php foreach ($countries as $country): ?>
                <option value="<?php echo $country['country_id']; ?>" <?php if ($country['country_id'] == $country_id) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($country['country_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Community -->
    <div class="form-item">
        <label for="community_id" data-lang-id="025-community">Your community:</label>
        <select name="community_id" id="community_id">
            <option value="" data-lang-id="026-select-community">Select Community</option>

            <?php foreach ($communities as $community): ?>
                <option value="<?php echo htmlspecialchars($community['com_id']); ?>" <?php if ($community['com_id'] == $community_id) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($community['com_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="form-caption" data-lang-id="011-location-full-caption">Your GoBrik community (migrated from GoBrik 2.0, soon you'll be able to add new communities)</p>
    </div>

    <!-- Hidden latitude and longitude fields -->
<input type="hidden" id="lat" name="latitude">
<input type="hidden" id="lon" name="longitude">

<!-- Location Full -->
<div class="form-item">
    <label for="location_full" data-lang-id="011X-location-full">Your local area:</label><br>
    <div class="input-container">
        <input type="text" id="location_full" name="location_full" aria-label="Location Full"
               value="<?php echo $location_full; ?>" required style="padding-left:45px;">
        <div id="loading-spinner" class="spinner" style="display: none;"></div>
        <div id="location-pin" class="pin-icon">📍</div>
    </div>
    <p class="form-caption" data-lang-id="011-location-full-caption">To edit, start typing your local area name, and we'll fill in the rest using the open source, non-corporate OpenStreetMap API.</p>
    <div id="location-error-required" class="form-field-error" data-lang-id="000-field-required-error">This field is required.</div>
</div>

<!-- Location Watershed -->
<div class="form-item">
    <label for="location_watershed" data-lang-id="011-watershed-location">Your local river:</label><br>
    <div class="input-container">
        <input type="text" id="location_watershed" name="location_watershed"
               value="<?php echo $location_watershed; ?>" aria-label="Location Watershed" style="width: 100%; padding: 10px;" >
        <div id="loading-spinner-watershed" class="spinner" style="display: none;"></div>
        <div id="watershed-pin" class="pin-icon">💦</div>
    </div>
    <p class="form-caption">💚 Rivers and their basins provide a great non-political way to localize our users by ecological region!</p>
</div>



    <!-- Preferred Language -->
    <div class="form-item">
        <label for="language_id" data-lang-id="017-preferred-language">Preferred Language:</label>
        <select name="language_id" id="language_id">
            <option value="" data-lang-id="018-select-language">Select Language</option>
            <?php foreach ($languages as $language): ?>
                <option value="<?php echo htmlspecialchars($language['language_id']); ?>" <?php if ($language['language_id'] == $language_id) echo 'selected'; ?>>
                    <?php
                    switch (strtolower($lang)) {
                        case 'id':
                            echo htmlspecialchars($language['language_name_id']);
                            break;
                        case 'fr':
                            echo htmlspecialchars($language['language_name_fr']);
                            break;
                        case 'es':
                            echo htmlspecialchars($language['language_name_es']);
                            break;
                        case 'en':
                        default:
                            echo htmlspecialchars($language['language_name_en']);
                            break;
                    }
                    ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Birth Date -->
    <div class="form-item">
        <label for="birth_date" data-lang-id="019-birth-date">Birth Date:</label>
        <input type="date" name="birth_date" id="birth_date" value="<?php echo htmlspecialchars($birth_date); ?>">
    </div>

    <!-- Save and Update Button -->
    <div style="margin:auto;text-align: center;margin-top:30px;">
        <button type="submit" class="submit-button enabled" aria-label="Save and update" data-lang-id="020-submit-button">Save and Update</button>
    </div>

</form>







        </div>

    <!--EARTHEN ACCOUNT DB CHECK -->
<div class="form-container" style="padding-top:20px">
    <h2 data-lang-id="021-earthen-status-title">Earthen Newsletter Subscription Status</h2>
    <p><span data-lang-id="022-check-to-see">Check to see if your</span> <?php echo htmlspecialchars($email); ?> <span data-lang-id="023-is-subscribed">is subscribed to the Earthen newsletter</span></p>
    <div id="earthen-status-message" style="display:none;"></div>
    <button id="check-earthen-status-button" class="submit-button enabled" data-lang-id="024-check-earthen-button">Check Earthen Status</button>

    <!-- Status Yes -->
    <div id="earthen-status-yes" style="display:none;">
        <p data-lang-id="025-yes-subscribed" style="color:green">Yes! You're subscribed to the following newsletters:</p>
        <ul id="newsletter-list"></ul>
        <button id="unsubscribe-button" class="submit-button delete">Unsubscribe</button>
        <button id="manage-subscription-button" class="submit-button enabled">↗️ Manage Subscription</button>
    </div>

    <!-- Status No -->
    <div id="earthen-status-no" style="display:none;">
        <p data-lang-id="026-not-subscribed">You're not yet subscribed.</p>
        <a href="https://earthen.io/#register" target="_blank" class="enabled" style="padding:6px;" data-lang-id="027-subscribe-button">↗️ Subscribe on Earthen</a>
    </div>
</div>



<!-- DELETE ACCOUNT -->
<div class="form-container" style="padding-top:20px; margin-top: 20px; border-top: 1px solid #ddd;">
    <h2 data-lang-id="028-delete-heading">Delete Your Account</h2>
    <p data-lang-id="029-delete-warning">Warning: Deleting your account will permanently remove all your data and cannot be undone.</p>

    <form id="delete-account-form" method="post">
    <button type="button" onclick="confirmDeletion('<?php echo htmlspecialchars($buwana_id); ?>', '<?php echo htmlspecialchars($lang); ?>')" class="submit-button delete" aria-label="Delete Account" data-lang-id="030-delete-my-account-button">Delete My Account</button>

</form>



</div>


    </div> <!-- close form-container -->
</div> <!-- close form-submission-box -->
</div>

</div> <!--closes main-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2024.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Function to handle the update status
    function catchUpdateReport(status, message = '') {
        const updateStatusDiv = document.getElementById('update-status');

        if (status === 'succeeded') {
            updateStatusDiv.innerHTML = "👍 Your user profile was updated!";
            scrollToTop();
        } else if (status === 'failed') {
            updateStatusDiv.innerHTML = "🤔 Huh... something went wrong with the update: " + message;
            scrollToTop();
        } else {
            updateStatusDiv.innerHTML = "❓ Unexpected status: " + status;
            scrollToTop();
        }
    }

    // Function to smoothly scroll to the top of the page
    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Handle the form submission using AJAX
    document.querySelector('form').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(this);

        fetch('profile_update_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            if (data.status === 'succeeded') {
                catchUpdateReport('succeeded');
            } else {
                const errorMessage = data.message || 'Unknown error occurred.';
                catchUpdateReport('failed', errorMessage);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            catchUpdateReport('failed', 'Error submitting the form: ' + error.message);
        });
    });

    // Check for status message from URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status) {
        catchUpdateReport(status);
    }
});




</script>


<script>

// CHECK EARTHEN SUBSCRIPTION
document.getElementById('check-earthen-status-button').addEventListener('click', function() {
    var email = '<?php echo addslashes($email); ?>';
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'check_earthen_status.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            console.log('Server response:', xhr.responseText); // Log the full server response
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);

                    var statusYes = document.getElementById('earthen-status-yes');
                    var statusNo = document.getElementById('earthen-status-no');
                    var newsletterList = document.getElementById('newsletter-list');
                    var checkButton = document.getElementById('check-earthen-status-button');

                    if (response.status === 'success') {
                        if (response.registered) {
                            // Hide the check status button and show the subscribed status
                            checkButton.style.display = 'none';
                            statusYes.style.display = 'block';

                            // Clear any existing list items
                            newsletterList.innerHTML = '';

                            // Store the member ID for unsubscribing
                            window.memberId = response.member_id;

                            // Add the newsletters to the list
                            if (response.newsletters && response.newsletters.length > 0) {
                                response.newsletters.forEach(function(newsletter) {
                                    var li = document.createElement('li');
                                    li.textContent = newsletter; // Set text content correctly
                                    newsletterList.appendChild(li);
                                });
                            } else {
                                newsletterList.innerHTML = '<li>No newsletters found.</li>';
                            }
                        } else {
                            // Hide the check status button and show the not subscribed status
                            checkButton.style.display = 'none';
                            statusNo.style.display = 'block';
                        }
                    } else {
                        console.error(response.message);
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                }
            } else {
                console.error('Failed to fetch subscription status. HTTP Status:', xhr.status);
            }
        }
    };

    xhr.send('email=' + encodeURIComponent(email));
});

// Event listener for the unsubscribe button click
document.getElementById('unsubscribe-button').addEventListener('click', unsubscribe);

// Function to handle the unsubscribe button click
function unsubscribe() {
    if (confirm("Are you sure you want to do this? We'll permanently unsubscribe you from all Earthen newsletters. Note, this will not affect your GoBrik or Buwana accounts.")) {
        var email = '<?php echo addslashes($email); ?>'; // Get email from PHP
        var memberId = window.memberId; // Use the stored member ID
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'check_earthen_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                console.log('Unsubscribe response:', xhr.responseText); // Log the server response

                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            alert(response.message);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                    }
                } else {
                    alert('Failed to unsubscribe. Please try again.');
                }
            }
        };

        // Send email and unsubscribe parameters to the server
        xhr.send('email=' + encodeURIComponent(email) + '&unsubscribe=true&member_id=' + encodeURIComponent(memberId));
    }
}

// Retrieve buwana_id dynamically (e.g., from a hidden field or data attribute)
const buwana_id = <?php echo json_encode($buwana_id); ?>;

// Event listener for the manage subscription button
document.getElementById('manage-subscription-button').addEventListener('click', function() {
    const url = 'activate-subscriptions.php?id=' + encodeURIComponent(buwana_id) + '&type=update';
    window.open(url, '_blank'); // Open the generated URL in a new tab
});




</script>



<script>


document.addEventListener('DOMContentLoaded', function () {
    // Function to handle the update status (success)
    function handleSuccessMessage(status) {
        const updateStatusDiv = document.getElementById('update-status');
        const updateErrorDiv = document.getElementById('update-error');

        // Clear any previous error messages
        updateErrorDiv.innerHTML = "";

        if (status === 'succeeded') {
            updateStatusDiv.innerHTML = "👍 Your user profile was updated!";
            scrollToTop();
        } else {
            updateStatusDiv.innerHTML = "❓ Unexpected status: " + status;
            scrollToTop();
        }
    }

    // Function to handle the update error (failure)
    function handleErrorMessage(message) {
        const updateStatusDiv = document.getElementById('update-status');
        const updateErrorDiv = document.getElementById('update-error');

        // Clear any previous success messages
        updateStatusDiv.innerHTML = "";

        updateErrorDiv.innerHTML = "🤔 Something went wrong with the update: " + message;
        scrollToTop();
    }

    // Function to smoothly scroll to the top of the page
    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Handle the form submission using AJAX
    document.querySelector('form').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(this);

        fetch('profile_update_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            if (data.status === 'succeeded') {
                handleSuccessMessage('succeeded');
            } else {
                const errorMessage = data.message || 'Unknown error occurred.';
                handleErrorMessage(errorMessage);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            handleErrorMessage('Error submitting the form: ' + error.message);
        });
    });

    // Check for status message from URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status === 'succeeded') {
        handleSuccessMessage(status);
    }
});

</script>

<script>
$(function () {
    let debounceTimer;

    // SECTION 1: Searching for the user's local area
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
            // Set the selected location in the input field
            $('#location_full').val(ui.item.value);
            // Set latitude and longitude
            $('#lat').val(ui.item.lat);
            $('#lon').val(ui.item.lon);

            // Enable the location_watershed field but do not populate yet
            $('#location_watershed').prop('disabled', false);

            return false; // Prevent default behavior
        },
        minLength: 3
    });

    // SECTION 2: Fetch nearby rivers when the user focuses on the location_watershed field
    $("#location_watershed").on('focus', function () {
        // Get latitude and longitude from the previously selected location
        const lat = $('#lat').val();
        const lon = $('#lon').val();

        // Check if latitude and longitude are available
        if (!lat || !lon) {
            console.error('No location selected to find nearby rivers.');
            return;
        }

        // Show loading spinner for the watershed
        $("#loading-spinner-watershed").show();
        $("#watershed-pin").hide(); // Hide the river pin icon when searching

        // Call Overpass API to find rivers around the selected location
        const overpassUrl = `https://overpass-api.de/api/interpreter?data=[out:json];(way["waterway"="river"](around:5000,${lat},${lon});relation["waterway"="river"](around:5000,${lat},${lon}););out body;`;

        // Fetch nearby rivers using Overpass API
        $.get(overpassUrl, function (data) {
            $("#loading-spinner-watershed").hide();
            $("#watershed-pin").show();

            let rivers = data.elements;
            let uniqueRivers = new Set();

            // Clear previous options and add a default message
            $('#location_watershed').empty().append('<option value="" disabled selected>Select your nearest river</option>');

            // Iterate through rivers and add up to 5 unique names to the dropdown
            rivers.forEach(function (river) {
                let riverName = river.tags.name;

                if (riverName && !uniqueRivers.has(riverName) && !riverName.toLowerCase().includes("unnamed") && uniqueRivers.size < 5) {
                    uniqueRivers.add(riverName);
                    $('#location_watershed').append(new Option(riverName, riverName));
                }
            });

            // If no rivers are found, show a message in the dropdown
            if (uniqueRivers.size === 0) {
                $('#location_watershed').append('<option value="" disabled>No rivers found nearby</option>');
            }
        }).fail(function () {
            $("#loading-spinner-watershed").hide();
            console.error("Failed to fetch data from Overpass API.");
            $('#location_watershed').append('<option value="" disabled>Error fetching rivers</option>');
        });
    });
});



</script>

</body>
</html>
