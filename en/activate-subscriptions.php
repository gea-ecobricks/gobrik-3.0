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
$version = '0.779';
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

// Check subscription status
$is_subscribed = false;
if (!empty($credential_key)) {
    ob_start(); // Start output buffering to capture the JSON response
    checkEarthenEmailStatus($credential_key);
    $api_response = ob_get_clean(); // Get the output and clean the buffer

    // Parse the API response
    $response_data = json_decode($api_response, true);
    if (isset($response_data['status']) && $response_data['status'] === 'success' && $response_data['registered'] === 1) {
        $is_subscribed = true;
    }
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
            <p>In order to keep in touch with you <?php echo $first_name; ?>, <span data-lang-id="002-setup-access-heading-a">we've developed some exciting newsletters on our Earthen newsletter platform.</span></p>
            <p id="subscribed" style="display:<?php echo $is_subscribed ? 'block' : 'none'; ?>;">It looks like you're already subscribed! Nice!</p>
            <p id="not-subscribed" style="display:<?php echo !$is_subscribed ? 'block' : 'none'; ?>;">You're not yet subscribed</p>
        </div>

        <!-- SIGNUP FORM -->
        <form id="select-earthen-subs" method="post" action="earthen_register.php?id=<?php echo htmlspecialchars($buwana_id); ?>">
            <!-- Form contents go here -->
        </form>

    </div>

    <div style="font-size: medium; text-align: center; margin: auto; align-self: center; padding-top:40px; padding-bottom:40px; margin-top: 0px;">
        <p style="font-size:medium;" data-lang-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
    </div>

</div>

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php"); ?>

</body>
</html>
