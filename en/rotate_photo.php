<?php



// Function to rotate an image by the specified degrees and save it back
function rotateEcobrickPhoto($sourcePath, $rotationDegrees, $targetPath = null) {
    // Get image details
    list($width, $height, $type) = getimagesize($sourcePath);

    // Load the source image based on its type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false; // Unsupported image type
    }

    // Rotate the image using the given degrees
    $rotatedImage = imagerotate($sourceImage, -$rotationDegrees, 0); // Negative for clockwise rotation to match CSS

    // If no target path is provided, overwrite the original
    if ($targetPath === null) {
        $targetPath = $sourcePath;
    }

    // Save the rotated image back in its original format
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($rotatedImage, $targetPath, 90); // 90 is JPEG quality
            break;
        case IMAGETYPE_PNG:
            imagepng($rotatedImage, $targetPath);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($rotatedImage, $targetPath);
            break;
    }

    // Free memory
    imagedestroy($sourceImage);
    imagedestroy($rotatedImage);

    return true; // Indicate success
}




// Check if the request is POST and required parameters are present
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['photo_url']) && isset($_POST['rotation'])) {
    $photoUrl = $_POST['photo_url'];
    $rotationDegrees = intval($_POST['rotation']);

    // Ensure that the degrees are within valid range (multiples of 90)
    if ($rotationDegrees % 90 !== 0) {
        echo "Invalid rotation degrees.";
        exit;
    }

    // Construct the actual file path from the URL
    $photoPath = $_SERVER['DOCUMENT_ROOT'] . parse_url($photoUrl, PHP_URL_PATH);

    // Call the rotate function
    if (rotateEcobrickPhoto($photoPath, $rotationDegrees)) {
        echo "Image rotated successfully.";
    } else {
        echo "Failed to rotate image.";
    }
}
