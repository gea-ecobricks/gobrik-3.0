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
if (!isset($_POST['first_name'], $_POST['last_name'], $_POST['country_id'], $_POST['language_id'], $_POST['birth_date'], $_POST['continent_code'], $_POST['watershed_id'])) {
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

// Update the user's profile in the Buwana database
$sql_update = "UPDATE users_tb SET first_name = ?, last_name = ?, country_id = ?, languages_id = ?, birth_date = ?, continent_code = ?, watershed_id = ? WHERE buwana_id = ?";
$stmt_update = $buwana_conn->prepare($sql_update);

// Debugging output to check watershed_id value
error_log("Debug: Updating user $buwana_id with watershed_id: " . $watershed_id);

// After binding and before executing, check for errors
if ($stmt_update->execute()) {
    echo json_encode(['status' => 'succeeded']);
} else {
    // Log the exact error message for debugging
    error_log('Failed to execute update query: ' . $stmt_update->error);
    echo json_encode(['status' => 'failed', 'message' => 'Failed to execute update query: ' . $stmt_update->error]);
}



if ($stmt_update) {
    // Bind parameters
    $stmt_update->bind_param('ssisssii', $first_name, $last_name, $country_id, $language_id, $birth_date, $continent_code, $watershed_id, $buwana_id);

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
