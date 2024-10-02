<?php
require_once '../buwanaconn_env.php'; // Your database connection

if (isset($_POST['query'])) {
    $query = '%' . trim($_POST['query']) . '%';  // Search with wildcards

    // Prepare the SQL query to search for communities by name
    $sql_search = "SELECT com_id, com_name FROM communities_tb WHERE com_name LIKE ?";
    $stmt_search = $buwana_conn->prepare($sql_search);

    if ($stmt_search) {
        $stmt_search->bind_param('s', $query);
        $stmt_search->execute();
        $stmt_search->bind_result($com_id, $com_name);

        $communities = [];
        while ($stmt_search->fetch()) {
            $communities[] = ['com_id' => $com_id, 'com_name' => $com_name];
        }

        $stmt_search->close();

        // Return the matching communities as JSON
        echo json_encode($communities);
    }
}
?>
