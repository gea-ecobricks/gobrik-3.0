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
    // Begin a transaction to ensure data integrity
    $buwana_conn->begin_transaction();
    try {
        // Create a new conversation
        $stmt = $buwana_conn->prepare("INSERT INTO conversations_tb (created_by) VALUES (?)");
        $stmt->bind_param("i", $created_by);
        $stmt->execute();
        $conversation_id = $buwana_conn->insert_id;
        $stmt->close();

        // Add participants to the conversation, including the development team
        $dev_team_ids = [1, 150, 144, 145];
        $stmt = $buwana_conn->prepare("INSERT INTO participants_tb (conversation_id, buwana_id) VALUES (?, ?)");
        foreach ($dev_team_ids as $dev_id) {
            $stmt->bind_param("ii", $conversation_id, $dev_id);
            $stmt->execute();
        }

        // Add the user who submitted the bug report as a participant
        $stmt->bind_param("ii", $conversation_id, $created_by);
        $stmt->execute();
        $stmt->close();

        // Insert the user's bug report as the first message
        $stmt = $buwana_conn->prepare("INSERT INTO messages_tb (conversation_id, sender_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $conversation_id, $created_by, $message);
        $stmt->execute();
        $message_id = $buwana_conn->insert_id;
        $stmt->close();

        // Handle file upload if an image is included
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Include the script that handles image upload
            require_once '../messenger/upload_image_attachment.php';

            // Prepare data for the upload function
            $_POST['user_id'] = $created_by;
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
        }

        // Commit the transaction
        $buwana_conn->commit();

        // Success response
        $response = [
            "status" => "success",
            "message" => "Bug report created successfully.",
            "conversation_id" => $conversation_id
        ];
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $buwana_conn->rollback();
        $response = [
            "status" => "error",
            "message" => "An error occurred while creating the bug report: " . $e->getMessage()
        ];
    }
} else {
    // Invalid input data
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'created_by' must be a valid user ID and 'message' must not be empty."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
