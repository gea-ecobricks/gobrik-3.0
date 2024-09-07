<?php
//confirm-email and activate-2 sends users here.
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
$version = '0.51';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
// set $is_logged_in to false for this page
$is_logged_in = false;

// Initialize variables
$buwana_id = $_GET['id'] ?? null;  // Correctly initializing buwana_id
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

// PART 2: Check if buwana_id is passed in the URL
if (is_null($buwana_id)) {
    echo '<script>
        alert("Hmm... something went wrong. No buwana ID was passed along. Please try logging in again. If this problem persists, you\'ll need to create a new account.");
        window.location.href = "login.php";
    </script>';
    exit();
}

// PART 3: Look up user information using buwana_id provided in URL

// Buwana database credentials
require_once ("../buwanaconn_env.php");

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

// Fetch languages from Buwana database
$sql_languages = "SELECT lang_id, languages_eng_name, language_active FROM languages_tb ORDER BY languages_eng_name";
$result_languages = $buwana_conn->query($sql_languages);

if ($result_languages->num_rows > 0) {
    while ($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}

// Fetch countries from Buwana database
$sql_countries = "SELECT country_id, country_name FROM countries_tb ORDER BY country_name";
$result_countries = $buwana_conn->query($sql_countries);

if ($result_countries->num_rows > 0) {
    while ($row = $result_countries->fetch_assoc()) {
        $countries[] = $row;
    }
} else {
    echo "No countries found.";
}


// PART 4: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_language_id = $_POST['language_id'];
    $selected_country_id = $_POST['country_id'];

    // Update the Buwana user's language and country using buwana_id
    $sql_update_buwana = "UPDATE users_tb SET languages_id = ?, country_id = ? WHERE buwana_id = ?";
    $stmt_update_buwana = $buwana_conn->prepare($sql_update_buwana);
    if ($stmt_update_buwana) {
        $stmt_update_buwana->bind_param('iii', $selected_language_id, $selected_country_id, $buwana_id);
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

// Close the Buwana database connection after all operations are done
$buwana_conn->close();

?>





<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<title>Activate your Buwana Account | Step 3 | GoBrik</title>
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
    <div id="status-message"><?php echo htmlspecialchars($first_name); ?>, <span data-lang-id="012-status-heading">your password & email are set!</span><div>
    <div id="sub-status-message" data-lang-id="013-sub-status-tell">Now please tell us a little about yourself...</div>
</div>


        <!--ACTIVATE 3 FORM-->
        <form id="user-info-form" method="post" action="activate-3.php?id=<?php echo htmlspecialchars($buwana_id); ?>">
                        <div class="form-item" id="language-select">
                          <!--<label for="language_id">Please tell us which language you prefer...</label><br>-->
            <select name="language_id" id="language_id" style="max-width:480px;display: block;margin: auto;cursor:pointer;" required>
                <!-- Placeholder option -->
                <span data-lang-id="014-language-placeholder">
                <option value="" disabled selected>Select your preferred language...</option>
                <span>

                <?php foreach ($languages as $language): ?>
                    <option value="<?php echo htmlspecialchars($language['lang_id']); ?>"
                        <?php echo $language['language_active'] == 0 ? 'disabled' : ''; ?>>
                        <?php echo htmlspecialchars($language['languages_eng_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            </div>

            <div class="form-item" id="country-select" style="display:none;">
                <!--<label for="country_id">Please select your country of residence...</label><br>-->
              <select name="country_id" id="country_id" style="max-width:480px;display: block;margin: auto;cursor:pointer;" required >
                  <span data-lang-id="015-country-place-holder">
                    <option value="">Select your country of residence...</option>
                  </span>
                    <?php foreach ($countries as $country) { ?>
                        <option value="<?php echo $country['country_id']; ?>">
                            <?php echo htmlspecialchars($country['country_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div id="submit-section" style="text-align:center;margin-top:15px;" data-lang-id="016-submit-complete-button">
                <input type="submit" id="submit-button" value="✔️ Complete Setup" class="submit-button disabled" disabled>
            </div>
        </form>
    </div>



</div>
</div>
    <!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show country selection after language is selected
    var languageSelect = document.getElementById('language_id');
    var countrySelect = document.getElementById('country-select');
    var submitButton = document.getElementById('submit-button');
    var countryDropdown = document.getElementById('country_id');

    languageSelect.addEventListener('change', function() {
        if (this.value !== '') {
            countrySelect.style.display = 'block'; // Show the country select
        } else {
            countrySelect.style.display = 'none'; // Hide the country select
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
        }
    });

    // Enable submit button after country is selected
    countryDropdown.addEventListener('change', function() {
        if (this.value !== '') {
            submitButton.disabled = false;
            submitButton.classList.remove('disabled');
            submitButton.classList.add('enabled');
        } else {
            submitButton.disabled = true;
            submitButton.classList.remove('enabled');
            submitButton.classList.add('disabled');
        }
    });
});
</script>

</body>
</html>
