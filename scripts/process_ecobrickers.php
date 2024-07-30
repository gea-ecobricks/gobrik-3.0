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

<!-- Part 1: Control Buttons and Instructions -->
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

<!-- Part 2: Display Latest Ecobrickers -->
<div id="ecobrick-being-processed">
    <div id="ecobricks-processed-gallery">
        <?php
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        include '../ecobricks_env.php';
        $conn->set_charset("utf8mb4");

        // SQL query to fetch the latest 10 ecobrickers
        $query = "SELECT first_name, buwana_id, location_full_txt, date_registered, email_addr FROM tb_ecobrickers
                  ORDER BY gobrik_migrated_dt DESC
                  LIMIT 1";

        $result = $conn->query($query);
        ?>

        <h1>Latest Ecobrickers Transferred</h1>
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

<!-- Part 3: Start Migration Button -->
<h2>Start Ecobricker Migration from Knack</h2>
<form id="knack-migration-form" method="POST" action="">
    <button type="submit">Start Migration</button>
</form>

<!-- Part 4: Process and Upload Data to GoBrik Database -->
<div id="knack-response">
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $api_key = "360aa2b0-af19-11e8-bd38-41d9fc3da0cf";
        $app_id = "5b8c28c2a1152679c209ce0c";
        $object_id = "object_14";

        $filters = [
            'match' => 'and',
            'rules' => [
                [
                    'field' => 'field_2525',
                    'operator' => 'is not',
                    'value' => 'yes'
                ]
            ]
        ];

        $url = "https://api.knack.com/v1/objects/$object_id/records?filters=" . urlencode(json_encode($filters)) . "&sort_field=field_261&sort_order=asc";

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
                    $record_id = $record['id'];
                    $legacy_gobrik_user_id = $record['field_261'];
                    $first_name = strtolower($record['field_102_raw']['first']);
                    $last_name = $record['field_102_raw']['last'];
                    $full_name = $record['field_102_raw']['full'];
                    $user_roles = $record['field_106'];
                    $gea_status = $record['field_273'];
                    $community = $record['field_125'];
                    $email_addr = $record['field_103'];
                    $date_registered = $record['field_294'];
                    $phone_no = $record['field_421'];
                    $ecobricks_made = $record['field_141'];
                    $brk_balance = $record['field_400'];
                    $aes_balance = $record['field_1747'];
                    $aes_purchased = $record['field_2000'];
                    $country_txt = $record['field_326'];
                    $region_txt = $record['field_359'];
                    $city_txt = $record['field_342'];
                    $location_full_txt = $record['field_429'];
                    $household_txt = $record['field_2028'];
                    $gender = $record['field_283'];
                    $personal_catalyst = $record['field_1676'];
                    $trainer_availability = $record['field_430'];
                    $pronouns = $record['field_552'];
                    $household_generation = $record['field_2231'];
                    $country_per_capita_consumption = $record['field_2106'];
                    $my_consumption_estimate = $record['field_2221'];
                    $household_members = $record['field_1851'];
                    $household = $record['field_2038'];

                    // Manually set fields
                    $buwana_activated = 0;
                    $gobrik_migrated = 1;
                    $account_notes = 'migrated from knack gobrik on July 29th, 2024';
                    $gobrik_migrated_dt = date('Y-m-d H:i:s');

                    // Insert the data into tb_ecobrickers
                    $sql_insert = "INSERT INTO tb_ecobrickers (maker_id, legacy_gobrik_user_id, first_name, last_name, full_name, user_roles, gea_status, community, email_addr, date_registered, phone_no, ecobricks_made, brk_balance, aes_balance, aes_purchased, country_txt, region_txt, city_txt, location_full_txt, household_txt, gender, personal_catalyst, trainer_availability, pronouns, household_generation, country_per_capita_consumption, my_consumption_estimate, household_members, household, buwana_activated, gobrik_migrated, account_notes, gobrik_migrated_dt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt_insert = $conn->prepare($sql_insert);
                    if ($stmt_insert) {
                        $stmt_insert->bind_param(
                            'sisssssssssidsssssssssssdddiiiss',
                            $record_id,
                            $legacy_gobrik_user_id,
                            $first_name,
                            $last_name,
                            $full_name,
                            $user_roles,
                            $gea_status,
                            $community,
                            $email_addr,
                            $date_registered,
                            $phone_no,
                            $ecobricks_made,
                            $brk_balance,
                            $aes_balance,
                            $aes_purchased,
                            $country_txt,
                            $region_txt,
                            $city_txt,
                            $location_full_txt,
                            $household_txt,
                            $gender,
                            $personal_catalyst,
                            $trainer_availability,
                            $pronouns,
                            $household_generation,
                            $country_per_capita_consumption,
                            $my_consumption_estimate,
                            $household_members,
                            $household,
                            $buwana_activated,
                            $gobrik_migrated,
                            $account_notes,
                            $gobrik_migrated_dt
                        );

                        if ($stmt_insert->execute()) {
                            echo '<p>Ecobricker inserted into GoBrik database!</p>';
                        } else {
                            echo '<p>Error inserting data: ' . $stmt_insert->error . '</p>';
                        }

                        $stmt_insert->close();
                    } else {
                        echo '<p>Error preparing statement: ' . $conn->error . '</p>';
                    }
                }
            } else {
                echo '<p>No ecobrickers found that match the criteria.</p>';
            }
        }
        curl_close($ch);
    }
    ?>
</div>




</body>
</html>
