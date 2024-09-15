<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

startSecureSession();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.764';
$page = 'login';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Initialize user variables
$first_name = '';
$buwana_id = '';
$country_icon = '';
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function

// Check if the user is logged in
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];
    require_once '../buwanaconn_env.php'; // Include the Buwana database connection

    // Fetch the user's continent icon
    $country_icon = getUserContinent($buwana_conn, $buwana_id);
    $watershed_name = getWatershedName($buwana_conn, $buwana_id, $lang);
    $buwana_conn->close(); // Close the database connection
}

// Determine if the user is logged in for dynamic content handling later
$is_logged_in = isset($buwana_id) && !empty($first_name);

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';

require_once '../includes/brik-inc.php';
require_once '../gobrikconn_env.php';

// Get the contents from the Ecobrick table as an ordered View, using the serial_no from the URL.
$serialNo = $_GET['serial_no'];

$sql = "SELECT * FROM tb_ecobricks WHERE serial_no = '" . $serialNo . "'";
$result = $gobrik_conn->query($sql);

if ($result->num_rows > 0) {
    while ($array = $result->fetch_assoc()) {
        echo '
        <div class="splash-content-block">
            <div class="splash-box">
                <div class="splash-heading"><span data-lang-id="001-splash-title">Ecobrick</span> ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . '</div>
                <div class="splash-sub">' . htmlspecialchars($array["weight_authenticated_kg"], ENT_QUOTES, 'UTF-8') . '&#8202;kg <span data-lang-id="002-splash-subtitle">of plastic has been secured out of the biosphere in</span> ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . '</div>
            </div>
            <div class="splash-image">
                <a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($array["ecobrick_full_photo_url"], ENT_QUOTES, 'UTF-8') . '\', \'Ecobrick ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '\')">
                    <img src="../' . htmlspecialchars($array["ecobrick_full_photo_url"], ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and authenticated on ' . htmlspecialchars($array["last_validation_ts"], ENT_QUOTES, 'UTF-8') . '">
                </a>
            </div>
        </div>
        <div id="splash-bar"></div>
        <div id="main-content">
            <div class="row">
                <div class="main">
                    <div class="row-details">';


if ($result->num_rows > 0) {
    while ($array = $result->fetch_assoc()) {
        echo '
        <div class="splash-content-block">
            <div class="splash-box">
                <div class="splash-heading"><span data-lang-id="001-splash-title">Ecobrick</span> ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . '</div>
                <div class="splash-sub">' . htmlspecialchars($array["weight_authenticated_kg"], ENT_QUOTES, 'UTF-8') . '&#8202;kg <span data-lang-id="002-splash-subtitle">of plastic has been secured out of the biosphere in</span> ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . '</div>
            </div>
            <div class="splash-image">
                <a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($array["ecobrick_full_photo_url"], ENT_QUOTES, 'UTF-8') . '\', \'Ecobrick ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '\')">
                    <img src="../' . htmlspecialchars($array["ecobrick_full_photo_url"], ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and authenticated on ' . htmlspecialchars($array["last_validation_ts"], ENT_QUOTES, 'UTF-8') . '">
                </a>
            </div>
        </div>';
    }
} else {
    // Code for handling no results
    echo '
    <div class="splash-content-block">
        <div class="splash-box">
            <div class="splash-heading">Sorry! :-(</div>
            <div class="splash-sub" data-lang-id="151x">No results for ecobrick ' . htmlspecialchars($serialNo, ENT_QUOTES, 'UTF-8') . ' in the Brikchain. Most likely this is because the Brikchain data is still in migration.</div>
        </div>
        <div class="splash-image"><img src="../webps/empty-ecobrick-450px.webp?v2" style="width: 80%; margin-top:20px;" alt="empty ecobrick"></div>
    </div>';
}
    <div id="splash-bar"></div>
    <div id="main-content">
        <div class="row">
            <div class="main">
                <br><br>
                <div class="ecobrick-data">
                    <p data-lang-id="152x">🚧 The data for ecobrick ' . $serialNo . ' has not yet been migrated to the blockchain. This could be because of transfer delay. Normally publishing occurs within 30 seconds of authentication. If more than 24hrs has passed, an error has occurred or this ecobrick was not authenticated.</p>
                </div>
                <br><br><br><br>
                <div class="page-paragraph">
                    <h3><p data-lang-id="154">The Brikchain</p></h3>
                    <p data-lang-id="155">When an ecobrick is authenticated, it is published to the brikcoin manual blockchain and coins are issued according to its ecological value. This is what we call the Brikchain. On the Brikchain, you can find authenticated ecobricks, blocks, and transactions that underpin the Brickoin complimentary currency.</p>
                    <p data-lang-id="156">As a non-capital, manual process, Brikcoins favors anyone anywhere willing to work with their hands to make a meaningful ecological contribution.</p>
                    <br>
                    <p><a class="action-btn-blue" href="brikchain.php" data-lang-id="157">🔎 Browse the Brikchain</a></p>
                    <p style="font-size: 0.85em; margin-top:20px;" data-lang-id="158">The live chain of transactions and ecobricks.</p>
                </div>
            </div>
            <div class="side">';
}

$gobrik_conn->close();
?>

<div class="side-module-desktop-mobile">
    <img src="../webps/aes-400px.webp" width="80%" alt="For-Earth Enterprise through eco bricking">
    <h5 data-lang-id="100-side-aes-text">The weight of the plastic inside an authenticated ecobrick is what we call Authenticated Ecobricked Plastic (AES plastic) for short.</h5><br>
    <a class="module-btn" href="/aes" target="_blank" data-lang-id="101-side-aes-button">About AES</a><br><br>
</div>

<div class="side-module-desktop-mobile">
    <img src="../webps/2-brikcoins-450px.webp" width="75%" loading="lazy" alt="eco brik and earth building can make regenerative structures">
    <h4>Brikcoins</h4>
    <h5 data-lang-id="102-side-brikcoins-text">When an ecobrick is authenticated, brikcoins are generated to represent the ecological value of its AES plastic.</h5><br>
    <a class="module-btn" href="brikcoins.php" data-lang-id="103-side-brikcoins-button">About Brikcoins</a><br><br>
</div>

<?php include 'side-modules/for-earth.php'; ?>

</div> <!-- End of main content -->

<!--FOOTER STARTS HERE-->
<?php require_once '../footer-2024.php'; ?>
</body>
</html>
