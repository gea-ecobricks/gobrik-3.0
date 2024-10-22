<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Log the start of the process
error_log("Starting image upload process");

// Get the current year for upload directories
$year_of_upload = date('Y');
error_log("Year of upload: $year_of_upload");

// Validate input data
$buwana_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;

// Ensure a file is uploaded
if ($buwana_id > 0 && $message_id > 0 && $conversation_id > 0 && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    error_log("Valid inputs received. User ID: $buwana_id, Message ID: $message_id, Conversation ID: $conversation_id");

    $image = $_FILES['image'];
    $original_file_path = $image['tmp_name'];
    $original_file_name = $image['name'];
    $mime_type = mime_content_type($original_file_path);
    error_log("Received file: $original_file_name with MIME type: $mime_type");

    $valid_mime_types = ['image/jpeg', 'image/png', 'image/webp'];

    // Validate the file type
    if (!in_array($mime_type, $valid_mime_types)) {
        error_log("Invalid file type: $mime_type");
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Please upload a JPEG, PNG, or WEBP image.']);
        exit();
    }

    // Define the file paths
    $file_name = $buwana_id . '-' . $message_id . '-' . $conversation_id . '.webp';
    $upload_dir = "../messenger-uploads/$year_of_upload/photos/";
    $thumb_dir = "../messenger-uploads/$year_of_upload/thumbs/";
    $image_path = $upload_dir . $file_name;
    $thumb_path = $thumb_dir . $file_name;

    // Ensure directories exist
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    if (!is_dir($thumb_dir)) mkdir($thumb_dir, 0777, true);
    error_log("Upload and thumbnail directories confirmed.");

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
            error_log("Unsupported image format.");
            echo json_encode(['status' => 'error', 'message' => 'Unsupported image format.']);
            exit();
    }

    // Get original image dimensions
    $original_width = imagesx($image_resource);
    $original_height = imagesy($image_resource);
    error_log("Original image dimensions: {$original_width}x{$original_height}");

    // Resize to a max of 1000px width while maintaining aspect ratio
    $max_width = 1000;
    if ($original_width > $max_width) {
        $resize_ratio = $max_width / $original_width;
        $new_width = $max_width;
        $new_height = $original_height * $resize_ratio;
    } else {
        $new_width = $original_width;
        $new_height = $original_height;
    }
    error_log("Resized image dimensions: {$new_width}x{$new_height}");

    // Create a new true color image for the resized version
    $resized_image = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($resized_image, $image_resource, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

    // Save the resized image as a WebP file
    imagewebp($resized_image, $image_path, 80); // Adjust quality as needed
    imagedestroy($resized_image);
    imagedestroy($image_resource);
    unlink($original_file_path); // Delete the original uploaded file
    error_log("Resized image saved as WebP at: $image_path");

    // Get the size of the resized image
    $image_size_bytes = filesize($image_path);

    // Create a thumbnail with a max dimension of 200px
    $thumb_max_dimension = 200;
    $thumb_ratio = min($thumb_max_dimension / $new_width, $thumb_max_dimension / $new_height);
    $thumb_width = $new_width * $thumb_ratio;
    $thumb_height = $new_height * $thumb_ratio;

    $thumb_image = imagecreatetruecolor($thumb_width, $thumb_height);
    imagecopyresampled($thumb_image, imagecreatefromwebp($image_path), 0, 0, 0, 0, $thumb_width, $thumb_height, $new_width, $new_height);
    imagewebp($thumb_image, $thumb_path, 80);
    imagedestroy($thumb_image);
    error_log("Thumbnail saved as WebP at: $thumb_path with dimensions: {$thumb_width}x{$thumb_height}");

    // Get the size of the thumbnail image
    $thumbnail_size_bytes = filesize($thumb_path);

    // Update the messages_tb with the image details
    $stmt = $buwana_conn->prepare("
        UPDATE messages_tb
        SET image_url = ?,
            thumbnail_url = ?,
            image_size_bytes = ?,
            thumbnail_size_bytes = ?,
            image_width = ?,
            image_height = ?,
            thumbnail_width = ?,
            thumbnail_height = ?,
            image_mime_type = ?
        WHERE message_id = ?
    ");
    $image_url = str_replace('../', '', $image_path); // Save relative URL for use
    $thumbnail_url = str_replace('../', '', $thumb_path);
    $stmt->bind_param(
        "ssiiiiisii",
        $image_url,
        $thumbnail_url,
        $image_size_bytes,
        $thumbnail_size_bytes,
        $new_width,
        $new_height,
        $thumb_width,
        $thumb_height,
        $mime_type,
        $message_id
    );
    $stmt->execute();
    $stmt->close();
    error_log("Database updated with image details for message ID: $message_id");

    // Return success response
    echo json_encode([
        'status' => 'success',
        'image_url' => $image_url,
        'thumbnail_url' => $thumbnail_url,
        'image_size_bytes' => $image_size_bytes,
        'thumbnail_size_bytes' => $thumbnail_size_bytes
    ]);
} else {
    error_log("Invalid request. Missing required data or file upload.");
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
