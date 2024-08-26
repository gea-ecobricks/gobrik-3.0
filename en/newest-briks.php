<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.41';
$page = 'newest-briks';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Include GoBrik database credentials
 require_once '../gobrikconn_env.php'; //sets up buwana_conn database connection

// SQL query to fetch the count of ecobricks and the sum of weight_g divided by 1000 to get kg
$sql = "SELECT COUNT(*) as ecobrick_count, SUM(weight_g) / 1000 as total_weight FROM tb_ecobricks";
$result = $gobrik_conn->query($sql);

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
$result_recent = $gobrik_conn->query($sql_recent);

$recent_ecobricks = [];
if ($result_recent->num_rows > 0) {
    while ($row = $result_recent->fetch_assoc()) {
        $recent_ecobricks[] = $row;
    }
}

$gobrik_conn->close();

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

    <!-- PAGE CONTENT -->
    <div id="top-page-image" class="my-ecobricks top-page-image"></div>

    <div id="form-submission-box" class="landing-page-form">
        <div class="form-container">

            <div style="text-align:center;width:100%;margin:auto;">
                <h2>The Latest Ecobricks</h2>
                <p>As of today, <?php echo $ecobrick_count; ?> ecobricks have been logged on GoBrik, representing over <?php echo round($total_weight); ?> kg of sequestered plastic!</p>
            </div>
            <div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
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

            <div style="display:flex;flex-flow:row;width:100%;justify-content:center;margin-top:30px;">
                <button class="go-button" id="log-ecobrick-button">➕ Log an Ecobrick</button>
            </div>
        </div>
    </div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2024.php"); ?>

<script>
document.getElementById('log-ecobrick-button').addEventListener('click', function() {
    // Redirect to the log.php page
    window.location.href = 'log.php';
});

</script>

</body>
</html>
