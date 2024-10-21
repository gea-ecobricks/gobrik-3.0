<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the user ID from the GET request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$response = [];

// Validate the user ID
if ($user_id > 0) {
    try {
        // Prepare the SQL query to retrieve conversations for the user
        // Prepare the SQL query to retrieve conversations for the user
$stmt = $buwana_conn->prepare("
    SELECT c.conversation_id,
           c.last_message_id,
           c.updated_at,
           m.content AS last_message,
           m.created_at AS last_message_time,
           GROUP_CONCAT(u.first_name SEPARATOR ', ') AS other_participants
    FROM conversations_tb c
    LEFT JOIN messages_tb m ON c.last_message_id = m.message_id
    JOIN participants_tb p ON c.conversation_id = p.conversation_id
    JOIN users_tb u ON u.buwana_id = p.buwana_id
    WHERE p.conversation_id = c.conversation_id AND p.buwana_id != ?
    GROUP BY c.conversation_id
    ORDER BY c.updated_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Bind the result fields
$stmt->bind_result($conversation_id, $last_message_id, $updated_at, $last_message, $last_message_time, $other_participants);

// Fetch all conversations into an associative array
$conversations = [];
while ($stmt->fetch()) {
    $conversations[] = [
        "conversation_id" => $conversation_id,
        "last_message_id" => $last_message_id,
        "last_message" => $last_message,
        "last_message_time" => $last_message_time,
        "other_participants" => $other_participants,
        "updated_at" => $updated_at
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
