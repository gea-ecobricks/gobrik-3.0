<?php
require_once '../buwanaconn_env.php'; // Include database connection

// Retrieve continent code from AJAX request
$continent_code = filter_input(INPUT_POST, 'continent_code', FILTER_SANITIZE_STRING);

// Prepare SQL query to fetch countries
$sql_countries = "SELECT country_id, country_name FROM countries_tb WHERE continent_code = ? ORDER BY country_name";
$stmt = $buwana_conn->prepare($sql_countries);

if ($stmt) {
    $stmt->bind_param('s', $continent_code);
    $stmt->execute();
    $result = $stmt->get_result();

    $countries = [];
    while ($row = $result->fetch_assoc()) {
        $countries[] = $row;
    }

    // Return the countries as a JSON response
    echo json_encode($countries);

    $stmt->close();
} else {
    echo json_encode([]);
}

// Close the database connection
$buwana_conn->close();
?>
