
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

<p>We're migrating ecobrickers from our old server to our new. Help us out by running this page on your computer or phone. Just keep it up. If it crashes or stops, reload the page. Thank you! 🙏</p>


<div id="ecobrickers-being-processed">
    <div id="ecobrickers-processed-gallery">
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
        $field_check = "field_2525";

        $url = "https://api.knack.com/v1/objects/$object_id/records";
        $headers = [
            "X-Knack-Application-ID: $app_id",
            "X-Knack-REST-API-Key: $api_key",
            "Content-Type: application/json"
        ];

        $params = [
            'filters' => [
                [
                    'field' => 'field_2525',
                    'operator' => 'is',
                    'value' => 'no'
                ],
                [
                    'field' => 'field_103',
                    'operator' => 'is',
                    'value' => $email
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

        $response = curl_exec($ch);
        if ($response === false) {
            echo '<p>Error retrieving data: ' . curl_error($ch) . '</p>';
        } else {
            $json_response = json_decode($response, true);
            if (!empty($json_response['records'])) {
                echo '<pre>' . json_encode($json_response['records'], JSON_PRETTY_PRINT) . '</pre>';
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




