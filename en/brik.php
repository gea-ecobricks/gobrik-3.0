<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

startSecureSession();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.766';
$page = 'brik';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Initialize user variables
$first_name = '';
$buwana_id = '';
$country_icon = '';
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function

// Check if the user is logged in
if (isLoggedIn()) {
    $buwana_id = $_SESSION['buwana_id'];
    require_once '../buwanaconn_env.php'; // Include the Buwana database connection

    // Fetch the user's continent icon
    $country_icon = getUserContinent($buwana_conn, $buwana_id);
    $watershed_name = getWatershedName($buwana_conn, $buwana_id, $lang);
    $buwana_conn->close();  // Close the database connection
}

// Determine if the user is logged in for dynamic content handling later
$is_logged_in = isset($buwana_id) && !empty($first_name);

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
</head>
<body>';

require_once ("../includes/brik-inc.php");
require_once '../gobrikconn_env.php';

// Get the contents from the Ecobrick table as an ordered View, using the serial_no from the URL
$serialNo = $_GET['serial_no'];

$sql = "SELECT * FROM tb_ecobricks WHERE serial_no = '" . $serialNo . "'";
$result = $gobrik_conn->query($sql);

if ($result->num_rows > 0) {
    while($array = $result->fetch_assoc()) {
        // Check the status of the ecobrick
        $status = strtolower($array["status"]);
        $isAuthenticated = ($status === "authenticated");

        // If the ecobrick is authenticated, use the existing display
        if ($isAuthenticated) {
            echo '
            <div class="splash-content-block">
                <div class="splash-box">
                    <div class="splash-heading"><span data-lang-id="001-splash-title">Ecobrick</span> ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . '</div>
                    <div class="splash-sub">' . htmlspecialchars($array["weight_g"], ENT_QUOTES, 'UTF-8') . '&#8202;g <span data-lang-id="002-splash-subtitle">of plastic has been secured out of the biosphere in</span> ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . '</div>
                </div>
                <div class="splash-image">
                    <a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($array["ecobrick_full_photo_url"], ENT_QUOTES, 'UTF-8') . '\', \'Ecobrick ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '\')"><img src="../' . htmlspecialchars($array["ecobrick_full_photo_url"], ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and authenticated on ' . htmlspecialchars($array["last_validation_ts"], ENT_QUOTES, 'UTF-8') . '"></a>
                </div>
            </div>
            <div id="splash-bar"></div>';
        } else {

            // NON AUTHENTICATED ECOBRICKS
            echo '
            <div class="splash-content-block">
                <div class="splash-box">

                    <div class="splash-sub">Ecobrick</span> ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '. It is pending review and authentication.</div>
                </div>
                <div class="splash-image">
                    <a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($array["ecobrick_full_photo_url"], ENT_QUOTES, 'UTF-8') . '\', \'Ecobrick ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '\')"><img src="../' . htmlspecialchars($array["ecobrick_full_photo_url"], ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . ' and authenticated on ' . htmlspecialchars($array["last_validation_ts"], ENT_QUOTES, 'UTF-8') . '"></a>
                </div>
            </div>
            <div id="splash-bar"></div>';
        }

        // Continue with the rest of the page content as it is
        echo '
        <div id="main-content">
            <div class="row">
                <div class="main">
                    <div class="row-details">';

        // Trim the vision value and check if it is set and not blank
        if (isset($array["vision"])) {
            $visionText = trim($array["vision"]);

            // Check if the trimmed value is not empty or a single space
            if ($visionText !== '' && $visionText !== ' ') {
                // Remove any existing quotation marks from the vision value
                $cleanedVisionText = str_replace('"', '', $visionText);

                // Display the cleaned value wrapped in quotation marks
                echo '<p><div class="vision-quote" style="margin-top:25px;"> "' . $cleanedVisionText . '" </div></p>';
            }
        }

        echo '<div class="lead-page-paragraph">
                <p><b>' . htmlspecialchars($array["owner"], ENT_QUOTES, 'UTF-8') . ' <span data-lang-id="110">has ecobricked </span> ' . htmlspecialchars($array["weight_g"], ENT_QUOTES, 'UTF-8') . '&#8202;g<span data-lang-id="111"> of community plastic in </span>' . htmlspecialchars($array["location_full"], ENT_QUOTES, 'UTF-8') . '<span data-lang-id="112"> using a </span>' . htmlspecialchars($array["volume_ml"], ENT_QUOTES, 'UTF-8') . 'ml <span data-lang-id="113"> bottle to make a </span>' . htmlspecialchars($array["sequestration_type"], ENT_QUOTES, 'UTF-8') . '.</b></p>
            </div>
            <div class="main-details">
                <div class="page-paragraph">
                    <p><span data-lang-id="114">This ecobrick was with a density of </span>' . htmlspecialchars($array["density"], ENT_QUOTES, 'UTF-8') . '&#8202;g/ml <span data-lang-id="115">and represents </span>' . htmlspecialchars($array["CO2_kg"], ENT_QUOTES, 'UTF-8') . '&#8202;kg <span data-lang-id="116">of sequestered CO2. The ecobrick is permanently marked with Serial Number </span>' . htmlspecialchars($array["serial_no"], ENT_QUOTES, 'UTF-8') . '<span data-lang-id="117"> and on </span>' . htmlspecialchars($array["date_logged_ts"], ENT_QUOTES, 'UTF-8') . '<span data-lang-id="118"> was automatically added to the validation queue.</p>
                    <p><b>This ecobrick has not yet been peer reviewed.  Its plastic has not yet been authenticated as sequestered.</b></p>
                    <br>
                </div>
            </div>';

        if (isset($array["selfie_photo_url"]) && $array["selfie_photo_url"] != '') {
            echo '<div class="side-details">
                    <img src="' . htmlspecialchars($array["selfie_photo_url"], ENT_QUOTES, 'UTF-8') . '" width="100%">
                  </div>';
        }

    echo '

			    </div>
			    <div id="data-chunk">
				<div class="ecobrick-data">
					<p style="margin-left: -32px;font-weight: bold;" data-lang-id="125"> +++ Raw Brikchain Data Record</p><br>
					<p>--------------------</p>
					<p data-lang-id="126">BEGIN BRIK RECORD ></p>';
			echo '
    <p><b data-lang-id="127">Logged:</b> ' . $array["date_logged_ts"] . '</p>
    <p><b data-lang-id="128">Volume:</b> ' . $array["volume_ml"] . ' &#8202;ml</p>
    <p><b data-lang-id="129">Weight:</b> ' . $array["weight_g"] . '&#8202;g</p>
    <p><b data-lang-id="130">Density:</b> ' . $array["density"] . '&#8202;g/ml</p>
    <p><b data-lang-id="131">CO2e:</b> ' . $array["CO2_kg"] . ' &#8202;kg</p>
    <p><b data-lang-id="132">Brikcoin value:</b> ' . $array["ecobrick_dec_brk_val"] . '&#8202;ß</p>
    <p><b data-lang-id="133">Maker:</b> <i>' . $array["owner"] . '</i></p>
    <p><b data-lang-id="134">Sequestration:</b> ' . $array["sequestration_type"] . '</p>
    <p><b data-lang-id="135">Brand:</b> ' . $array["brand_name"] . '</p>
    <p><b data-lang-id="136">Bottom colour:</b> ' . $array["bottom_colour"] . '</p>
    <p><b data-lang-id="137">Plastic source:</b> ' . $array["plastic_from"] . '</p>
    <p><b data-lang-id="138">Community:</b> ' . $array["community_name"] . '</p>
    <p><b data-lang-id="139">City:</b> ' . $array["location_city"] . '</p>
    <p><b data-lang-id="140">Region:</b> ' . $array["location_region"] . '</p>
    <p><b data-lang-id="141">Country:</b> ' . $array["location_country"] . '</p>
    <p><b data-lang-id="142">Full location:</b> ' . $array["location_full"] . '</p>
    <p><b data-lang-id="143">Validation:</b> ' . $array["last_validation_ts"] . '</p>
    <p><b data-lang-id="144">Validator 1:</b> ' . $array["validator_1"] . '</p>
    <p><b data-lang-id="145">Validator 2:</b> ' . $array["validator_2"] . '</p>
    <p><b data-lang-id="146">Validator 3:</b> ' . $array["validator_3"] . '</p>
    <p><b data-lang-id="147">Validation score avg.:</b> ' . $array["validation_score_avg"] . '</p>
    <p><b data-lang-id="147b">Catalyst:</b> ' . $array["catalyst"] . '</p>
    <p><b data-lang-id="148">Validation score final:</b> ' . $array["final_validation_score"] . '</p>
    <p><b data-lang-id="149">Authenticated weight:</b> ' . $array["weight_authenticated_kg"] . '&#8202;kg</p>
    <p data-lang-id="150">||| END RECORD.</p>

';

        echo '</div>
			</div>
            <br><hr><br>
            <div class="page-paragraph">
                <h3><p data-lang-id="151">The Brikchain</p></h3>
                <p data-lang-id="152">When an ecobrick is authenticated (like the one above!) it is published to the brikcoin manual blockchain and brikcoins are issued according to its ecological value. This is what we call the Brikchain. On the Brikchain, you can find this ecobrick as well as all the other ecobricks, blocks, and transactions that underpin the Brickoin currency.</p>
                <p data-lang-id="153">As a non-capital, manual process, brikcoin generation favors anyone anywhere willing to work with their hands to make a meaningful ecological contribution.</p>
                <br>
                <p><a class="action-btn-blue" href="brikchain.php" data-lang-id="154">🔎 Browse the Brikchain</a></p>
                <p style="font-size: 0.85em; margin-top:20px;" data-lang-id="155">The live chain of transactions and ecobricks.</p>
            </div>
        </div>';

        echo '<div class="side">
            <div class="side-module-desktop-mobile">
                <img src="../pngs/authenticated-ecobrick.png" width="90%" alt="Following the Earths example through eco bricking">
                <br><h4 data-lang-id="104-side-authenticated-text">Pending Review!</h4>
                <h5 data-lang-id="105-side-authenticated-text">This ecobrick is currently in the validation queue awaiting peer review and authentication.</h5><br><br><br>
            </div>';

    }
} else {
    // ECOBRICK NOT FOUND
    echo '<div class="splash-content-block">
            <div class="splash-box">
                <div class="splash-heading">Sorry! :-(</div>
                <div class="splash-sub" data-lang-id="151x">No results for ecobrick ' . htmlspecialchars($serialNo, ENT_QUOTES, 'UTF-8') . ' in the Brikchain. Most likely this is because the Brikchain data is still in migration.</div>
            </div>
            <div class="splash-image"><img src="../webps/empty-ecobrick-450px.webp?v2" style="width: 80%; margin-top:20px;" alt="empty ecobrick"></div>
        </div>
        <div id="splash-bar"></div>

        <a name="top"></a>

        <div id="main-content">
            <div class="row">
                <div class="main">
                    <br><br>
                    <div class="ecobrick-data">
                        <p data-lang-id="152x">🚧 The data for ecobrick ' . htmlspecialchars($serialNo, ENT_QUOTES, 'UTF-8') . ' has not yet been migrated to the blockchain. This could be because of transfer delay. Normally publishing occurs within 30 seconds of authentication. If more than 24hrs has passed, an error has occurred or this ecobrick was not authenticated.</p>
                    </div>
                    <br><br><br><br>
                    <div class="page-paragraph">
                        <p><h3 data-lang-id="154">The Brikchain</h3></p>
                        <p data-lang-id="155">When an ecobrick is authenticated, it is published to the brikcoin manual blockchain and coins are issued according to its ecological value. This is what we call the Brikchain. On the Brikchain, you can find authenticated ecobricks, blocks, and transactions that underpin the Brickoin complementary currency.</p>
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
</div>

<div class="side-module-desktop-mobile">
    <img src="../webps/2-brikcoins-450px.webp" width="75%" loading="lazy" alt="eco brik and earth building can make regenerative structures">
    <h4>Brikcoins</h4>
    <h5 data-lang-id="102-side-brikcoins-text">When an ecobrick is authenticated, brikcoins are generated to represent the ecological value of its AES plastic.</h5><br>
</div>

<?php include 'side-modules/for-earth.php';?>

</div>
</div>
</div>
</div><!--closes main?-->
<!--FOOTER STARTS HERE-->
<?php require_once ("../footer-2024.php");?>
</body>
</html>
