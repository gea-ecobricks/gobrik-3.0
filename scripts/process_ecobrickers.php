<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ecobricker Migration Form</title>
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
        .gallery div {
            background: white;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
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
<div class="control-buttons">
    <button class="button" onclick="stopProcessing()">Stop Processing</button>
    <button class="button" onclick="startProcessing()">ᐉ Start Processing</button>
</div>
<p>We're migrating ecobricks from our old server to our new. Help us out by running this page on your computer or phone. Just keep it up. If it crashes or stops, reload the page. Thank you! 🙏</p>

<script>
    function stopProcessing() {
        if (confirm('Are you sure you want to stop the processing?')) {
            window.location.href = 'process_ecobrick.php?action=stop';
        }
    }

    function startProcessing() {
        if (confirm('Are you sure you want to start the processing?')) {
            window.location.href = 'process_ecobrick.php?action=start';
        }
    }
</script>

<div id="ecobrick-being-processed">
    <div id="ecobricks-processed-gallery">
        <?php
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        include '../ecobricks_env.php';
        $conn->set_charset("utf8mb4");

        // SQL query to fetch the latest 10 ecobrickers
        $query = "SELECT first_name, buwana_id, location_full_txt, date_registered, email_addr FROM ecobricker_live_tb
                  ORDER BY date_registered DESC
                  LIMIT 10";

        $result = $conn->query($query);
        ?>

        <body>
        <h1>Latest Ecobrickers Added</h1>
        <div class="gallery">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $first_name = htmlspecialchars($row['first_name']);
                    $buwana_id = htmlspecialchars($row['buwana_id']);
                    $location = htmlspecialchars($row['location_full_txt']);
                    $date_registered = htmlspecialchars($row['date_registered']);
                    $email = htmlspecialchars($row['email_addr']);

                    echo "<div>
                        <p><strong>Name:</strong> $first_name</p>
                        <p><strong>Buwana ID:</strong> $buwana_id</p>
                        <p><strong>Location:</strong> $location</p>
                        <p><strong>Date Registered:</strong> $date_registered</p>
                        <p><strong>Email:</strong> $email</p>
                      </div>";
                }
            } else {
                echo "<p>No ecobrickers found.</p>";
            }
            ?>
        </div>
    </div>
</div>



<!--PART 3-->
<h2>Retrieve Ecobricker Data from Knack</h2>
<form id="knack-search-form" method="POST" action="">
    <label for="email">Enter Ecobricker Email Address:</label>
    <input type="email" id="email" name="email" required>
    <button type="submit">Retrieve</button>
</form>

<div id="knack-response">
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        $api_key = "360aa2b0-af19-11e8-bd38-41d9fc3da0cf";
        $app_id = "5b8c28c2a1152679c209ce0c";
        $object_id = "object_14";

        $filters = [
            'match' => 'and',
            'rules' => [
                [
                    'field' => 'field_103',
                    'operator' => 'is',
                    'value' => $email
                ]
            ]
        ];

        $url = "https://api.knack.com/v1/objects/$object_id/records?filters=" . urlencode(json_encode($filters));

        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Knack-Application-ID: $app_id",
            "X-Knack-REST-API-Key: $api_key",
            "Content-Type: application/json"
        ]);

        // Execute cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if ($response === false) {
            echo '<p>Error retrieving data: ' . curl_error($ch) . '</p>';
        } else {
            // Log the entire JSON response to the console
            echo "<script>console.log('Knack API Response: " . addslashes($response) . "');</script>";

            $json_response = json_decode($response, true);
            if (!empty($json_response['records'])) {
                foreach ($json_response['records'] as $record) {
                    $first_name = $record['field_198'];
                    $email = $record['field_103'];
                    $connected_ecobricks = $record['field_335'];
                    $ecobricker_id = $record['field_261'];

                    echo "<p><strong>First Name:</strong> " . htmlspecialchars($first_name) . "</p>";
                    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
                    echo "<p><strong>Connected Ecobricks:</strong> " . htmlspecialchars($connected_ecobricks) . "</p>";
                    echo "<p><strong>Ecobricker ID:</strong> " . htmlspecialchars($ecobricker_id) . "</p>";
                }
            } else {
                echo '<p>No ecobricker found with the provided email and criteria.</p>';
            }
        }
        curl_close($ch);
    }
    ?>
</div>


</body>
</html>
