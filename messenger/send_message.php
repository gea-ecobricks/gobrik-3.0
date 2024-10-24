<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log("Starting message send process");

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the inputs from the POST request
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
$sender_id = isset($_POST['sender_id']) ? intval($_POST['sender_id']) : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

$response = [];

// Check if the required data is present
if ($conversation_id > 0 && $sender_id > 0 && (!empty($content) || isset($_FILES['image']))) {
    $buwana_conn->begin_transaction();
    try {
        // Insert the new message into the messages table
        $stmt = $buwana_conn->prepare("INSERT INTO messages_tb (conversation_id, sender_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $conversation_id, $sender_id, $content);
        $stmt->execute();
        $message_id = $buwana_conn->insert_id;
        $stmt->close();
        error_log("Message ID: $message_id created for Conversation ID: $conversation_id");

        // Handle file upload if an image is included
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing image upload for message ID: $message_id");

            // Prepare data for the upload function
            $_POST['user_id'] = $sender_id;
            $_POST['message_id'] = $message_id;
            $_POST['conversation_id'] = $conversation_id;
            $_FILES['image'] = $_FILES['image'];

            // Capture the output of the upload_image_attachment.php script
            ob_start();
            include '../messenger/upload_image_attachment.php';
            $upload_response = json_decode(ob_get_clean(), true);

            // Check the response from the upload script
            if (!isset($upload_response['status']) || $upload_response['status'] !== 'success') {
                throw new Exception($upload_response['message'] ?? 'Unknown error during image upload.');
            }
        }

        // Update the last message ID and timestamp in the conversation
        $stmt = $buwana_conn->prepare("UPDATE conversations_tb SET last_message_id = ?, updated_at = NOW(), size_in_bytes = size_in_bytes + LENGTH(?) WHERE conversation_id = ?");
        $stmt->bind_param("isi", $message_id, $content, $conversation_id);
        $stmt->execute();
        $stmt->close();

        // Update message status for participants
        $stmt = $buwana_conn->prepare("SELECT buwana_id FROM participants_tb WHERE conversation_id = ?");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();
        $stmt->bind_result($buwana_id);
        $participants = [];
        while ($stmt->fetch()) {
            $participants[] = $buwana_id;
        }
        $stmt->close();

        $status_stmt = $buwana_conn->prepare("INSERT INTO message_status_tb (message_id, buwana_id, status, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = NOW()");
        foreach ($participants as $recipient_id) {
            $status = ($recipient_id == $sender_id) ? 'read' : 'sending';
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
        $buwana_conn->rollback();
        $response = [
            "status" => "error",
            "message" => "An error occurred while sending the message: " . $e->getMessage()
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'conversation_id', 'sender_id', and 'content' or an image are required."
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
$buwana_conn->close();
?>
