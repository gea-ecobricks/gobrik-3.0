<?php
// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the user ID from the GET request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$response = [];

// Validate the user ID
if ($user_id > 0) {
    try {
        // Prepare the SQL query to retrieve conversations for the user
        $stmt = $buwana_conn->prepare("
            SELECT c.conversation_id,
                   c.last_message_id,
                   c.updated_at,
                   m.content AS last_message,
                   m.created_at AS last_message_time,
                   u.first_name AS last_message_sender_name,
                   u.buwana_id AS last_message_sender_id
            FROM conversations_tb c
            LEFT JOIN messages_tb m ON c.last_message_id = m.message_id
            LEFT JOIN users_tb u ON m.sender_id = u.buwana_id
            JOIN participants_tb p ON c.conversation_id = p.conversation_id
            WHERE p.buwana_id = ?
            ORDER BY c.updated_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all conversations as an associative array
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = [
                "conversation_id" => $row['conversation_id'],
                "last_message_id" => $row['last_message_id'],
                "last_message" => $row['last_message'],
                "last_message_time" => $row['last_message_time'],
                "last_message_sender_name" => $row['last_message_sender_name'],
                "last_message_sender_id" => $row['last_message_sender_id'],
                "updated_at" => $row['updated_at']
            ];
        }

        // Close the statement
        $stmt->close();

        // Return the conversation data
        $response = [
            "status" => "success",
            "conversations" => $conversations
        ];
    } catch (Exception $e) {
        $response = [
            "status" => "error",
            "message" => "An error occurred while retrieving conversations: " . $e->getMessage()
        ];
    }
} else {
    // Invalid user ID
    $response = [
        "status" => "error",
        "message" => "Invalid user ID."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
