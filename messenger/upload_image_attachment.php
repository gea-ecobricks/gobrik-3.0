<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the start of the process
error_log("Starting image upload process...");

// Include the database connection
require_once '../buwanaconn_env.php';

// Get the current year for upload directories
$year_of_upload = date('Y');
error_log("Year of upload: $year_of_upload");

// Validate input data
$buwana_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;

// Log input validation checks
error_log("User ID: $buwana_id, Message ID: $message_id, Conversation ID: $conversation_id");

// Ensure a file is uploaded
if ($buwana_id > 0 && $message_id > 0 && $conversation_id > 0 && isset($_FILES['image'])) {
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        error_log("Image file received: " . $_FILES['image']['name']);

        $image = $_FILES['image'];
        $original_file_path = $image['tmp_name'];
        $original_file_name = $image['name'];
        $mime_type = mime_content_type($original_file_path);
        $valid_mime_types = ['image/jpeg', 'image/png', 'image/webp'];

        // Validate the file type
        if (!in_array($mime_type, $valid_mime_types)) {
            error_log("Invalid file type: $mime_type");
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Please upload a JPEG, PNG, or WEBP image.']);
            exit();
        }

        error_log("Valid file type: $mime_type");

        // Define the file paths
        $file_name = $buwana_id . '-' . $message_id . '-' . $conversation_id . '.webp';
        $upload_dir = "../messenger-uploads/$year_of_upload/photos/";
        $thumb_dir = "../messenger-uploads/$year_of_upload/thumbs/";
        $image_path = $upload_dir . $file_name;
        $thumb_path = $thumb_dir . $file_name;

        // Ensure directories exist
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        if (!is_dir($thumb_dir)) mkdir($thumb_dir, 0777, true);

        // Load the image using GD library
        switch ($mime_type) {
            case 'image/jpeg':
                $image_resource = imagecreatefromjpeg($original_file_path);
                break;
            case 'image/png':
                $image_resource = imagecreatefrompng($original_file_path);
                break;
            case 'image/webp':
                $image_resource = imagecreatefromwebp($original_file_path);
                break;
            default:
                error_log("Unsupported image format: $mime_type");
                echo json_encode(['status' => 'error', 'message' => 'Unsupported image format.']);
                exit();
        }

        // More logs for each step
        error_log("Image resource loaded. Original dimensions: " . imagesx($image_resource) . "x" . imagesy($image_resource));

        // Rest of the code to resize, save, and handle the image...

    } else {
        error_log("Error with file upload: " . $_FILES['image']['error']);
        echo json_encode(['status' => 'error', 'message' => 'Error during file upload.']);
        exit();
    }
} else {
    error_log("Invalid request. Missing required data or file upload.");
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
