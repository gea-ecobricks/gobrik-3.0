<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.368';
$page = 'dashboard';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Check if the user is logged in
if (!isset($_SESSION['buwana_id'])) {
    echo '<script>alert("Please login before viewing this page."); window.location.href = "login.php";</script>';
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

// Include database connection
include '../gobrik_env.php'; // this file provides the database server, user, dbname information to access the GoBrik server

// Create connection to GoBrik database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Look up fields from tb_ecobrickers using the buwana_id
$sql_lookup_user = "SELECT first_name, ecobricks_made, location_full_txt, maker_id FROM tb_ecobrickers WHERE buwana_id = ?";
$stmt_lookup_user = $conn->prepare($sql_lookup_user);

if ($stmt_lookup_user) {
    $stmt_lookup_user->bind_param("i", $buwana_id);
    $stmt_lookup_user->execute();
    $stmt_lookup_user->bind_result($first_name, $ecobricks_made, $location_full_txt, $maker_id);
    $stmt_lookup_user->fetch();
    $stmt_lookup_user->close();
} else {
    die("Error preparing statement for tb_ecobrickers: " . $conn->error);
}

// SQL query to fetch the 20 most recent ecobricks made by the user and calculate totals
$sql_recent = "SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, volume_ml, location_full, ecobricker_maker, serial_no, status FROM tb_ecobricks WHERE maker_id = ? ORDER BY date_logged_ts DESC LIMIT 20";
$stmt_recent = $conn->prepare($sql_recent);

$total_weight = 0;
$total_volume = 0;
$recent_ecobricks = [];
if ($stmt_recent) {
    $stmt_recent->bind_param("s", $maker_id);
    $stmt_recent->execute();
    $stmt_recent->bind_result($ecobrick_thumb_photo_url, $ecobrick_full_photo_url, $weight_g, $volume_ml, $location_full, $ecobricker_maker, $serial_no, $status);
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
    $stmt_recent->close();
}

$conn->close();

if ($total_volume > 0) {
    $net_density = $total_weight / $total_volume;
} else {
    $net_density = 0;
}

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
';

?>

<title>Dashboard | GoBrik 3.0</title>

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
            <p>Welcome to the new GoBrik 3.0! So far you've logged <?php echo htmlspecialchars($ecobricks_made); ?> ecobricks in <?php echo htmlspecialchars($location_full_txt); ?>! In total you've logged <?php echo $total_weight; ?> grams with a net density of <?php echo number_format($net_density, 2); ?> g/ml. Your knack maker id is <?php echo htmlspecialchars($maker_id); ?>.</p>
        </div>
        <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <button class="go-button" id="log-ecobrick-button">➕ Log an Ecobrick</button>

        </div>

        <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <h3>My Ecobricks</h3>
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
                            It looks like you haven't logged any ecobricks yet! When you do, they will appear here for you to manage.
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($recent_ecobricks as $ecobrick) : ?>
                        <tr>
                            <td>
                                <img src="https://ecobricks.org/<?php echo htmlspecialchars($ecobrick['ecobrick_thumb_photo_url']); ?>"
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

        <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <button id="newest-ecobricks-button" onclick="newestEcobricks()">📅 Newest Ecobricks</button>
            <!-- Logout Button -->
            <button id="logout-button" onclick="logoutUser()">📤 Log Out</button>
        </div>
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

    if (hours < 12) {
        greeting = "Good morning";
    } else if (hours < 18) {
        greeting = "Good afternoon";
    } else {
        greeting = "Good evening";
    }

    document.getElementById("greeting").innerHTML = greeting + " <?php echo htmlspecialchars($first_name); ?>!";
}


document.getElementById('log-ecobrick-button').addEventListener('click', function() {
    // Redirect to the log.php page
    window.location.href = 'log.php';
});

document.getElementById('newest-ecobricks-button').addEventListener('click', function() {
    // Redirect to the newest-briks.php page
    window.location.href = 'newest-briks.php';
});

document.getElementById('logout-button').addEventListener('click', function() {
    // Log out and redirect to the login.php page
    window.location.href = 'logout.php';
});

</script>

</body>
</html>
