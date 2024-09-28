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
$version = '0.777';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$response = ['success' => false];
$buwana_id = $_GET['id'] ?? null;

// Initialize user variables
$credential_type = '';
$credential_key = '';
$first_name = '';
$account_status = '';
$country_icon = '';
// Global variable to store the user's subscribed newsletters
$subscribed_newsletters = [];


// Include database connection
include '../buwanaconn_env.php';
include '../gobrikconn_env.php';
require_once ("../scripts/earthen_subscribe_functions.php");

// Look up user information if buwana_id is provided
if ($buwana_id) {
    $gea_status = getGEA_status($buwana_id);  //added here
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



// Check subscription status
$is_subscribed = false;
$earthen_subscriptions = ''; // To store newsletter names if subscribed
if (!empty($credential_key)) {
    // Call the function and capture the JSON response
    $api_response = checkEarthenEmailStatus($credential_key);

    // Parse the API response
    $response_data = json_decode($api_response, true);

    // Check if the response is valid JSON and handle accordingly
    if (json_last_error() === JSON_ERROR_NONE && isset($response_data['status']) && $response_data['status'] === 'success') {
        if ($response_data['registered'] === 1) {
            $is_subscribed = true;
            // Join newsletter names with commas for display
            $earthen_subscriptions = implode(', ', $subscribed_newsletters);
        }
    } else {
        // Handle invalid JSON or other errors
        echo '<script>console.error("Invalid JSON response or error: ' . htmlspecialchars($response_data['message'] ?? 'Unknown error') . '");</script>';
    }
}

}

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Select Earthen Subscriptions</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php require_once ("../includes/activate-subscriptions-inc.php");?>
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
            <div id="earthen-server-error" class="form-field-error"></div>

            <!-- SLECT SUBSCRIPTIONS FORM -->
                   <!-- SIGNUP FORM -->
        <!-- SIGNUP FORM -->
        <form id="select-earthen-subs" method="post" action="process_sub_selections.php" style="margin-top:30px;">
            <input type="hidden" name="subscribed_newsletters" value="<?php echo htmlspecialchars(json_encode($subscribed_newsletters)); ?>">
            <input type="hidden" name="credential_key" value="<?php echo htmlspecialchars($credential_key); ?>">
            <input type="hidden" name="buwana_id" value="<?php echo htmlspecialchars($buwana_id); ?>">

            <div class="subscription-boxes">
                <!-- Subscription boxes will be populated here by the PHP function -->
                <?php grabActiveEarthenSubs(); ?>
            </div>

            <p class="form-caption" style="text-align:center; margin-top: 20px">Note: these subscriptions are independent of GoBrik account notifications that we sometimes need to send.</p>
              <div class="form-item" id="notifications-confirm">
                        <input type="checkbox" id="newsletter" name="newsletter" checked>
                        <label for="newsletter" style="font-size:1.0;" class="form-caption" data-lang-id="014-i-agree-newsletter">I agree to receive the <a href="#" onclick="showModalInfo('earthen', '<?php echo $lang; ?>')" class="underline-link">Earthen newsletter</a> for app, ecobrick, and earthen updates</label>
                    </div>

            <div id="submit-section" style="text-align:center;margin-top:25px;" data-lang-id="016-complete-button">
                <input type="submit" id="submit-button" value="Setup Complete!" class="submit-button enabled">
            </div>
        </form>



        </div>
    </did>


    </div>
</div>


</div>

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php"); ?>

<script>

    // subBoxHighlighter.js

document.addEventListener('DOMContentLoaded', function () {
    const subBoxes = document.querySelectorAll('.sub-box');

    subBoxes.forEach(box => {
        const checkbox = box.querySelector('.sub-checkbox');

        // Toggle checkbox when box is clicked
        box.addEventListener('click', function (event) {
            if (event.target !== checkbox && event.target.className !== 'checkbox-label') {
                checkbox.checked = !checkbox.checked;
            }
            updateBoxStyle(box, checkbox.checked);
        });

        // Update style on checkbox change
        checkbox.addEventListener('change', function () {
            updateBoxStyle(box, checkbox.checked);
        });
    });

    function updateBoxStyle(box, isSelected) {
        if (isSelected) {
            box.style.border = '2px solid green';
            box.style.backgroundColor = 'var(--darker)';
        } else {
            box.style.border = '1px solid rgba(128, 128, 128, 0.5)';
            box.style.backgroundColor = 'transparent';
        }
    }
});





</script>




</body>
</html>
