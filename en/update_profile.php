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
if (!isset($_POST['first_name'], $_POST['last_name'], $_POST['country_id'], $_POST['language_id'], $_POST['birth_date'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Missing required fields.']);
    exit();
}

// Sanitize and validate input fields
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$country_id = (int)$_POST['country_id'];
$language_id = (int)$_POST['language_id'];
$birth_date = $_POST['birth_date'];

// Update the user's profile in the Buwana database
$sql_update = "UPDATE users_tb SET first_name = ?, last_name = ?, country_id = ?, languages_id = ?, birth_date = ? WHERE buwana_id = ?";
$stmt_update = $buwana_conn->prepare($sql_update);

if ($stmt_update) {
    $stmt_update->bind_param('ssiisi', $first_name, $last_name, $country_id, $language_id, $birth_date, $buwana_id);

    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'succeeded']);
    } else {
        echo json_encode(['status' => 'failed', 'message' => 'Failed to execute update query.']);
    }
    $stmt_update->close();
} else {
    echo json_encode(['status' => 'failed', 'message' => 'Failed to prepare update statement.']);
}

$buwana_conn->close();
exit();
?>
