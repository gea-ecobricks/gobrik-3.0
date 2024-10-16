<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.43';
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
    $sql_lookup_user = "SELECT first_name, ecobricks_made, location_full_txt, ecobricker_id FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt_lookup_user = $gobrik_conn->prepare($sql_lookup_user);

    if ($stmt_lookup_user) {
        $stmt_lookup_user->bind_param("i", $buwana_id);
        $stmt_lookup_user->execute();
        $stmt_lookup_user->bind_result($first_name, $ecobricks_made, $location_full_txt, $ecobricker_id);
        $stmt_lookup_user->fetch();
        $stmt_lookup_user->close();
    } else {
        die("Error preparing statement for tb_ecobrickers: " . $gobrik_conn->error);
    }

$maker_id = $ecobricker_id; // Assuming ecobricker_id is equivalent to maker_id

// Fetch all ecobricks data for the user's maker_id directly from tb_ecobricks
$sql_recent = "
    SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, weight_g / 1000 AS weight_kg, volume_ml,
           location_full, ecobricker_maker, serial_no, status
    FROM tb_ecobricks
    WHERE maker_id = ?
    ORDER BY date_logged_ts DESC";

$stmt_recent = $gobrik_conn->prepare($sql_recent);

$total_weight = 0; // Total weight in kilograms
$total_volume = 0; // Total volume in ml
$recent_ecobricks = [];
$ecobrick_count = 0; // Count of ecobricks

if ($stmt_recent) {
    // Bind maker_id to the query
    $stmt_recent->bind_param("s", $maker_id);
    $stmt_recent->execute();

    // Bind the results, now also binding weight_g (in grams) and weight_kg (in kilograms)
    $stmt_recent->bind_result($ecobrick_thumb_photo_url, $ecobrick_full_photo_url, $weight_g, $weight_kg, $volume_ml, $location_full, $ecobricker_maker, $serial_no, $status);

    // Fetch and process the results
    while ($stmt_recent->fetch()) {
        $recent_ecobricks[] = [
            'ecobrick_thumb_photo_url' => $ecobrick_thumb_photo_url,
            'ecobrick_full_photo_url' => $ecobrick_full_photo_url,
            'weight_g' => $weight_g,  // Now the weight in grams is available
            'weight_kg' => $weight_kg,  // Also have the weight in kilograms
            'volume_ml' => $volume_ml,
            'location_full' => $location_full,
            'ecobricker_maker' => $ecobricker_maker,
            'serial_no' => $serial_no,
            'status' => $status,
        ];
        $total_weight += $weight_kg; // Sum up total weight in kilograms
        $total_volume += $volume_ml; // Sum up total volume in ml
        $ecobrick_count++; // Increment the ecobrick count
        $net_density = $total_volume > 0 ? ($total_weight * 1000) / $total_volume : 0;
    }

        // Close the statement after fetching
        $stmt_recent->close();
    } else {
        die("Error preparing the statement for fetching ecobricks: " . $gobrik_conn->error);
    }

    // Calculate net density


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
            <img src="/<?php echo htmlspecialchars($ecobrick['ecobrick_thumb_photo_url']); ?>?v=2"
                 alt="Ecobrick Thumbnail"
                 class="table-thumbnail"
                 onclick="ecobrickPreview('<?php echo htmlspecialchars($ecobrick['ecobrick_full_photo_url']); ?>?v=2', '<?php echo htmlspecialchars($ecobrick['serial_no']); ?>', '<?php echo htmlspecialchars($ecobrick['weight_g']); ?>g', '<?php echo htmlspecialchars($ecobrick['ecobricker_maker']); ?>', '<?php echo htmlspecialchars($ecobrick['location_full']); ?>')">
        </td>
        <td><?php echo htmlspecialchars($ecobrick['weight_g']); ?>g</td>
        <td><?php echo htmlspecialchars($ecobrick['location_full']); ?></td>
        <td><?php echo htmlspecialchars($ecobrick['status']); ?></td>
        <td>
            <!-- The button that calls the JavaScript function -->
            <button class="serial-button" onclick="viewEcobrickActions('<?php echo htmlspecialchars($ecobrick['serial_no']); ?>', '<?php echo htmlspecialchars($ecobrick['status']); ?>', '<?php echo htmlspecialchars($lang); ?>')">
                <?php
                // Display the serial number without line breaks
                echo htmlspecialchars($ecobrick['serial_no']);
                ?>
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



<script>
function viewEcobrickActions(serial_no, status, lang) {
    console.log("Button clicked with serial number:", serial_no);
    const modal = document.getElementById('form-modal-message');
    const messageContainer = document.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');

    // Clear existing content in the modal
    messageContainer.innerHTML = '';

    // Determine the appropriate language object
    let translations;
    switch (lang) {
        case 'fr':
            translations = fr_Translations;
            break;
        case 'es':
            translations = es_Translations;
            break;
        case 'id':
            translations = id_Translations;
            break;
        default:
            translations = en_Translations; // Default to English
    }

    // Properly encode serial number for URL safety
    let encodedSerialNo = encodeURIComponent(serial_no);

    // Construct the content (stack of buttons)
    let content = `
        <a class="ecobrick-action-button" href="brik.php?serial_no=${encodedSerialNo}" data-lang-id="013-view-ecobrick-post">
            🔍 ${translations['013-view-ecobrick-post']}
        </a>
        <a class="ecobrick-action-button" href="log.php?retry=${encodedSerialNo}" data-lang-id="015-edit-ecobrick">
            ✏️ ${translations['015-edit-ecobrick']}
        </a>
        <a class="ecobrick-action-button deleter-button" href="javascript:void(0);" onclick="deleteEcobrick('${encodedSerialNo}')" data-lang-id="014-delete-ecobrick">
            ❌ ${translations['014-delete-ecobrick']}
        </a>
    `;

    // Insert the content into the message container
    messageContainer.innerHTML = content;

    // Display the modal
    modal.style.display = 'flex';
    modalBox.style.background = 'none';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

function deleteEcobrick(serial_no) {
    // Ask the user for confirmation
    if (confirm('Are you sure you want to delete this ecobrick from the database? This cannot be undone.')) {
        // Send the delete request via fetch
        fetch('delete-ecobrick.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'serial_no': serial_no, // Send serial_no
                'action': 'delete_ecobrick' // Include action for clarity
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json(); // Expecting JSON from the server
        })
        .then(data => {
            if (data.success) {
                alert('Your ecobrick has been successfully deleted.');
//                 window.location.href = 'dashboard.php'; // Redirect after deletion
            } else {
                alert('There was an error deleting the ecobrick: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error processing your request.');
        });
    }
}
</script>



</body>
</html>
