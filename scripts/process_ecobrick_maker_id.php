
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ᐉ Maker ID Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ddd;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .gallery img {
            max-width: 150px;
            height: auto;
            cursor: pointer;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .gallery a {
            text-decoration: none;
        }

        .button {
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<p>We're updating ecobrick records with the maker id</p>

<div id="ecobrick-being-processed">

    <div id="ecobricks-processed-gallery">



       <?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../ecobricks_env.php';
$conn->set_charset("utf8mb4");

// SQL query to fetch the latest 18 authenticated ecobricks whose maker_id is not '000000000000000000000000'
$query = "SELECT serial_no, ecobrick_thumb_photo_url FROM tb_ecobricks
          WHERE status = 'authenticated' AND maker_id != '000000000000000000000000'
          ORDER BY date_published_ts DESC
          LIMIT 18";

$result = $conn->query($query);
?>

<body>
<h1>Latest Ecobrick Imports</h1>
<div class="gallery">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $serial_no = $row['serial_no'];
            $thumb_url = $row['ecobrick_thumb_photo_url'];
            echo "<a href='https://ecobricks.org/en/details-ecobrick-page.php?serial_no=$serial_no' target='_blank'>
                    <img src='https://ecobricks.org/$thumb_url' alt='Ecobrick $serial_no' title='Ecobrick $serial_no'>
                  </a>";
        }
    } else {
        echo "<p>No ecobricks found.</p>";
    }
    ?>
</div>
</body>



        <?php
        $conn->close();
        ?>
    </div>

 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Ecobrick</title>
    <style>
        .button {
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php
// PART 2 of the code
// Go to knack database and get ecobrick to extract maker ID from.

// Knack API settings
$api_key = "360aa2b0-af19-11e8-bd38-41d9fc3da0cf";
$app_id = "5b8c28c2a1152679c209ce0c";

// Create connection to the database
$servername = "your_server_name"; // Ensure this is defined correctly
$username = "your_username"; // Ensure this is defined correctly
$password = "your_password"; // Ensure this is defined correctly
$dbname = "your_database_name"; // Ensure this is defined correctly

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<script>confirm('Connection failed: " . $conn->connect_error . ". Do you want to proceed to the next ecobrick?'); window.location.href = 'process_ecobricks.php';</script>");
}
echo "<p>Connected to Knack server...</p>";

// Prepare filters to get records with the transfer status field field_2526 set to "No" and field_534 containing "Authenticated"
$filters = [
    'match' => 'and',
    'rules' => [
        [
            'field' => 'field_2526',
            'operator' => 'is not',
            'value' => 'Yes'
        ],
        [
            'field' => 'field_534',
            'operator' => 'contains',
            'value' => 'Authenticated'
        ]
    ]
];

// Prepare the API request to retrieve ecobrick record...
$url = "https://api.knack.com/v1/objects/object_2/records?filters=" . urlencode(json_encode($filters)) . "&sort_field=field_73&sort_order=desc&rows_per_page=1";

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-Knack-Application-Id: $app_id",
    "X-Knack-REST-API-Key: $api_key"
]);

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    $error = curl_error($ch);
    echo "<script>confirm('Error fetching data from Knack API: " . addslashes($error) . ". Do you want to proceed to the next ecobrick?'); window.location.href = 'process_ecobricks.php';</script>";
    curl_close($ch);
    exit;
}
echo "<h3>Starting to retrieve ecobrick data</h3>";

// Close cURL session
curl_close($ch);

// Add console logging to confirm API access and response
echo "<script>console.log('Knack API Request URL: " . addslashes($url) . "');</script>";
echo "<script>console.log('Knack API Response: " . addslashes($response) . "');</script>";

$data = json_decode($response, true);

$record_found = false;
$record_details = "";

// Retrieval section:
if (isset($data['records']) && count($data['records']) > 0) {
    $record = $data['records'][0];
    $ecobrick_unique_id = $record['field_73'];
    $knack_record_id = $record['id'];  // Assuming the record ID is stored in 'id'
    $record_found = true;
    $record_details = $record;

    // Extract the maker_record_id from field_335
    $maker_record_id = "";
    if (isset($record['field_335_raw'][0]['id'])) {
        $maker_record_id = $record['field_335_raw'][0]['id'];
    }

    // Displaying the serial number and message
    echo "<h3>Now processing ecobrick $ecobrick_unique_id ...</h3>";
    echo "<p>Maker record ID was retrieved! $maker_record_id</p>";
} else {
    echo "<p>No ecobrick records found with the provided criteria.</p>";
}

// Display button to restart the script
echo '<button class="button" onclick="window.location.href=\'process_ecobricks.php\'">Restart Processing</button>';
?>

</body>
</html>




</body>
</html>
