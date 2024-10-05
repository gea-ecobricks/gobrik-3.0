<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../gobrikconn_env.php';  // Include connection file
require_once '../buwanaconn_env.php';  // Additional connection file

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.766';
$page = 'brik';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function

// Check if the user is logged in
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];
    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
}

echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
<title>Ecobrick Record</title>
</head>
<body>';

require_once ("../includes/brik-inc.php");

// Get the contents from the Ecobrick table as an ordered View, using the serial_no from the URL
$serialNo = $_GET['serial_no'];

$sql = "SELECT serial_no, weight_g, location_full, ecobrick_full_photo_url, date_logged_ts, last_validation_ts, status, vision, owner, volume_ml, sequestration_type, density, CO2_kg, selfie_photo_url, ecobrick_dec_brk_val, brand_name, bottom_colour, plastic_from, community_name, location_city, location_region, location_country, validator_1, validator_2, validator_3, validation_score_avg, catalyst, final_validation_score, weight_authenticated_kg FROM tb_ecobricks WHERE serial_no = ?";
$stmt = $gobrik_conn->prepare($sql);

if ($stmt) {
    // Bind the serial_no as a string
    $stmt->bind_param("s", $serialNo);
    $stmt->execute();

    // Store the result
    $stmt->store_result();

    // Bind the result variables
    $stmt->bind_result(
        $serial_no, $weight_g, $location_full, $ecobrick_full_photo_url, $date_logged_ts, $last_validation_ts, $status,
        $vision, $owner, $volume_ml, $sequestration_type, $density, $CO2_kg, $selfie_photo_url, $ecobrick_dec_brk_val,
        $brand_name, $bottom_colour, $plastic_from, $community_name, $location_city, $location_region, $location_country,
        $validator_1, $validator_2, $validator_3, $validation_score_avg, $catalyst, $final_validation_score, $weight_authenticated_kg
    );

    // Fetch the results
    while ($stmt->fetch()) {
        // Check the status of the ecobrick
        $status = strtolower($status);
        $isAuthenticated = ($status === "authenticated");

        // If the ecobrick is authenticated, use the existing display
        if ($isAuthenticated) {
            echo '
            <div class="splash-content-block">
                <div class="splash-box">
                    <div class="splash-heading"><span data-lang-id="001-splash-title">Ecobrick</span> ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . '</div>
                    <div class="splash-sub">' . htmlspecialchars($weight_g, ENT_QUOTES, 'UTF-8') . '&#8202;g <span data-lang-id="002-splash-subtitle">of plastic has been secured out of the biosphere in</span> ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . '</div>
                </div>
                <div class="splash-image">
                    <a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '\', \'Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '\')">
                    <img src="../' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and authenticated on ' . htmlspecialchars($last_validation_ts, ENT_QUOTES, 'UTF-8') . '"></a>
                </div>
            </div>
            <div id="splash-bar"></div>';
        } else {
            // NON AUTHENTICATED ECOBRICKS
            echo '
            <div class="splash-content-block">
                <div class="splash-box">
                    <div class="splash-sub">Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '. It is pending review and authentication.</div>
                </div>
                <div class="splash-image">
                    <a href="javascript:void(0);" onclick="viewGalleryImage(\'' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '\', \'Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '\')"><img src="../' . htmlspecialchars($ecobrick_full_photo_url, ENT_QUOTES, 'UTF-8') . '" alt="Ecobrick ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and logged on ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '" title="Ecobrick Serial ' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . ' was made in ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . ' and authenticated on ' . htmlspecialchars($last_validation_ts, ENT_QUOTES, 'UTF-8') . '"></a>
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

        // VISION
        if (!empty(trim($vision))) {
            $cleanedVisionText = str_replace('"', '', trim($vision));
            echo '<p><div class="vision-quote" style="margin-top:25px;"> "' . htmlspecialchars($cleanedVisionText, ENT_QUOTES, 'UTF-8') . '" </div></p>';
        }

        // EXPLANATION
        echo '<div class="lead-page-paragraph">
                <p><b>' . htmlspecialchars($owner, ENT_QUOTES, 'UTF-8') . ' <span data-lang-id="110">has ecobricked </span> ' . htmlspecialchars($weight_g, ENT_QUOTES, 'UTF-8') . '&#8202;g<span data-lang-id="111"> of community plastic in </span>' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . '<span data-lang-id="112"> using a </span>' . htmlspecialchars($volume_ml, ENT_QUOTES, 'UTF-8') . 'ml <span data-lang-id="113"> bottle to make a </span>' . htmlspecialchars($sequestration_type, ENT_QUOTES, 'UTF-8') . '.</b></p>
            </div>
            <div class="main-details">
                <div class="page-paragraph">
                    <p><span data-lang-id="114">This ecobrick has a density of </span>' . htmlspecialchars($density, ENT_QUOTES, 'UTF-8') . '&#8202;g/ml <span data-lang-id="115">and represents </span>' . htmlspecialchars($CO2_kg, ENT_QUOTES, 'UTF-8') . '&#8202;kg <span data-lang-id="116">of sequestered CO2. The ecobrick is permanently marked with Serial Number </span>' . htmlspecialchars($serial_no, ENT_QUOTES, 'UTF-8') . '<span data-lang-id="117"> and was added to the validation queue on </span>' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '.</p>
                    <p><b>This ecobrick has not yet been peer-reviewed. Its plastic has not been authenticated as sequestered.</b></p>
                    <br>
                </div>
            </div>';

        // IF THERE'S A SELFIE IT GOES HERE
        if (!empty($selfie_photo_url)) {
            echo '<div class="side-details">
                    <img src="' . htmlspecialchars($selfie_photo_url, ENT_QUOTES, 'UTF-8') . '" width="100%">
                  </div>';
        }

        // DATA CHUNK
        echo '
			    </div>
			    <div id="data-chunk">
				<div class="ecobrick-data">
					<p style="margin-left: -32px;font-weight: bold;" data-lang-id="125"> +++ Raw Brikchain Data Record</p><br>
					<p>--------------------</p>
					<p data-lang-id="126">BEGIN BRIK RECORD ></p>';
        echo '
    <p><b data-lang-id="127">Logged:</b> ' . htmlspecialchars($date_logged_ts, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="128">Volume:</b> ' . htmlspecialchars($volume_ml, ENT_QUOTES, 'UTF-8') . ' &#8202;ml</p>
    <p><b data-lang-id="129">Weight:</b> ' . htmlspecialchars($weight_g, ENT_QUOTES, 'UTF-8') . '&#8202;g</p>
    <p><b data-lang-id="130">Density:</b> ' . htmlspecialchars($density, ENT_QUOTES, 'UTF-8') . '&#8202;g/ml</p>
    <p><b data-lang-id="131">CO2e:</b> ' . htmlspecialchars($CO2_kg, ENT_QUOTES, 'UTF-8') . ' &#8202;kg</p>
    <p><b data-lang-id="132">Brikcoin value:</b> ' . htmlspecialchars($ecobrick_dec_brk_val, ENT_QUOTES, 'UTF-8') . '&#8202;ß</p>
    <p><b data-lang-id="133">Maker:</b> <i>' . htmlspecialchars($owner, ENT_QUOTES, 'UTF-8') . '</i></p>
    <p><b data-lang-id="134">Sequestration:</b> ' . htmlspecialchars($sequestration_type, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="135">Brand:</b> ' . htmlspecialchars($brand_name, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="136">Bottom colour:</b> ' . htmlspecialchars($bottom_colour, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="137">Plastic source:</b> ' . htmlspecialchars($plastic_from, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="138">Community:</b> ' . htmlspecialchars($community_name, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="139">City:</b> ' . htmlspecialchars($location_city, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="140">Region:</b> ' . htmlspecialchars($location_region, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="141">Country:</b> ' . htmlspecialchars($location_country, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="142">Full location:</b> ' . htmlspecialchars($location_full, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="143">Validation:</b> ' . htmlspecialchars($last_validation_ts, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="144">Validator 1:</b> ' . htmlspecialchars($validator_1, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="145">Validator 2:</b> ' . htmlspecialchars($validator_2, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="146">Validator 3:</b> ' . htmlspecialchars($validator_3, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="147">Validation score avg.:</b> ' . htmlspecialchars($validation_score_avg, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="147b">Catalyst:</b> ' . htmlspecialchars($catalyst, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="148">Validation score final:</b> ' . htmlspecialchars($final_validation_score, ENT_QUOTES, 'UTF-8') . '</p>
    <p><b data-lang-id="149">Authenticated weight:</b> ' . htmlspecialchars($weight_authenticated_kg, ENT_QUOTES, 'UTF-8') . '&#8202;kg</p>
    <p data-lang-id="150">||| END RECORD.</p>';
    }

    $stmt->close();  // Close the statement
} else {
    echo "Failed to prepare the SQL statement: " . $gobrik_conn->error;
}

$gobrik_conn->close();  // Close the database connection
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
