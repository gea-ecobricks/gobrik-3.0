<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (isLoggedIn()) {
    echo "<script>
        alert('Looks like you already have an account and are logged in! Let\'s take you to your dashboard.');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = false; // Ensure not logged in for this page

// Set page variables
$page = 'activate-subscriptions';
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.771';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$response = ['success' => false];
$buwana_id = $_GET['id'] ?? null;

// Initialize user variables
$credential_type = '';
$credential_key = '';
$first_name = '';
$account_status = '';
$country_icon = '';

// Include database connection
include '../buwanaconn_env.php';

// Look up user information if buwana_id is provided
if ($buwana_id) {
    $sql_lookup_credential = "SELECT credential_type, credential_key FROM credentials_tb WHERE buwana_id = ?";
    $stmt_lookup_credential = $buwana_conn->prepare($sql_lookup_credential);
    if ($stmt_lookup_credential) {
        $stmt_lookup_credential->bind_param("i", $buwana_id);
        $stmt_lookup_credential->execute();
        $stmt_lookup_credential->bind_result($credential_type, $credential_key);
        $stmt_lookup_credential->fetch();
        $stmt_lookup_credential->close();
    } else {
        $response['error'] = 'db_error';
    }

    $sql_lookup_user = "SELECT first_name, account_status FROM users_tb WHERE buwana_id = ?";
    $stmt_lookup_user = $buwana_conn->prepare($sql_lookup_user);
    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $buwana_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name, $account_status);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        $response['error'] = 'db_error';
    }

    $credential_type = htmlspecialchars($credential_type);
    $first_name = htmlspecialchars($first_name);

    if ($account_status !== 'name set only') {
        $response['error'] = 'account_status';
    }
}


// Function to perform base64 URL encoding
function base64UrlEncode($data) {
    // Base64 encode the data, then remove padding and replace characters to make it URL-safe
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

// Gather subscription data
grabActiveEarthenSubs();

//Check subscription status
$is_subscribed = false;
$earthen_subscriptions = ''; // To store newsletter names if subscribed
if (!empty($credential_key)) {
    ob_start(); // Start output buffering to capture the JSON response
    checkEarthenEmailStatus($credential_key); // Pass credential_key as $email
    $api_response = ob_get_clean(); // Get the output and clean the buffer

    // Parse the API response
    $response_data = json_decode($api_response, true);
    if (isset($response_data['status']) && $response_data['status'] === 'success' && $response_data['registered'] === 1) {
        $is_subscribed = true;
        // Join newsletter names with commas
        $earthen_subscriptions = !empty($response_data['newsletters']) ? implode(', ', $response_data['newsletters']) : '';
    }
}



// Function to grab active newsletters from Ghost API and populate the subscription form
function grabActiveEarthenSubs() {
    // Define the API URL for fetching newsletters
    $ghost_api_url = "https://earthen.io/ghost/api/v3/admin/newsletters/";

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
        echo "<script>console.error('Curl error: " . curl_error($ch) . "');</script>";
        exit();
    }

    if ($http_code >= 200 && $http_code < 300) {
        // Successful response, parse the JSON data
        $response_data = json_decode($response, true);

        if ($response_data && isset($response_data['newsletters']) && is_array($response_data['newsletters'])) {
            // Generate HTML for each newsletter
            foreach ($response_data['newsletters'] as $newsletter) {
                // Extract data
                $name = htmlspecialchars($newsletter['name']);
                $description = htmlspecialchars($newsletter['description']);
                $sender_name = htmlspecialchars($newsletter['sender_name']);
                $language = "English"; // Assuming all are in English; adjust if you have this data in the JSON

                // Define the icon color based on the order (for demonstration)
                $colors = ['light-red', 'light-yellow', 'light-blue', 'light-green'];
                $color = $colors[array_rand($colors)];

                // Output the subscription box HTML
                echo "
                    <div class=\"sub-box\" data-color=\"{$color}\">
                        <div class=\"sub-icon {$color}\"></div>
                        <div class=\"sub-content\">
                            <h4 class=\"sub-name\">{$name}</h4>
                            <p class=\"sub-sender-name\">by {$sender_name}</p>
                            <p class=\"sub-description\">{$description}</p>
                            <p class=\"subscription-language\">{$language}</p>
                        </div>
                    </div>
                ";
            }
        } else {
            echo "<script>console.log('No active newsletters found.');</script>";
        }
    } else {
        // Handle error
        error_log('HTTP status ' . $http_code . ': ' . $response);
        echo "<script>console.error('API call to Earthen.io failed with HTTP code: " . $http_code . "');</script>";
    }

    // Close the cURL session
    curl_close($ch);
}

// Call the function within your form or wherever you want the boxes to appear
grabActiveEarthenSubs();



function checkEarthenEmailStatus($email) {
    // Prepare and encode the email address for use in the API URL
    $email_encoded = urlencode($email);
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
        $newsletters = []; // Array to hold newsletter names

        if ($response_data && isset($response_data['members']) && is_array($response_data['members']) && count($response_data['members']) > 0) {
            $registered = 1; // Member with the given email exists

            // Extract newsletter names
            if (isset($response_data['members'][0]['newsletters'])) {
                foreach ($response_data['members'][0]['newsletters'] as $newsletter) {
                    $newsletters[] = $newsletter['name'];
                }
            }

            echo json_encode(['status' => 'success', 'registered' => $registered, 'message' => 'User is subscribed.', 'newsletters' => $newsletters]);
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

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Select Earthen Subscriptions</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php require_once ("../includes/signup-inc.php");?>
<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
<div id="top-page-image" class="credentials-banner top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;">
            <h2 data-lang-id="001-setup-access-heading">Select Earthen Subscriptions</h2>
            <p>In order to keep in touch with you <?php echo $first_name; ?>,
                <span data-lang-id="002-setup-access-heading-a">we've developed some exciting newsletters on our Earthen newsletter platform and can send them to <?php echo $credential_key; ?>.</span>
            </p>
            <div id="subscribed" style="display:<?php echo $is_subscribed ? 'block' : 'none'; ?>;">
                <?php if ($is_subscribed && !empty($earthen_subscriptions)): ?>
                    <p>Looks like you're already subscribed to: <?php echo htmlspecialchars($earthen_subscriptions); ?>!</p>
                <?php else: ?>
                    <p>It looks like you're already subscribed!</p>
                <?php endif; ?>
            </div>
            <div id="not-subscribed" style="display:<?php echo !$is_subscribed ? 'block' : 'none'; ?>;">You're not yet subscribed</div>

            <!-- SIGNUP FORM -->
            <form id="select-earthen-subs" method="post" action="earthen_register.php?id=<?php echo htmlspecialchars($buwana_id); ?>">
    <div class="subscription-boxes">
        <!-- Subscription boxes will be populated here by the PHP function -->
        <?php grabActiveEarthenSubs(); ?>
    </div>
</form>
        </div>

        <div style="font-size: medium; text-align: center; margin: auto; align-self: center; padding-top:40px; padding-bottom:40px; margin-top: 0px;">
            <p style="font-size:medium;" data-lang-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>


</div>

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php"); ?>

</body>
</html>
