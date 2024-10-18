
<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.43';
$page = 'newest-briks';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

// Include database connection
require_once '../gobrikconn_env.php';

// Check if the user is logged in
$is_logged_in = isLoggedIn();
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'];

    // Fetch the user's details if needed (e.g., first_name, user_location)
    require_once '../buwanaconn_env.php';
    $first_name = getFirstName($buwana_conn, $buwana_id);
    $buwana_conn->close();
}

// Fetch the count of ecobricks and the total weight in kg
$sql = "SELECT COUNT(*) as ecobrick_count, SUM(weight_g) / 1000 as total_weight FROM tb_ecobricks WHERE status != 'not ready'";
$result = $gobrik_conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ecobrick_count = number_format($row['ecobrick_count']);
    $total_weight = number_format(round($row['total_weight'])) . ' kg'; // Add a half-space before 'kg'
} else {
    $ecobrick_count = '0';
    $total_weight = '0 kg';
}

$gobrik_conn->close();


echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>



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






<div style="text-align:center;width:100%;margin:auto;margin-top:25px;">
    <h2 data-lang-id="001-latest-ecobricks">Latest Ecobricks</h2>
<p>
    As of today, <?php echo number_format($ecobrick_count); ?> ecobricks have been logged on GoBrik,
    representing over <?php echo number_format(round($total_weight)); ?> kg of sequestered plastic!
</p>

    <table id="latest-ecobricks" class="display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th data-lang-id="1103-brik">Brik</th>
                <th data-lang-id="1104-weight">Weight</th>
                <th data-lang-id="1108-volume">Volume</th>
                <th data-lang-id="1109-density">Density</th>
                <th data-lang-id="1110-date-logged">Date Logged</th>
                <th data-lang-id="1105-location">Location</th>
                <th data-lang-id="1106-status">Status</th>
                <th data-lang-id="1107-serial">Serial</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_ecobricks as $ecobrick) : ?>
                <tr>
                    <td>
                        <img src="<?php echo htmlspecialchars($ecobrick['ecobrick_thumb_photo_url']); ?>"
                             alt="Ecobrick <?php echo htmlspecialchars($ecobrick['serial_no']); ?> Thumbnail"
                             title="Ecobrick <?php echo htmlspecialchars($ecobrick['serial_no']); ?>"
                             class="table-thumbnail"
                             onclick="ecobrickPreview('<?php echo htmlspecialchars($ecobrick['ecobrick_full_photo_url']); ?>', '<?php echo htmlspecialchars($ecobrick['serial_no']); ?>', '<?php echo htmlspecialchars($ecobrick['weight_g']); ?> g', '<?php echo htmlspecialchars($ecobrick['ecobricker_maker']); ?>', '<?php echo htmlspecialchars($ecobrick['date_logged_ts']); ?>')">
                    </td>
                    <td><?php echo htmlspecialchars($ecobrick['weight_g']); ?> g</td>
                    <td><?php echo htmlspecialchars($ecobrick['volume_ml']); ?> ml</td>
                    <td><?php echo number_format($ecobrick['density'], 2); ?> g/ml</td>
                    <td><?php echo date("Y-m-d", strtotime($ecobrick['date_logged_ts'])); ?></td>
                    <td style="white-space: normal;"><?php echo htmlspecialchars($ecobrick['location_brik']); ?></td>
                    <td><?php echo htmlspecialchars($ecobrick['status']); ?></td>
                    <td>
                        <button class="serial-button" data-text="<?php echo htmlspecialchars($ecobrick['serial_no']); ?>">
                            <a href="brik.php?serial_no=<?php echo htmlspecialchars($ecobrick['serial_no']); ?>">
                                <?php echo htmlspecialchars($ecobrick['serial_no']); ?>
                            </a>
                        </button>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
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
    $(document).ready(function() {
        $('#latest-ecobricks').DataTable({
            "responsive": true,
            "serverSide": true,
            "processing": true,
            "ajax": {
                "url": "../api/fetch_ecobricks.php",
                "type": "POST"
            },
            "pageLength": 12,
            "language": {
                "emptyTable": "It looks like no ecobricks have been logged yet!",
                "lengthMenu": "Show _MENU_ briks",
                "search": "",
                "info": "Showing _START_ to _END_ of _TOTAL_ ecobricks",
                "infoEmpty": "No ecobricks available",
                "loadingRecords": "Loading ecobricks...",
                "processing": "Processing...",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [0, 6] }, // Make the image and status columns unsortable
                { "className": "all", "targets": [0, 1, 7] }, // Ensure Brik (thumbnail), Weight, and Serial always display
                { "className": "min-tablet", "targets": [2, 3, 4] }, // These fields can be hidden first on smaller screens
                { "className": "none", "targets": [5] } // Allow Location text to wrap as needed
            ],
            "initComplete": function() {
                var searchBox = $('div.dataTables_filter input');
                searchBox.attr('placeholder', 'Search briks...');
            }
        });
    });
</script>






</body>
</html>
