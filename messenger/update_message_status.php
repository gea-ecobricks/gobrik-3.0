<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve inputs from the POST request
$message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

$response = [];

// Validate input data
if ($message_id > 0 && $user_id > 0 && in_array($status, ['read', 'delivered', 'undelivered', 'error'])) {
    try {
        // Prepare the SQL query to update the message status
        $stmt = $buwana_conn->prepare("
            UPDATE message_status_tb
            SET status = ?, updated_at = NOW()
            WHERE message_id = ? AND buwana_id = ?
        ");
        $stmt->bind_param("sii", $status, $message_id, $user_id);
        $stmt->execute();

        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            $response = [
                "status" => "success",
                "message" => "Message status updated successfully."
            ];
        } else {
            // If no rows were affected, it might mean the status entry does not exist
            $response = [
                "status" => "error",
                "message" => "No status updated. The message or user may not exist."
            ];
        }

        // Close the statement
        $stmt->close();
    } catch (Exception $e) {
        $response = [
            "status" => "error",
            "message" => "An error occurred while updating the message status: " . $e->getMessage()
        ];
    }
} else {
    // Invalid input data
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'message_id', 'user_id', and a valid 'status' are required."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
