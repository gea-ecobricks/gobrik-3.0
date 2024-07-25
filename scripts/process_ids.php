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
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function processNextEcobrick() {
                $.ajax({
                    url: 'process_ecobrick_maker_id.php',
                    method: 'POST',
                    success: function(response) {
                        $('#process-status').html(response);
                        setTimeout(processNextEcobrick, 1000);  // Adjust the timeout as needed
                    },
                    error: function() {
                        $('#process-status').html('<p>Error processing the next ecobrick. Please try again.</p>');
                    }
                });
            }

            processNextEcobrick();
        });
    </script>
</head>
<body>

<h1>Ecobricker Migration</h1>
<p>We're moving ecobricker accounts to the new GoBrik3 database.  First step is to connect all the ecobricks in the brikchain with these accounts.  To do so we're updating all ecobricks with their maker's record ID.</p>

<div id="process-status">
    <?php
    include '../ecobricks_env.php';

    // Knack API settings
    $api_key = "360aa2b0-af19-11e8-bd38-41d9fc3da0cf";
    $app_id = "5b8c28c2a1152679c209ce0c";
    $servername = "localhost";
    $username = "ecobricks_brikchain_viewer";
    $password = "desperate-like-the-Dawn";
    $dbname = "ecobricks_gobrik_msql_db";

    // Create connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("<script>confirm('Connection failed: " . $conn->connect_error . ". Do you want to proceed to the next ecobrick?'); window.location.href = 'process_ecobricks.php';</script>");
    }

    // Prepare filters
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

    // Prepare the API request to get total count and the next ecobrick record
    $url_count = "https://api.knack.com/v1/objects/object_2/records?filters=" . urlencode(json_encode($filters)) . "&rows_per_page=1";
    $url_record = $url_count . "&sort_field=field_73&sort_order=desc&rows_per_page=1";

    // Function to perform cURL requests
    function fetchCurl($url, $headers) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    // Fetch total count of records
    $headers = [
        "X-Knack-Application-Id: $app_id",
        "X-Knack-REST-API-Key: $api_key"
    ];
    $response_count = fetchCurl($url_count, $headers);
    $data_count = json_decode($response_count, true);
    $total_records = $data_count['total_records'];

    // Fetch the next ecobrick record
    $response_record = fetchCurl($url_record, $headers);
    $data_record = json_decode($response_record, true);

    if (isset($data_record['records']) && count($data_record['records']) > 0) {
        $record = $data_record['records'][0];
        $ecobrick_unique_id = $record['field_73'];
        $knack_record_id = $record['id'];
        $maker_record_id = isset($record['field_335_raw'][0]['id']) ? $record['field_335_raw'][0]['id'] : "";

        echo "<h3>Now processing ecobrick $ecobrick_unique_id ...</h3>";
        echo "<p>Maker record ID was retrieved! $maker_record_id</p>";

        // Update Knack record
        $update_data = ['field_2526' => 'Yes'];
        $update_url = "https://api.knack.com/v1/objects/object_2/records/$knack_record_id";
        $update_response = fetchCurl($update_url, array_merge($headers, ["Content-Type: application/json"]), json_encode($update_data));

        if ($update_response === false) {
            echo "<p>Error updating Knack record.</p>";
        } else {
            echo "<p>Successfully updated Knack record with field_2526 set to 'Yes'.</p>";
        }

        // Update MySQL database
        $sql_update_ecobrick = "UPDATE tb_ecobricks SET maker_id = ? WHERE serial_no = ?";
        $stmt_update_ecobrick = $conn->prepare($sql_update_ecobrick);
        if ($stmt_update_ecobrick) {
            $stmt_update_ecobrick->bind_param("ss", $maker_record_id, $ecobrick_unique_id);
            if ($stmt_update_ecobrick->execute()) {
                echo "<p>Successfully updated ecobrick with serial number $ecobrick_unique_id. Maker ID set to $maker_record_id.</p>";
            } else {
                echo "<p>Error updating ecobrick: " . $stmt_update_ecobrick->error . "</p>";
            }
            $stmt_update_ecobrick->close();
        } else {
            echo "<p>Error preparing statement: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>No ecobrick records found with the provided criteria.</p>";
    }

    echo "<p>There are now $total_records ecobrick records left to process.</p>";
    $conn->close();
    ?>
</div>

</body>
</html>
