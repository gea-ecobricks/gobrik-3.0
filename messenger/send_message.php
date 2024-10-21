<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the inputs from the POST request
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
$sender_id = isset($_POST['sender_id']) ? intval($_POST['sender_id']) : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

$response = [];

// Check if the required data is present
if ($conversation_id > 0 && $sender_id > 0 && !empty($content)) {
    // Begin a transaction to ensure data integrity
    $buwana_conn->begin_transaction();
    try {
        // Insert the new message into the messages table
        $stmt = $buwana_conn->prepare("INSERT INTO messages_tb (conversation_id, sender_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $conversation_id, $sender_id, $content);
        $stmt->execute();
        $message_id = $buwana_conn->insert_id;
        $stmt->close();

        // Update the last message ID and timestamp in the conversation
        $stmt = $buwana_conn->prepare("UPDATE conversations_tb SET last_message_id = ?, updated_at = NOW(), size_in_bytes = size_in_bytes + LENGTH(?) WHERE conversation_id = ?");
        $stmt->bind_param("isi", $message_id, $content, $conversation_id);
        $stmt->execute();
        $stmt->close();

        // Retrieve all participants of the conversation to update their message status
        $stmt = $buwana_conn->prepare("SELECT buwana_id FROM participants_tb WHERE conversation_id = ?");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $participants = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Insert or update the message status for each participant
        $status_stmt = $buwana_conn->prepare("INSERT INTO message_status_tb (message_id, buwana_id, status, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = NOW()");
        foreach ($participants as $participant) {
            $recipient_id = $participant['buwana_id'];
            $status = ($recipient_id == $sender_id) ? 'read' : 'sending'; // Set 'read' for the sender immediately
            $status_stmt->bind_param("iis", $message_id, $recipient_id, $status);
            $status_stmt->execute();
        }
        $status_stmt->close();

        // Commit the transaction
        $buwana_conn->commit();

        // Success response
        $response = [
            "status" => "success",
            "message_id" => $message_id
        ];
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $buwana_conn->rollback();

        // Error response
        $response = [
            "status" => "error",
            "message" => "An error occurred while sending the message: " . $e->getMessage()
        ];
    }
} else {
    // Invalid input data
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'conversation_id', 'sender_id', and 'content' are required."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
