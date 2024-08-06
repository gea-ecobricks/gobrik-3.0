<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.365';
$page = 'newest-briks';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

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
    while ($row = $result_recent->fetch_assoc()) {
        $recent_ecobricks[] = $row;
    }
}

$conn2->close();

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
<title>Newest Ecobricks</title>
';
?>

<title>Newest Ecobricks | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/newest-briks-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>
<div id="top-page-image" class="dolphin-pic top-page-image"></div>

<!-- DASHBOARD CONTENT -->
<div id="form-submission-box" style="height:fit-content;">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;">
            <h2>The Latest Ecobricks</h2>
            <p>As of today, <?php echo $ecobrick_count; ?> ecobricks have been logged on GoBrik, representing over <?php echo round($total_weight); ?> kg of sequestered plastic!</p>
        </div>
        <div style="display:flex;flex-flow:row;width:100%;justify-content:center;">
            <button class="go-button" id="log-ecobrick-button">➕ Log an Ecobrick</button>
            <!-- Login Button -->
            <button class="go-button" id="login-button" onclick="loginUser()">📤 Log In</button>
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
                                <?php $serial_no = htmlspecialchars($ecobrick['serial_no']); $wrapped_serial_no = substr($serial_no, 0, 3) . '<br>' . substr($serial_no, 3, 3); ?>
                                <a href="brik.php?serial_no=<?php echo $serial_no; ?>"><?php echo $wrapped_serial_no; ?></a>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2024.php"); ?>

<script type="text/javascript">
function showModalInfo(type) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    let content = '';
    photobox.style.display = 'none';
    switch (type) {
        case 'reset':
            content = `
                <img src="../pngs/exchange-bird.png" alt="Reset Password" height="250px" width="250px" class="preview-image">
                <div class="preview-title">Reset Password</div>
                <div class="preview-text">Oops! This function is not yet operational. Create another account for the moment as all accounts will be deleted once we migrate from beta to live.</div>
            `;
            break;
        default:
            content = '<p>Invalid term selected.</p>';
    }

    messageContainer.innerHTML = content;

    // Show the modal and update other page elements
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}

document.getElementById('log-ecobrick-button').addEventListener('click', function() {
    // Redirect to the log.php page
    window.location.href = 'log.php';
});

function loginUser() {
    // Redirect to the login.php page
    window.location.href = 'login.php';
}
</script>

</body>
</html>
