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

error_log("User ID: $sender_id, Conversation ID: $conversation_id, Content Length: " . strlen($content));

$response = [];

// Check if the required data is present
if ($conversation_id > 0 && $sender_id > 0 && (!empty($content) || isset($_FILES['image']))) {
    // Begin a transaction to ensure data integrity
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

            // Include the script that handles image upload
            require_once '../messenger/upload_image_attachment.php';

            // Prepare data for the upload function
            $_POST['user_id'] = $sender_id;
            $_POST['message_id'] = $message_id;
            $_POST['conversation_id'] = $conversation_id;
            $_FILES['image'] = $_FILES['image']; // Include the image file

            // Call the upload script and handle the response
            ob_start(); // Start output buffering to capture the JSON response
            include '../messenger/upload_image_attachment.php';
            $upload_response = json_decode(ob_get_clean(), true); // Decode the JSON response

            if ($upload_response['status'] !== 'success') {
                throw new Exception($upload_response['message']);
            }
            error_log("Image upload successful for message ID: $message_id");
        }

        // Update the last message ID and timestamp in the conversation
        $stmt = $buwana_conn->prepare("UPDATE conversations_tb SET last_message_id = ?, updated_at = NOW(), size_in_bytes = size_in_bytes + LENGTH(?) WHERE conversation_id = ?");
        $stmt->bind_param("isi", $message_id, $content, $conversation_id);
        $stmt->execute();
        $stmt->close();

        // Retrieve all participants of the conversation to update their message status
        $stmt = $buwana_conn->prepare("SELECT buwana_id FROM participants_tb WHERE conversation_id = ?");
        $stmt->bind_param("i", $conversation_id);
        $stmt->execute();

        // Bind the result field and fetch each participant
        $stmt->bind_result($buwana_id);
        $participants = [];
        while ($stmt->fetch()) {
            $participants[] = $buwana_id;
        }
        $stmt->close();

        // Insert or update the message status for each participant
        $status_stmt = $buwana_conn->prepare("INSERT INTO message_status_tb (message_id, buwana_id, status, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = NOW()");
        foreach ($participants as $recipient_id) {
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
        error_log("Error in send_message.php: " . $e->getMessage());

        // Error response
        $response = [
            "status" => "error",
            "message" => "An error occurred while sending the message: " . $e->getMessage()
        ];
    }
} else {
    error_log("Invalid input data. User ID: $sender_id, Conversation ID: $conversation_id, Content Length: " . strlen($content));
    // Invalid input data
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'conversation_id', 'sender_id', and 'content' or an image are required."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
