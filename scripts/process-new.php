<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ᐉ Help the Great GoBrik Migration</title>
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
        .log {
            margin: 20px 0;
            padding: 10px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="control-buttons">
    <button class="button" onclick="stopProcessing()">Stop Processing</button>
    <button class="button" onclick="startProcessing()">ᐉ Start Processing</button>
</div>
<p>We're migrating ecobricks from our old server to our new. Help us out by running this page on your computer or phone. Just keep it up. If it crashes or stops, reload the page. Thank you!</p>

<div id="ecobrick-being-processed">
    <div id="ecobricks-processed-gallery">
        <?php
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        include '../ecobricks_env.php';
        $conn->set_charset("utf8mb4");

        $query = "SELECT serial_no, ecobrick_thumb_photo_url FROM tb_ecobricks
                  WHERE status = 'authenticated'
                  ORDER BY date_published_ts DESC
                  LIMIT 18";

        $result = $conn->query($query);
        ?>

        <h1>Latest Ecobrick Imports</h1>
        <div class="gallery">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $serial_no = $row['serial_no'];
                    $thumb_url = $row['ecobrick_thumb_photo_url'];
                    echo "<a href='https://ecobricks.org/en/details-ecobrick-page.php?serial_no=$serial_no' target='_blank'>
                    <img src='$thumb_url' alt='Ecobrick $serial_no' title='Ecobrick $serial_no'>
                  </a>";
                }
            } else {
                echo "<p>No ecobricks found.</p>";
            }
            ?>
        </div>
        <?php $conn->close(); ?>
    </div>
</div>

<div id="knack-response"></div>

<script>


    function processEcobricker() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'process_ecobricker_new.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                const logDiv = document.createElement('div');
                logDiv.className = 'log';

                if (response.success) {
                    logDiv.innerHTML = response.success;
                } else {
                    logDiv.innerHTML = response.error;
                }

                document.getElementById('knack-response').appendChild(logDiv);
                processEcobricker();
            }
        };

        xhr.send();
    }

    document.addEventListener('DOMContentLoaded', function() {
        processEcobricker();
    });
</script>
</body>
</html>
