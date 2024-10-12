<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.38';
$page = 'dashboard';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);

   // Fetch user details from the GoBrik database
    $sql_lookup_user = "SELECT first_name, ecobricks_made, location_full_txt, maker_id FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt_lookup_user = $gobrik_conn->prepare($sql_lookup_user);

    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $buwana_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name, $ecobricks_made, $location_full_txt, $maker_id);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement for tb_ecobrickers: " . $gobrik_conn->error);
    }


    // Fetch other user data (e.g., ecobrick count and weight)
    //Please help me modify this php so that it returns a weight in kg rather than grams for total_weight:
// Prepare SQL query to count ecobricks and calculate total weight filtered by maker_id and buwana_id
$sql = "
    SELECT COUNT(*) as ecobrick_count, SUM(weight_g) / 1000 as total_weight
    FROM tb_ecobricks
    JOIN tb_ecobrickers ON tb_ecobricks.maker_id = tb_ecobrickers.maker_id
    WHERE tb_ecobricks.maker_id = ? AND tb_ecobrickers.buwana_id = ?";

$stmt = $gobrik_conn->prepare($sql);

if ($stmt) {
    // Bind both maker_id and buwana_id to the query
    $stmt->bind_param("ss", $maker_id, $buwana_id);
    $stmt->execute();
    $stmt->bind_result($ecobrick_count, $total_weight);

    // Fetch the results
    if ($stmt->fetch()) {
        // Handle cases where null values may be returned (e.g., no records found)
        $ecobrick_count = $ecobrick_count ? $ecobrick_count : 0;
        $total_weight = $total_weight ? $total_weight : 0;
    } else {
        $ecobrick_count = 0;
        $total_weight = 0;
    }

    $stmt->close();
} else {
    die("Error preparing statement for ecobrick data: " . $gobrik_conn->error);
}

// Fetch all ecobricks data for the user's maker id directly from tb_ecobricks
$sql_recent = "
    SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, volume_ml,
           location_full, ecobricker_maker, serial_no, status
    FROM tb_ecobricks
    WHERE maker_id = ?
    ORDER BY date_logged_ts DESC";

$stmt_recent = $gobrik_conn->prepare($sql_recent);

$total_weight = 0;
$total_volume = 0;
$recent_ecobricks = [];

if ($stmt_recent) {
    // Bind maker_id to the query
    $stmt_recent->bind_param("s", $maker_id);
    $stmt_recent->execute();

    // Bind the results
    $stmt_recent->bind_result($ecobrick_thumb_photo_url, $ecobrick_full_photo_url, $weight_g, $volume_ml, $location_full, $ecobricker_maker, $serial_no, $status);

    // Fetch and process the results
    while ($stmt_recent->fetch()) {
        $recent_ecobricks[] = [
            'ecobrick_thumb_photo_url' => $ecobrick_thumb_photo_url,
            'ecobrick_full_photo_url' => $ecobrick_full_photo_url,
            'weight_g' => $weight_g,
            'volume_ml' => $volume_ml,
            'location_full' => $location_full,
            'ecobricker_maker' => $ecobricker_maker,
            'serial_no' => $serial_no,
            'status' => $status,
        ];
        $total_weight += $weight_g;
        $total_volume += $volume_ml;
    }

    // Close the statement after fetching
    $stmt_recent->close();
} else {
    die("Error preparing the statement for fetching ecobricks: " . $gobrik_conn->error);
}


    // Calculate net density
    $net_density = $total_volume > 0 ? $total_weight / $total_volume : 0;

    // Close database connections
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    // Redirect to login page with the redirect parameter set to the current page
    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "login.php?redirect=' . urlencode($page) . '";
    </script>';
    exit();
}

// Output the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/dashboard-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>
<div id="top-page-image" class="dolphin-pic top-page-image"></div>

<!-- DASHBOARD CONTENT -->
<div id="form-submission-box" style="height:fit-content;margin-top: 90px;">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;">
            <h2 id="greeting">Hello <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p id="subgreeting">Welcome to the new GoBrik 3.0!</p>
        </div>
        <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <a href="log.php" class="confirm-button enabled" id="log-ecobrick-button" data-lang-id="001-log-an-ecobrick">➕ Log an Ecobrick</a>
        </div>

        <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <h3 data-lang-id="002-my-ecobricks">My Ecobricks</h3>
            <table id="latest-ecobricks">
                <tr>
                    <th data-lang-id="1103-brik">Brik</th>
                    <th data-lang-id="1104-weight">Weight</th>
                    <th data-lang-id="1105-location">Location</th>
                    <th data-lang-id="1106-status">Status</th>
                    <th data-lang-id="1107-serial">Serial</th>
                </tr>
                <?php if (empty($recent_ecobricks)) : ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">
                            <span data-lang-id="003-no-ecobricks-yet">It looks like you haven't logged any ecobricks yet! When you do, they will appear here for you to manage.</span>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($recent_ecobricks as $ecobrick) : ?>
                        <tr>
                            <td>
                                <img src="/<?php echo htmlspecialchars($ecobrick['ecobrick_thumb_photo_url']); ?>"
                                     alt="Ecobrick Thumbnail"
                                     class="table-thumbnail"
                                     onclick="ecobrickPreview('<?php echo htmlspecialchars($ecobrick['ecobrick_full_photo_url']); ?>', '<?php echo htmlspecialchars($ecobrick['serial_no']); ?>', '<?php echo htmlspecialchars($ecobrick['weight_g']); ?>g', '<?php echo htmlspecialchars($ecobrick['ecobricker_maker']); ?>', '<?php echo htmlspecialchars($ecobrick['location_full']); ?>')">
                            </td>
                            <td><?php echo htmlspecialchars($ecobrick['weight_g']); ?>g</td>
                            <td><?php echo htmlspecialchars($ecobrick['location_full']); ?></td>
                            <td><?php echo htmlspecialchars($ecobrick['status']); ?></td>
                            <td>
                                <button class="serial-button">
                                    <?php $serial_no = htmlspecialchars($ecobrick['serial_no']); $wrapped_serial_no = substr($serial_no, 0, 3) . '<br>' . substr($serial_no, 3, 3); ?>
                                    <a href="brik.php?serial_no=<?php echo $serial_no; ?>"><?php echo $wrapped_serial_no; ?></a>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>

        <div style="display:flex;flex-flow:row;width:100%;justify-content:center; margin-top:50px;">
            <a href="newest-briks.php"><button id="newest-ecobricks-button"  style="padding:5px;margin:5px;background:grey;border-radius:5px;color:var(--text-color);cursor:pointer;border:none;" data-lang-id="005-newest-ecobricks">📅 Newest Ecobricks</button></a>
        </div>

    <!--    <div style="text-align:center;width:100%;margin:auto;">
            <p style="font-size:smaller;">As of today, <?php echo $ecobrick_count; ?> ecobricks have been logged on GoBrik, representing over <?php echo round($total_weight); ?> kg of sequestered plastic!</p>
        </div>
    -->
    </div>
</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2024.php"); ?>

<script type="text/javascript">

// JavaScript to determine the user's time of day and display an appropriate greeting
window.onload = function() {
    var now = new Date();
    var hours = now.getHours();
    var greeting;
    var lang = "<?php echo htmlspecialchars($lang); ?>"; // Get the language from PHP

    // Determine greeting based on the time of day
    if (hours < 12) {
        switch (lang) {
            case 'fr':
                greeting = "Bonjour";
                break;
            case 'es':
                greeting = "Buenos días";
                break;
            case 'id':
                greeting = "Selamat pagi";
                break;
            case 'en':
            default:
                greeting = "Good morning";
                break;
        }
    } else if (hours < 18) {
        switch (lang) {
            case 'fr':
                greeting = "Bon après-midi";
                break;
            case 'es':
                greeting = "Buenas tardes";
                break;
            case 'id':
                greeting = "Selamat siang";
                break;
            case 'en':
            default:
                greeting = "Good afternoon";
                break;
        }
    } else {
        switch (lang) {
            case 'fr':
                greeting = "Bonsoir";
                break;
            case 'es':
                greeting = "Buenas noches";
                break;
            case 'id':
                greeting = "Selamat malam";
                break;
            case 'en':
            default:
                greeting = "Good evening";
                break;
        }
    }

    document.getElementById("greeting").innerHTML = greeting + " <?php echo htmlspecialchars($first_name); ?>!";
}


//
// document.getElementById('log-ecobrick-button').addEventListener('click', function() {
//     // Redirect to the log.php page
//     window.location.href = 'log.php';
// });

document.getElementById('newest-ecobricks-button').addEventListener('click', function() {
    // Redirect to the newest-briks.php page
    window.location.href = 'newest-briks.php';
});
// Main greeting function to determine the user's time of day and display an appropriate greeting
function mainGreeting() {
    var now = new Date();
    var hours = now.getHours();
    var greeting;
    var lang = "<?php echo htmlspecialchars($lang); ?>"; // Get the language from PHP

    // Determine greeting based on the time of day
    if (hours < 12) {
        switch (lang) {
            case 'fr':
                greeting = "Bonjour";
                break;
            case 'es':
                greeting = "Buenos días";
                break;
            case 'id':
                greeting = "Selamat pagi";
                break;
            case 'en':
            default:
                greeting = "Good morning";
                break;
        }
    } else if (hours < 18) {
        switch (lang) {
            case 'fr':
                greeting = "Bon après-midi";
                break;
            case 'es':
                greeting = "Buenas tardes";
                break;
            case 'id':
                greeting = "Selamat siang";
                break;
            case 'en':
            default:
                greeting = "Good afternoon";
                break;
        }
    } else {
        switch (lang) {
            case 'fr':
                greeting = "Bonsoir";
                break;
            case 'es':
                greeting = "Buenas noches";
                break;
            case 'id':
                greeting = "Selamat malam";
                break;
            case 'en':
            default:
                greeting = "Good evening";
                break;
        }
    }

    document.getElementById("greeting").innerHTML = greeting + " <?php echo htmlspecialchars($first_name); ?>!";
}

// Secondary greeting function to provide additional dynamic content
function secondaryGreeting() {
    // Retrieve the language setting from the server-side PHP variable
    const lang = '<?php echo htmlspecialchars($lang); ?>';
    const ecobricksMade = <?php echo (int) $ecobricks_made; ?>;
    const locationFullTxt = '<?php echo htmlspecialchars($location_full_txt); ?>';
    const totalWeight = '<?php echo htmlspecialchars($total_weight); ?>';
    const netDensity = '<?php echo number_format($net_density, 2); ?>';

    // Determine the appropriate language object based on the current language setting
    let translations;
    switch (lang) {
        case 'fr':
            translations = fr_Page_Translations;
            break;
        case 'es':
            translations = es_Page_Translations;
            break;
        case 'id':
            translations = id_Page_Translations;
            break;
        default:
            translations = en_Page_Translations; // Default to English if no match is found
    }

    // Determine the message to display based on the number of ecobricks made
    let message;
    if (ecobricksMade < 1) {
        message = translations.welcomeBeta;
    } else {
        // Replace placeholders with dynamic values
        message = translations.loggedEcobricks
            .replace('{ecobricksMade}', ecobricksMade)
            .replace('{locationFullTxt}', locationFullTxt)
            .replace('{totalWeight}', totalWeight)
            .replace('{netDensity}', netDensity);
    }

    // Set the inner HTML of the subgreeting paragraph
    document.getElementById('subgreeting').innerHTML = message;
}

// Combine both functions in the window.onload event
window.onload = function() {
    mainGreeting();
    secondaryGreeting();
};


</script>

</body>
</html>
