<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.381';
$page = 'profile';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Check if the user is logged in
if (!isset($_SESSION['buwana_id'])) {
    echo '<script>alert("Please login before viewing this page."); window.location.href = "login.php";</script>';
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

// Include database connection
require_once '../gobrikconn_env.php';
require_once '../buwanaconn_env.php'; // Buwana database credentials

// Fetch user information using buwana_id from the Buwana database
$sql_user_info = "SELECT full_name, first_name, last_name, email, country_id, languages_id, birth_date, created_at, last_login, brikcoin_balance, role, account_status, notes, terms_of_service FROM users_tb WHERE buwana_id = ?";
$stmt_user_info = $buwana_conn->prepare($sql_user_info);

if ($stmt_user_info) {
    $stmt_user_info->bind_param('i', $buwana_id);
    $stmt_user_info->execute();
    $stmt_user_info->bind_result($full_name, $first_name, $last_name, $email, $country_id, $languages_id, $birth_date, $created_at, $last_login, $brikcoin_balance, $role, $account_status, $notes, $terms_of_service);
    $stmt_user_info->fetch();
    $stmt_user_info->close();
} else {
    die('Error preparing statement for fetching user info: ' . $buwana_conn->error);
}

// Fetch languages from Buwana database
$languages = [];
$sql_languages = "SELECT lang_id, languages_eng_name, language_active FROM languages_tb ORDER BY languages_eng_name";
$result_languages = $buwana_conn->query($sql_languages);
if ($result_languages->num_rows > 0) {
    while ($row = $result_languages->fetch_assoc()) {
        $languages[] = $row;
    }
}

// Fetch countries from Buwana database
$countries = [];
$sql_countries = "SELECT country_id, country_name FROM countries_tb ORDER BY country_name";
$result_countries = $buwana_conn->query($sql_countries);
if ($result_countries->num_rows > 0) {
    while ($row = $result_countries->fetch_assoc()) {
        $countries[] = $row;
    }
}
echo '<!DOCTYPE html>
<html lang="' . $lang . '">
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
            <div id="status-message"><?php echo htmlspecialchars($first_name); ?>'s Profile Settings</div>
            <div id="sub-status-message">Review and update your Buwana account profile here:</div>
        </div>



        <div class="left-column" style="font-size:0.9em">
            <!-- Non-editable Fields -->
            <div class="form-item">
                <p><strong>Full Name:</strong><p>
                    <h4> <?php echo htmlspecialchars($full_name); ?></h4>
            </div>
            <div class="form-item">
                <p><strong>Account Created At:</strong></p>
                <p><?php echo htmlspecialchars($created_at); ?></p>
            </div>
            <div class="form-item">
                <p><strong>Last Login:</strong></p>
                 <p><?php echo htmlspecialchars($last_login); ?></p>
            </div>
            <div class="form-item">
                <p><strong>Brikcoin Balance:</strong> <?php echo htmlspecialchars($brikcoin_balance); ?></p>
            </div>
            <div class="form-item">
                <p><strong>Role:</strong> <?php echo htmlspecialchars($role); ?></p>
            </div>
            <div class="form-item">
                <p><strong>Account Status:</strong> <?php echo htmlspecialchars($account_status); ?></p>
            </div>
            <div class="form-item">
                <p><strong>Account Notes:</strong> <?php echo htmlspecialchars($notes); ?></p>
            </div>
            <div class="form-item">
                <p><strong>Agreed to Terms of Service:</strong> <?php echo $terms_of_service ? 'Yes' : 'No'; ?></p>
            </div>
        </div>

        <div class="right-column">
            <!-- Editable Fields -->
            <form method="post" action="update_profile.php">
                <div class="form-item">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                </div>

                <div class="form-item">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                </div>

                <div class="form-item">
                    <label for="email">Email:</label>
                    <input type="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                </div>

                <div class="form-item">
                    <label for="country_id">Country:</label>
                    <select name="country_id" id="country_id">
                        <option value="">Select Country</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['country_id']; ?>" <?php if ($country['country_id'] == $country_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($country['country_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-item">
                    <label for="language_id">Preferred Language:</label>
                    <select name="language_id" id="language_id">
                        <option value="">Select Language</option>
                        <?php foreach ($languages as $language): ?>
                            <option value="<?php echo $language['lang_id']; ?>" <?php if ($language['lang_id'] == $languages_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($language['languages_eng_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-item">
                    <label for="birth_date">Birth Date:</label>
                    <input type="date" name="birth_date" id="birth_date" value="<?php echo htmlspecialchars($birth_date); ?>">
                </div>

                <!-- Save and Update Button -->
                <div data-lang-id="016-submit-button" style="margin:auto;text-align: center;margin-top:30px;">
                    <button type="submit" class="submit-button enabled" aria-label="Save and update">Save and Update</button>
                </div>
            </form>

    </div>

 <div class="form-item" style="margin: 70px 10px 40px 10px;">
            <p style="text-align:center;">You can delete your GoBrik and Buwana accounts permanently here.  Warning, this is permanent and immediate!</p>
            <!-- DELETE ACCOUNT FORM -->
            <form id="delete-account-form" method="post" action="delete_account.php?id=<?php echo htmlspecialchars($ecobricker_id); ?>">
                <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                    <button type="button" class="submit-button delete" onclick="confirmDeletion()">Delete my account</button>
                </div>

            </form>
            <p data-lang-id="003-warning" style="font-size:medium; text-align: center;">WARNING: This cannot be undone.</p>
            <br>
         </div>

        <!-- Other Dashboard Buttons -->
        <div style="display:flex;flex-flow:row;width:100%;justify-content:center; margin-top:50px;">
            <a href="newest-briks.php"><button id="newest-ecobricks-button" style="padding:5px;margin:5px;background:grey;border-radius:5px;color:var(--text-color);cursor:pointer;border:none;">📅 Newest Ecobricks</button></a>
            <!-- Logout Button -->
            <button id="logout-button" style="padding:5px;margin:5px;background:grey;border-radius:5px;color:var(--text-color);cursor:pointer;border:none;">📤 Log Out</button>
        </div>
    </div>
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
        } else if (status === 'failed') {
            updateStatusDiv.innerHTML = "🤔 Something went wrong with the update.";
        }
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

</body>
</html>
