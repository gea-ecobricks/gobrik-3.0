
<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.43';
$page = 'newest-briks';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));
$is_logged_in = isLoggedIn(); // Check if the user is logged in using the helper function


// Check if the user is logged in
if (isLoggedIn()) {
    $buwana_id = $_SESSION['buwana_id'];
        // Include database connection
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    $buwana_conn->close();  // Close the database connection
} else {

}

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

// SQL query to fetch the 12 most recent ecobricks, excluding those with status "not ready"
$sql_recent = "
    SELECT ecobrick_thumb_photo_url, ecobrick_full_photo_url, weight_g, weight_g / 1000 AS weight_kg, volume_ml,
           density, date_logged_ts, location_full, location_watershed, ecobricker_maker, serial_no, status
    FROM tb_ecobricks
    WHERE status != 'not ready'
    ORDER BY date_logged_ts DESC
    LIMIT 12";



$result_recent = $gobrik_conn->query($sql_recent);

$recent_ecobricks = [];
if ($result_recent->num_rows > 0) {
    while ($row = $result_recent->fetch_assoc()) {
        // Extract parts of location_full
        $location_parts = explode(',', $row['location_full']);
        $location_parts = array_map('trim', $location_parts); // Trim whitespace from each part

        // Get the last and third-last elements of the location
        $location_last = $location_parts[count($location_parts) - 1] ?? '';
        $location_third_last = $location_parts[count($location_parts) - 3] ?? '';

        // Build the location_brik string
        $location_brik = $location_third_last . ', ' . $location_last;

        // If location_watershed is not null or empty, prepend it
        if (!empty($row['location_watershed'])) {
            $location_brik = $row['location_watershed'] . ', ' . $location_brik;
        }

        // Add the processed data to the recent_ecobricks array
        $recent_ecobricks[] = [
            'ecobrick_thumb_photo_url' => $row['ecobrick_thumb_photo_url'],
            'ecobrick_full_photo_url' => $row['ecobrick_full_photo_url'],
            'weight_g' => $row['weight_g'],
            'weight_kg' => $row['weight_kg'],
            'volume_ml' => $row['volume_ml'],
            'density' => $row['density'],
            'date_logged_ts' => $row['date_logged_ts'],
            'location_brik' => $location_brik,
            'ecobricker_maker' => $row['ecobricker_maker'],
            'serial_no' => $row['serial_no'],
            'status' => $row['status'],
        ];
    }
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
    <h2>The Latest Ecobricks</h2>
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
                "url": "newest-briks.php",
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
