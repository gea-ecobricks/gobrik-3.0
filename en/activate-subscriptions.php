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

// Ensure the output is valid JSON
if (!empty($credential_key)) {
    // Call the checkEarthenEmailStatus function with the user's email address
    $response = checkEarthenEmailStatus($credential_key);

    // Check and clean the response before embedding
    $response_data = json_decode($response, true);

    if ($response_data === null || json_last_error() !== JSON_ERROR_NONE) {
        // Log the error for debugging purposes
        error_log("Invalid JSON response from checkEarthenEmailStatus: " . $response);
        echo '<script>console.error("Invalid JSON response from server.");</script>';
    } else {
        // Safely embed the JSON data as a JavaScript variable on the page
        echo '<script>const subscriptionData = ' . json_encode($response_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ';</script>';
    }

    // Check if the user is subscribed based on the response data
    if (isset($response_data['status']) && $response_data['status'] === 'success' && $response_data['registered'] === 1) {
        $is_subscribed = true;
        $earthen_subscriptions = !empty($response_data['newsletters']) ? implode(', ', $response_data['newsletters']) : '';
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

            <!-- SIGNUP FORM -->
            <form id="select-earthen-subs" method="post" action="earthen_register.php?id=<?php echo htmlspecialchars($buwana_id); ?>" style="margin-top:30px;">
                <div class="subscription-boxes">
                    <!-- Subscription boxes will be populated here by the PHP function -->
                    <?php grabActiveEarthenSubs(); ?>
                </div>

            <p class="form-caption" style="text-align:center; margin-top: 20px">Note: these subscriptions are indepedent of GoBrik account notifications that we sometimes need to send.</p>

            <div id="submit-section" style="text-align:center;margin-top:25px;" data-lang-id="016-complete-button">
                <input type="submit" id="submit-button" value="Setup Complete!" class="submit-button enabled">

    </div>
            </form>
        </div>
    </did>

<div style="color: var(--text-color); margin-left: 0px;">
    <span data-lang-id="1000-logged-in-as">Logged in as</span>
    <span><?php echo htmlspecialchars($first_name); ?></span>  |
    <span style="color: var(--subdued);">
        <?php
        if ($gea_status !== null) {
            echo "GEA Status: " . htmlspecialchars($gea_status);
        } else {
            $response['error'] = 'gea_status_error';
            echo "GEA Status: Not available"; // Optional: display an alternative message
        }
        ?>
    </span>
</div>

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


// JavaScript function to modify the subscription presentation based on subscription status
function modifySubscriptionPresentation(subscriptionData) {
    const { registered, newsletters } = subscriptionData;

    // Map newsletter IDs to the corresponding checkbox IDs
    const idToCheckboxMap = {
        '62943b9aad0b695aa46139b0': 'default-newsletter', // Earthen
        '6621d4f55e227d049e56f404': 'gea-trainers', // GEA Trainer Newsletter (English)
        '662352b4d27acf008a160ac2': 'gea-trainer-newsletter-indonesian', // Buletin Pelatih Ecobrick (Indonesian)
        '663b20e9d27acf008a250eb0': 'updates-by-russell' // Ayyew 452
    };

    // Default newsletter checkbox to preselect if the user is not subscribed to any
    const defaultNewsletterCheckbox = document.getElementById('default-newsletter');

    // Uncheck all checkboxes initially
    document.querySelectorAll('.sub-checkbox').forEach((checkbox) => {
        checkbox.checked = false;
    });

    // Check if the user is subscribed
    if (registered === 1) {
        // Loop through the subscribed newsletters and check the corresponding checkboxes
        newsletters.forEach(newsletter => {
            const checkboxId = idToCheckboxMap[newsletter.id]; // Find the corresponding checkbox ID using the newsletter ID
            const checkbox = document.getElementById(checkboxId);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    } else {
        // If not subscribed, check the default newsletter (Earthen)
        if (defaultNewsletterCheckbox) {
            defaultNewsletterCheckbox.checked = true;
        }
    }
}

// Run the modifySubscriptionPresentation function on page load
document.addEventListener('DOMContentLoaded', function () {
    // Ensure subscriptionData is available before calling the function
    if (typeof subscriptionData !== 'undefined') {
        modifySubscriptionPresentation(subscriptionData);
    } else {
        console.error('No subscription data found.');
    }
});




</script>




</body>
</html>
