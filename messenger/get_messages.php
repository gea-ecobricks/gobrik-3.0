<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the conversation ID and user ID from the request
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$response = [];

// Validate the conversation ID and user ID
if ($conversation_id > 0 && $user_id > 0) {
    try {
        // Prepare the SQL query to retrieve messages for the conversation
        $stmt = $buwana_conn->prepare("
    SELECT m.message_id,
           m.sender_id,
           u.first_name AS sender_name,
           m.content,
           m.created_at,
           ms.status AS message_status,
           m.image_url,
           m.thumbnail_url
    FROM messages_tb m
    LEFT JOIN users_tb u ON m.sender_id = u.buwana_id
    LEFT JOIN message_status_tb ms ON ms.message_id = m.message_id AND ms.buwana_id = ?
    WHERE m.conversation_id = ?
    ORDER BY m.created_at ASC
");

$stmt->bind_param("ii", $user_id, $conversation_id);
$stmt->execute();

// Make sure to bind all selected fields here
$stmt->bind_result($message_id, $sender_id, $sender_name, $content, $created_at, $message_status, $image_url, $thumbnail_url);

// Fetch all messages into an associative array
$messages = [];
while ($stmt->fetch()) {
    $messages[] = [
        "message_id" => $message_id,
        "sender_id" => $sender_id,
        "sender_name" => $sender_name,
        "content" => $content,
        "created_at" => $created_at,
        "status" => $message_status,
        "image_url" => $image_url,
        "thumbnail_url" => $thumbnail_url
    ];
}

$stmt->close();


        // Update the last read message ID for the user in the participants_tb
        $update_stmt = $buwana_conn->prepare("
            UPDATE participants_tb
            SET last_read_message_id = (
                SELECT MAX(message_id)
                FROM messages_tb
                WHERE conversation_id = ?
            )
            WHERE buwana_id = ? AND conversation_id = ?
        ");
        $update_stmt->bind_param("iii", $conversation_id, $user_id, $conversation_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Return the message data
        $response = [
            "status" => "success",
            "messages" => $messages
        ];
    } catch (Exception $e) {
        $response = [
            "status" => "error",
            "message" => "An error occurred while retrieving messages: " . $e->getMessage()
        ];
    }
} else {
    // Invalid conversation ID or user ID
    $response = [
        "status" => "error",
        "message" => "Invalid conversation ID or user ID."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
