<?php
// Start the session and enable error reporting for debugging
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
require_once '../gobrikconn_env.php'; // Ensure this file sets up $gobrik_conn correctly

// Ensure proper response headers for JSON response
header('Content-Type: application/json');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the ecobrick_unique_id from the POST data
    $ecobrick_unique_id = $_POST['ecobrick_unique_id'] ?? null;

    // Check if the ecobrick_unique_id is valid
    if ($ecobrick_unique_id && is_numeric($ecobrick_unique_id)) {
        try {
            // Prepare the DELETE SQL query
            $sql = "DELETE FROM tb_ecobricks WHERE ecobrick_unique_id = ?";
            $stmt = $gobrik_conn->prepare($sql);

            // Check if the query was successfully prepared
            if ($stmt) {
                // Bind the ecobrick_unique_id to the SQL query
                $stmt->bind_param('i', $ecobrick_unique_id);

                // Execute the query
                if ($stmt->execute()) {
                    // If the deletion was successful, send success response
                    echo json_encode(['success' => true, 'message' => 'Ecobrick deleted successfully']);
                } else {
                    // If there was an error executing the query, send error response
                    echo json_encode(['success' => false, 'error' => 'Failed to delete ecobrick: ' . $stmt->error]);
                }

                // Close the statement
                $stmt->close();
            } else {
                // If preparing the query failed, send error response
                echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $gobrik_conn->error]);
            }
        } catch (Exception $e) {
            // Catch any exception that occurred and send error response
            echo json_encode(['success' => false, 'error' => 'Exception occurred: ' . $e->getMessage()]);
        }
    } else {
        // If ecobrick_unique_id is invalid or missing, send error response
        echo json_encode(['success' => false, 'error' => 'Invalid ecobrick ID provided']);
    }
} else {
    // If the request is not POST, send error response
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Close the database connection
if (isset($gobrik_conn)) {
    $gobrik_conn->close();
}
?>
