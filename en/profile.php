<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

startSecureSession();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.386';
$page = 'profile';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Initialize user variables
$first_name = '';
$buwana_id = '';
$country_icon = '';
$watershed_id = ''; // Initialize watershed_id to prevent undefined variable error
$watershed_name = '';
$continent_name = ''; // Initialize continent name variable
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function

// earthen_helper.php

function checkEarthenEmailStatus($email_addr) {
    // Prepare and encode the email address for use in the API URL
    $email_encoded = urlencode($email_addr);
    $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/members/?filter=email:$email_encoded";

    // Split API Key into ID and Secret for JWT generation
    $apiKey = '66db68b5cff59f045598dbc3:5c82d570631831f277b1a9b4e5840703e73a68e948812b2277a0bc11c12c973f';
    list($id, $secret) = explode(':', $apiKey);

    // Prepare the header and payload for the JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'kid' => $id]);
    $now = time();
    $payload = json_encode([
        'iat' => $now,
        'exp' => $now + 300, // Token valid for 5 minutes
        'aud' => '/v3/admin/' // Corrected audience value to match the expected pattern
    ]);

    // Base64Url Encode function
    function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Encode Header and Payload
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // Create the Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, hex2bin($secret), true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Create the JWT token
    $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

    // Set up the cURL request to the Ghost Admin API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ghost_api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Ghost ' . $jwt,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true); // Use GET to fetch data

    // Execute the cURL session
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        echo json_encode(['status' => 'error', 'message' => 'Curl error: ' . curl_error($ch)]);
        exit();
    }

    if ($http_code >= 200 && $http_code < 300) {
        // Successful response, parse the JSON data
        $response_data = json_decode($response, true);

        // Check if members are found
        $registered = 0; // Default to not registered
        if ($response_data && isset($response_data['members']) && is_array($response_data['members']) && count($response_data['members']) > 0) {
            $registered = 1; // Member with the given email exists
            echo json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is subscribed.']);
        } else {
            echo json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is not subscribed.']);
        }
    } else {
        // Handle error
        error_log('HTTP status ' . $http_code . ': ' . $response);
        echo json_encode(['status' => 'error', 'message' => 'API call to Earthen.io failed with HTTP code: ' . $http_code]);
    }

    // Close the cURL session
    curl_close($ch);
}






// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch user information using buwana_id from the Buwana database
    $country_icon = getUserContinent($buwana_conn, $buwana_id);
    $watershed_name = getWatershedName($buwana_conn, $buwana_id, $lang); // Corrected to include the $lang parameter

    // Fetch Full user information including watershed_id using buwana_id from the Buwana database
    $sql_user_info = "SELECT full_name, first_name, last_name, email, country_id, languages_id, birth_date, created_at, last_login, brikcoin_balance, role, account_status, notes, terms_of_service, continent_code, watershed_id FROM users_tb WHERE buwana_id = ?";
    $stmt_user_info = $buwana_conn->prepare($sql_user_info);

    if ($stmt_user_info) {
        $stmt_user_info->bind_param('i', $buwana_id);
        $stmt_user_info->execute();
        $stmt_user_info->bind_result($full_name, $first_name, $last_name, $email, $country_id, $languages_id, $birth_date, $created_at, $last_login, $brikcoin_balance, $role, $account_status, $notes, $terms_of_service, $continent_code, $watershed_id);
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


    // Fetch watersheds from the Buwana database
    $watersheds = [];
    $sql_watersheds = "SELECT watershed_id, watershed_name FROM watersheds_tb ORDER BY watershed_name";
    $result_watersheds = $buwana_conn->query($sql_watersheds);
    if ($result_watersheds && $result_watersheds->num_rows > 0) {
        while ($row = $result_watersheds->fetch_assoc()) {
            $watersheds[] = $row;
        }
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
            <div id="status-message" data-lang-id="001-profile-settings-title"><?php echo htmlspecialchars($first_name); ?>'s Profile Settings</div>
            <div id="sub-status-message" data-lang-id="002-review-update-message">Review and update your Buwana account profile here:</div>
            <div id="update-status" style="font-size:1.3em; color:green;padding:10px;margin-top:10px;"></div>
        </div>

        <div id="buwana-account" style="background:var(--lighter); padding:10px; border-radius:12px; display:flex; flex-wrap: wrap;">
    <div class="left-column" style="font-size:0.9em; flex: 1 1 50%; padding: 10px;">

                <!-- Non-editable Fields -->
                <div class="form-item">
                    <p data-lang-id="004-full-name"><strong>Full Name:</strong></p>
                    <h3> <?php echo htmlspecialchars($full_name); ?></h3>
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
                    <p><?php echo htmlspecialchars($brikcoin_balance); ?></p>
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
            </div>

            <div class="right-column" style="flex: 1 1 50%; padding: 10px;">
                <!-- Editable Fields -->
               <form method="post" action="update_profile.php">
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

    <!-- Email -->
    <div class="form-item">
        <label for="email" data-lang-id="014-email">Email:</label>
        <input type="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
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

    <!-- Watershed -->
    <div class="form-item">
        <label for="watershed_id" data-lang-id="023-watershed">Watershed:</label>
        <select name="watershed_id" id="watershed_id">
            <option value="" data-lang-id="024-select-watershed">Select Watershed</option>
            <?php foreach ($watersheds as $watershed): ?>
                <option value="<?php echo $watershed['watershed_id']; ?>" <?php if ($watershed['watershed_id'] == $watershed_id) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($watershed['watershed_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Preferred Language -->
 <div class="form-item">
    <label for="language_id" data-lang-id="017-preferred-language">Preferred Language:</label>
    <select name="language_id" id="language_id">
        <option value="" data-lang-id="018-select-language">Select Language</option>
        <?php foreach ($languages as $language): ?>
            <option value="<?php echo htmlspecialchars($language['language_id']); ?>" <?php if ($language['language_id'] == $languages_id) echo 'selected'; ?>>
                <?php
                // Display the language name based on the user's selected language ($lang)
                switch (strtolower($lang)) {
                    case 'id':
                        echo htmlspecialchars($language['language_name_id']); // Indonesian
                        break;
                    case 'fr':
                        echo htmlspecialchars($language['language_name_fr']); // French
                        break;
                    case 'es':
                        echo htmlspecialchars($language['language_name_es']); // Spanish
                        break;
                    case 'en':
                    default:
                        echo htmlspecialchars($language['language_name_en']); // Default to English
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

    <!--earthen db check-->
<div class="form-container" style="padding-top:20px">
    <h2>Earthen Newsletter Subscription Status</h2>
    <p>Check to see if your <?php echo htmlspecialchars($email); ?> is subscribed to the Earthen newsletter</p>
    <p id="earthen-status-message" style="display:none;"></p>
    <button id="check-earthen-status">Check Earthen Status</button>
</div>


<div class="form-container" style="padding-top:20px; margin-top: 20px; border-top: 1px solid #ddd;">
    <h2>Delete Your Account</h2>
    <p>Warning: Deleting your account will permanently remove all your data and cannot be undone.</p>

    <form id="delete-account-form" method="post" action="delete_account.php" onsubmit="return confirmDelete();">
        <input type="hidden" name="buwana_id" value="<?php echo htmlspecialchars($buwana_id); ?>" />

        <div style="text-align: center; margin-top: 20px;">
            <button type="submit" class="delete-button">Delete My Account</button>
        </div>
    </form>
</div>

        <!-- Other Dashboard Buttons -->
        <div style="display:flex;flex-flow:row;width:100%;justify-content:center; margin-top:50px;">
            <a href="newest-briks.php"><button id="newest-ecobricks-button" data-lang-id="023-newest-ecobricks" style="padding:5px;margin:5px;background:grey;border-radius:5px;color:var(--text-color);cursor:pointer;border:none;">📅 Newest Ecobricks</button></a>
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
    function catchUpdateReport(status) {
        const updateStatusDiv = document.getElementById('update-status');

        if (status === 'succeeded') {
            updateStatusDiv.innerHTML = "👍 Your user profile was updated!";
            scrollToTop();
        } else if (status === 'failed') {
            updateStatusDiv.innerHTML = "🤔 Something went wrong with the update.";
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

        fetch('update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                catchUpdateReport(data.status);
            } else {
                catchUpdateReport('failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            catchUpdateReport('failed');
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
function confirmDeletion() {
    if (confirm("Are you certain you wish to delete your account? This cannot be undone.")) {
        if (confirm("Ok. We will delete your account! Note that this does not affect ecobrick data that has been permanently archived in the brikchain. Note that currently our Earthen newsletter is separate from GoBrik-- which has its own easy unsubscribe mechanism.")) {
            document.getElementById('delete-account-form').submit();
        }
    }
}
</script>

<!--Earthe DB check-->

<script>
document.getElementById('check-earthen-status-button').addEventListener('click', function() {
    var email = '<?php echo $email_addr; ?>'; // Use the user's email address
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'check_earthen_subscription.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                var messageElement = document.getElementById('earthen-status-message');

                if (response.status === 'success') {
                    if (response.registered) {
                        messageElement.textContent = "Yes! You're subscribed.";
                        // Show buttons to unsubscribe or update
                    } else {
                        messageElement.textContent = "You're not yet subscribed.";
                        // Show button to subscribe
                    }
                } else {
                    messageElement.textContent = response.message;
                }
                messageElement.style.display = 'block';
            } catch (e) {
                console.error('Error parsing JSON:', e);
            }
        }
    };

    xhr.send('email=' + encodeURIComponent(email));
});

</script>

</body>
</html>
