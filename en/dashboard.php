<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.365';
$page = 'dashboard';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Check if the user is logged in
if (!isset($_SESSION['buwana_id'])) {
    echo '<script>alert("Please login before viewing this page."); window.location.href = "login.php";</script>';
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

// Include database connection
include '../buwana_env.php'; // this file provides the database server, user, dbname information to access the server

// Look up fields from users_tb using the buwana_id
$first_name = '';
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_lookup_user = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
$stmt_lookup_user = $conn->prepare($sql_lookup_user);

if ($stmt_lookup_user) {
    $stmt_lookup_user->bind_param("i", $buwana_id);
    $stmt_lookup_user->execute();
    $stmt_lookup_user->bind_result($first_name);
    $stmt_lookup_user->fetch();
    $stmt_lookup_user->close();
} else {
    die("Error preparing statement for users_tb: " . $conn->error);
}

$conn->close();

// Include GoBrik database credentials
include '../gobrik_env.php';

// Create connection to GoBrik database
$conn2 = new mysqli($servername, $username, $password, $dbname);

if ($conn2->connect_error) {
    die("Connection failed: " . $conn2->connect_error);
}
// SQL query to fetch the count of ecobricks and the sum of weight_g divided by 1000 to get kg
$sql = "SELECT COUNT(*) as ecobrick_count, SUM(weight_g) / 1000 as total_weight FROM tb_ecobricks";
$result = $conn2->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ecobrick_count = $row['ecobrick_count'];
    $total_weight = $row['total_weight'];
} else {
    $ecobrick_count = 0;
    $total_weight = 0;
}

// SQL query to fetch the 12 most recent ecobricks
$sql_recent = "SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, location_full, ecobricker_maker, serial_no, status FROM tb_ecobricks ORDER BY date_logged_ts DESC LIMIT 12";
$result_recent = $conn2->query($sql_recent);

$recent_ecobricks = [];
if ($result_recent->num_rows > 0) {
    while($row = $result_recent->fetch_assoc()) {
        $recent_ecobricks[] = $row;
    }
}

$conn2->close();

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
'
?>




<title>Dashboard | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once ("../includes/dashboard-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>

<!-- PAGE CONTENT -->
   <div id="top-page-image" class="dolphin-pic top-page-image"></div>

<div id="form-submission-box" style="height:fit-content;">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;">
            <h2>Welcome <?php echo htmlspecialchars($first_name); ?>!</h2>
            <h3>You're logged into the brand new GoBrik 3.0!</h3>
            <p>As of today, <?php echo $ecobrick_count; ?> ecobricks have been logged on GoBrik, representing over <?php echo round($total_weight); ?> kg of sequestered plastic!</p>
        </div>
   <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <button class="go-button" id="log-ecobrick-button">➕ Log an Ecobrick</button>
            <!-- Logout Button -->
            <button class="go-button" id="logout-button" onclick="logoutUser()">📤 Logout</button>
        </div>

        <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
            <h3>Most Recent Ecobricks</h3>
            <table id="latest-ecobricks">
                <tr>
                    <th data-lang-id="1103-brik">Brik</th>
                    <th data-lang-id="1104-weight">Weight</th>
                    <th data-lang-id="1105-location">Location</th>
                    <th data-lang-id="1106-status">Status</th>
                    <th data-lang-id="1107-serial">Serial</th>
                </tr>
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
                <?php $serial_no = htmlspecialchars($ecobrick['serial_no']); $wrapped_serial_no = substr($serial_no, 0, 3) . '<br>' . substr($serial_no, 3, 3);?>
                <a href="brik.php?serial_no=<?php echo $serial_no; ?>"><?php echo $wrapped_serial_no; ?></a>
            </button>
        </td>
    </tr>
<?php endforeach; ?>

            </table>
        </div>


</div><!--closes dashboard content-->


</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once ("../footer-2024.php"); ?>

<script>
document.getElementById('log-ecobrick-button').addEventListener('click', function() {
    // Redirect to the log.php page
    window.location.href = 'log.php';
});
</script>

</body>
</html>
