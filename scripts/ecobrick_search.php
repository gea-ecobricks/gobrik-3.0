<?php
include '../gobrik_env.php'; // Include your GoBrik database credentials

$query = isset($_GET['query']) ? $_GET['query'] : '';

$conn2 = new mysqli($servername, $username, $password, $dbname);

if ($conn2->connect_error) {
    die("Connection failed: " . $conn2->connect_error);
}

$sql = "SELECT ecobrick_thumb_photo_url, weight_g, location_full, ecobricker_maker, serial_no
        FROM tb_ecobricks
        WHERE LOWER(serial_no) LIKE ? OR LOWER(location_full) LIKE ? OR LOWER(ecobricker_maker) LIKE ?
        ORDER BY date_logged_ts DESC
        LIMIT 20";

$stmt = $conn2->prepare($sql);
$search_param = '%' . $query . '%';
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

$ecobricks = [];
while ($row = $result->fetch_assoc()) {
    $ecobricks[] = $row;
}

$stmt->close();
$conn2->close();

header('Content-Type: application/json');
echo json_encode($ecobricks);
?>
