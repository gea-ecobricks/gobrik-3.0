<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once '../buwanaconn_env.php';

// Retrieve the search query from the GET request
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

$response = [];

// Validate the query
if (!empty($query)) {
    try {
        // Prepare the SQL query to search for users
        $stmt = $buwana_conn->prepare("
            SELECT buwana_id, first_name, last_name
            FROM users_tb
            WHERE (first_name LIKE ? OR last_name LIKE ? OR username LIKE ?)
            AND buwana_id != ?
            LIMIT 10
        ");
        $searchQuery = '%' . $query . '%';
        $stmt->bind_param("sssi", $searchQuery, $searchQuery, $searchQuery, $user_id);
        $stmt->execute();
        $stmt->bind_result($buwana_id, $first_name, $last_name);

        // Fetch all matching users
        $users = [];
        while ($stmt->fetch()) {
            $users[] = [
                "buwana_id" => $buwana_id,
                "first_name" => $first_name,
                "last_name" => $last_name
            ];
        }
        $stmt->close();

        $response = [
            "status" => "success",
            "users" => $users
        ];
    } catch (Exception $e) {
        $response = [
            "status" => "error",
            "message" => "An error occurred while searching for users: " . $e->getMessage()
        ];
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Invalid search query."
    ];
}

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$buwana_conn->close();
?>
