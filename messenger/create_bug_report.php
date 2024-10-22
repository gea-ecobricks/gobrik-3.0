<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the inputs from the POST request
$created_by = isset($_POST['created_by']) ? intval($_POST['created_by']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$response = [];

// Check if the required data is present
if ($created_by > 0 && !empty($message)) {
    $buwana_conn->begin_transaction();
    try {
        // Create a new conversation
        $stmt = $buwana_conn->prepare("INSERT INTO conversations_tb (created_by) VALUES (?)");
        $stmt->bind_param("i", $created_by);
        $stmt->execute();
        $conversation_id = $buwana_conn->insert_id;
        $stmt->close();

        // Add participants to the conversation
        $dev_team_ids = [1, 150, 144, 145];
        $stmt = $buwana_conn->prepare("INSERT INTO participants_tb (conversation_id, buwana_id) VALUES (?, ?)");
        foreach ($dev_team_ids as $dev_id) {
            $stmt->bind_param("ii", $conversation_id, $dev_id);
            $stmt->execute();
        }
        $stmt->bind_param("ii", $conversation_id, $created_by);
        $stmt->execute();
        $stmt->close();

        // Insert the user's message
        $stmt = $buwana_conn->prepare("INSERT INTO messages_tb (conversation_id, sender_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $conversation_id, $created_by, $message);
        $stmt->execute();
        $message_id = $buwana_conn->insert_id;
        $stmt->close();

        // Check if an image is included
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $_POST['user_id'] = $created_by;
            $_POST['message_id'] = $message_id;
            $_POST['conversation_id'] = $conversation_id;

            // Include and run the image upload script
            ob_start(); // Buffer the output of the included script
            include '../messenger/upload_image_attachment.php';
            $upload_response = json_decode(ob_get_clean(), true);

            if ($upload_response['status'] !== 'success') {
                throw new Exception($upload_response['message']);
            }
        }

        $buwana_conn->commit();
        $response = ["status" => "success", "message" => "Bug report created successfully.", "conversation_id" => $conversation_id];
    } catch (Exception $e) {
        $buwana_conn->rollback();
        $response = ["status" => "error", "message" => "An error occurred: " . $e->getMessage()];
    }
} else {
    $response = ["status" => "error", "message" => "Invalid input data."];
}

// Output JSON response
header('Content-Type: application/json');
echo json_encode($response);
$buwana_conn->close();
?>
