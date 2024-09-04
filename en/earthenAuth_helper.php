<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['buwana_id']);
}

function getUserFirstName($buwana_conn, $buwana_id) {
    $first_name = '';
    $sql_user_info = "SELECT first_name FROM users_tb WHERE buwana_id = ?";
    $stmt_user_info = $buwana_conn->prepare($sql_user_info);

    if ($stmt_user_info) {
        $stmt_user_info->bind_param('i', $buwana_id);
        if ($stmt_user_info->execute()) {
            $stmt_user_info->bind_result($first_name);
            $stmt_user_info->fetch();
        }
        $stmt_user_info->close();
    }
    return $first_name;
}
?>
