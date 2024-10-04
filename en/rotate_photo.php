<?php
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
