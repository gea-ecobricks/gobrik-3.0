<?php
require_once '../buwanaconn_env.php'; // Include database connection

// Retrieve continent code from AJAX request
$continent_code = filter_input(INPUT_POST, 'continent_code', FILTER_SANITIZE_STRING);

// Prepare SQL query to fetch watersheds
$sql_watersheds = "SELECT watershed_id, watershed_name FROM watersheds_tb WHERE continent_code = ? ORDER BY watershed_name";
$stmt = $buwana_conn->prepare($sql_watersheds);

if ($stmt) {
    $stmt->bind_param('s', $continent_code);
    $stmt->execute();
    $result = $stmt->get_result();

    $watersheds = [];
    while ($row = $result->fetch_assoc()) {
        $watersheds[] = $row;
    }

    // Return the watersheds as a JSON response
    echo json_encode($watersheds);

    $stmt->close();
} else {
    echo json_encode([]);
}

// Close the database connection
$buwana_conn->close();
?>
