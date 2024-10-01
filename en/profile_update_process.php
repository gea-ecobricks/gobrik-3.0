<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../buwanaconn_env.php'; // Buwana database credentials

// Check if the user is logged in
if (!isset($_SESSION['buwana_id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'User is not logged in.']);
    exit();
}

$buwana_id = $_SESSION['buwana_id'];

// Check if all required fields are present
if (!isset($_POST['first_name'], $_POST['last_name'], $_POST['country_id'], $_POST['language_id'], $_POST['birth_date'], $_POST['continent_code'], $_POST['watershed_id'], $_POST['community_id'], $_POST['location_full'], $_POST['latitude'], $_POST['longitude'], $_POST['location_watershed'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Missing required fields.']);
    exit();
}

// Sanitize and validate input fields
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$country_id = (int)$_POST['country_id'];
$language_id = trim($_POST['language_id']); // Treat language_id as a string
$birth_date = $_POST['birth_date'];
$continent_code = trim($_POST['continent_code']); // Sanitize continent_code
$watershed_id = (int)$_POST['watershed_id']; // Sanitize watershed_id
$community_id = (int)$_POST['community_id']; // Sanitize community_id
$location_full = trim($_POST['location_full']); // Sanitize location_full
$latitude = trim($_POST['latitude']); // Sanitize latitude
$longitude = trim($_POST['longitude']); // Sanitize longitude
$location_watershed = trim($_POST['location_watershed']); // Sanitize location_watershed

// Update the user's profile in the Buwana database
$sql_update = "UPDATE users_tb
               SET first_name = ?, last_name = ?, country_id = ?, languages_id = ?, birth_date = ?,
                   continent_code = ?, watershed_id = ?, community_id = ?, location_full = ?,
                   latitude = ?, longitude = ?, location_watershed = ?
               WHERE buwana_id = ?";

$stmt_update = $buwana_conn->prepare($sql_update);

if ($stmt_update) {
    // Bind parameters
    $stmt_update->bind_param('ssisssissssss',
        $first_name, $last_name, $country_id, $language_id, $birth_date,
        $continent_code, $watershed_id, $community_id, $location_full,
        $latitude, $longitude, $location_watershed, $buwana_id
    );

    // Execute the statement
    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'succeeded']);
    } else {
        echo json_encode(['status' => 'failed', 'message' => 'Failed to execute update query: ' . $stmt_update->error]);
    }
    $stmt_update->close();
} else {
    echo json_encode(['status' => 'failed', 'message' => 'Failed to prepare update statement: ' . $buwana_conn->error]);
}

// Close the database connection
$buwana_conn->close();
exit();
?>
