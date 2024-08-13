<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$ecobricker_id = $_GET['id'] ?? null;
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.453';
$page = 'activate';
$first_name = '';
$email_addr = '';
$languages = [];
$countries = [];

// PART 1: Check if the user is already logged in
if (isset($_SESSION['buwana_id'])) {
    header("Location: dashboard.php");
    exit();
}

// PART 2: Check if ecobricker_id is passed in the URL
if (is_null($ecobricker_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No ecobricker ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 3: Look up user information using ecobricker_id provided in URL

// GoBrik database credentials
$gobrik_servername = "localhost";
$gobrik_username = "ecobricks_brikchain_viewer";
$gobrik_password = "desperate-like-the-Dawn";
$gobrik_dbname = "ecobricks_gobrik_msql_db";

// Create connection to GoBrik database
$gobrik_conn = new mysqli($gobrik_servername, $gobrik_username, $gobrik_password, $gobrik_dbname);
if ($gobrik_conn->connect_error) {
    die("Connection failed: " . $gobrik_conn->connect_error);
}
$gobrik_conn->set_charset("utf8mb4");

// Fetch user information
$sql_user_info = "SELECT first_name, email_addr FROM tb_ecobrickers WHERE ecobricker_id = ?";
$stmt_user_info = $gobrik_conn->prepare($sql_user_info);
if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $ecobricker_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($first_name, $email_addr);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
} else {
    die('Error preparing statement for fetching user info: ' . $gobrik_conn->error);
}

// Fetch languages from Buwana database

    // Buwana database credentials
    $buwana_servername = "localhost";
    $buwana_username = "ecobricks_gobrik_app";
    $buwana_password = "1EarthenAuth!";
    $buwana_dbname = "ecobricks_earthenAuth_db";

    // Create connection for Buwana database
    $buwana_conn = new mysqli($buwana_servername, $buwana_username, $buwana_password, $buwana_dbname);
    if ($buwana_conn->connect_error) {
        error_log("Connection failed: " . $buwana_conn->connect_error);
        echo json_encode(['success' => false, 'error' => 'db_connection_failed']);
        ob_end_flush();
        exit();
    }
    $buwana_conn->set_charset("utf8mb4");

$sql_languages = "SELECT languages_id, language_name FROM languages_tb ORDER BY language_name";
$result_languages = $buwana_conn->query($sql_languages);
if ($result_languages->num_rows > 0) {
    while ($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}

// Fetch countries from Buwana database
$sql_countries = "SELECT country_id, country_name FROM tb_countries ORDER BY country_name";
$result_countries = $buwana_conn->query($sql_countries);
if ($result_countries->num_rows > 0) {
    while ($row = $result_countries->fetch_assoc()) {
        $countries[] = $row;
    }
}

$buwana_conn->close();

// PART 4: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_language_id = $_POST['language_id'];
    $selected_country_id = $_POST['country_id'];

    // Update the Buwana user's language and country
    $buwana_conn = new mysqli($gobrik_servername, $gobrik_username, $gobrik_password, "ecobricks_earthenAuth_db");
    if ($buwana_conn->connect_error) {
        error_log("Connection failed: " . $buwana_conn->connect_error);
        echo json_encode(['success' => false, 'error' => 'db_connection_failed']);
        exit();
    }
    $buwana_conn->set_charset("utf8mb4");

    $sql_update_buwana = "UPDATE users_tb SET languages_id = ?, country_id = ? WHERE email = ?";
    $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
    if ($stmt_update_buwana) {
        $stmt_update_buwana->bind_param('iis', $selected_language_id, $selected_country_id, $email_addr);
        $stmt_update_buwana->execute();
        $stmt_update_buwana->close();

        // Redirect to the next step
        header("Location: login.php?id=" . urlencode($ecobricker_id));
        exit();
    } else {
        error_log('Error preparing statement for updating Buwana user: ' . $buwana_conn->error);
        echo json_encode(['success' => false, 'error' => 'db_update_failed']);
        exit();
    }

    $buwana_conn->close();
}
?>




<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Activate your Buwana Account | Step 2 | GoBrik</title>
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
<div id="top-page-image" class="city-image top-page-image"></div>

<div id="form-submission-box" class="landing-page-form">
    <div class="form-container">

        <div style="text-align:center;width:100%;margin:auto;">
                      <h2>Ok, <?php echo htmlspecialchars($first_name); ?>, your new password has been set!</h2>
            <p>Now please tell us a little about yourself...</p>
                    </div>

        <!--ACTIVATE 3 FORM-->
        <form id="user-info-form" method="post" action="activate-3.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">
            <div class="form-item" id="language-select">
                <label for="language_id">Please tell us which language you prefer...</label><br>
                <select name="language_id" id="language_id" required>
                    <option value="">Select your language</option>
                    <?php foreach ($languages as $language) { ?>
                        <option value="<?php echo $language['languages_id']; ?>"><?php echo htmlspecialchars($language['language_name']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-item" id="country-select" style="display:none;">
                <label for="country_id">Please select your country of residence...</label><br>
                <select name="country_id" id="country_id" required>
                    <option value="">Select your country</option>
                    <?php foreach ($countries as $country) { ?>
                        <option value="<?php echo $country['country_id']; ?>"><?php echo htmlspecialchars($country['country_name']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div id="submit-section" style="text-align:center;margin-top:15px;">
                <input type="submit" id="submit-button" value="Continue" class="submit-button disabled" disabled>
            </div>
        </form>

    </div>

    <div style="text-align:center;width:100%;margin:auto;margin-top: 20px;">
        <p style="font-size:medium;" data-land-id="000-already-have-account">Already have an account? <a href="login.php">Login</a></p>
    </div>

</div>
</div>
    <!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>

<script>
$(document).ready(function() {
    // Show country selection after language is selected
    $('#language_id').change(function() {
        if ($(this).val() !== '') {
            $('#country-select').slideDown();
        } else {
            $('#country-select').slideUp();
            $('#submit-button').prop('disabled', true).addClass('disabled');
        }
    });

    // Enable submit button after country is selected
    $('#country_id').change(function() {
        if ($(this).val() !== '') {
            $('#submit-button').prop('disabled', false).removeClass('disabled');
        } else {
            $('#submit-button').prop('disabled', true).addClass('disabled');
        }
    });
});
</script>

</body>
</html>
