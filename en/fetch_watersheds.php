<?php
require_once("../buwanaconn_env.php"); // Include the database connection file

header('Content-Type: application/json'); // Set header for JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['continent_code'])) {
    $continent_code = $_POST['continent_code'];

    // Prepare and execute query
    $stmt = $buwana_conn->prepare("SELECT watershed_id, watershed_name FROM watersheds_tb WHERE continent_code = ? ORDER BY watershed_name");
    $stmt->bind_param('s', $continent_code);
    $stmt->execute();
    $result = $stmt->get_result();

    $watersheds = [];
    while ($row = $result->fetch_assoc()) {
        $watersheds[] = $row;
    }

    echo json_encode($watersheds); // Encode the result as JSON
} else {
    // If the request is invalid, return an empty array
    echo json_encode([]);
}
?>
