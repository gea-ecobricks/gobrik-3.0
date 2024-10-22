<?php
ob_start(); // Start output buffering
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../buwanaconn_env.php';

$created_by = isset($_POST['created_by']) ? intval($_POST['created_by']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$response = [];

if ($created_by > 0 && !empty($message)) {
    $buwana_conn->begin_transaction();
    try {
        $stmt = $buwana_conn->prepare("INSERT INTO conversations_tb (created_by) VALUES (?)");
        $stmt->bind_param("i", $created_by);
        $stmt->execute();
        $conversation_id = $buwana_conn->insert_id;
        $stmt->close();

        $dev_team_ids = [1, 150, 144, 145];
        $stmt = $buwana_conn->prepare("INSERT INTO participants_tb (conversation_id, buwana_id) VALUES (?, ?)");
        foreach ($dev_team_ids as $dev_id) {
            $stmt->bind_param("ii", $conversation_id, $dev_id);
            $stmt->execute();
        }

        $stmt->bind_param("ii", $conversation_id, $created_by);
        $stmt->execute();
        $stmt->close();

        $stmt = $buwana_conn->prepare("INSERT INTO messages_tb (conversation_id, sender_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $conversation_id, $created_by, $message);
        $stmt->execute();
        $message_id = $buwana_conn->insert_id;
        $stmt->close();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            require_once '../messenger/upload_image_attachment.php';
            $_POST['user_id'] = $created_by;
            $_POST['message_id'] = $message_id;
            $_POST['conversation_id'] = $conversation_id;
            $_FILES['image'] = $_FILES['image'];

            ob_start();
            include '../messenger/upload_image_attachment.php';
            $upload_response = json_decode(ob_get_clean(), true);

            if ($upload_response['status'] !== 'success') {
                throw new Exception($upload_response['message']);
            }
        }

        $buwana_conn->commit();

        $response = [
            "status" => "success",
            "message" => "Bug report created successfully.",
            "conversation_id" => $conversation_id
        ];
    } catch (Exception $e) {
        $buwana_conn->rollback();
        $response = [
            "status" => "error",
            "message" => "An error occurred while creating the bug report: " . $e->getMessage()
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid input data. 'created_by' must be a valid user ID and 'message' must not be empty."
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
ob_end_flush(); // End buffering and flush output
$buwana_conn->close();
?>
