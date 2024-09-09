<?php
require_once 'earthen_helper.php'; // Include the helper functions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_addr = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!empty($email_addr)) {
        // Call the function to check the email status
        $isSubscribed = checkEarthenEmailStatus($email_addr);

        // Return the result as JSON
        echo json_encode(['isSubscribed' => $isSubscribed]);
    } else {
        echo json_encode(['error' => 'Invalid email address']);
    }
}
?>