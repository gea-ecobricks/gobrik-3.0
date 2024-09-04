<?php
session_start();

function startSecureSession() {
    // Regenerate the session ID periodically to prevent session fixation
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } elseif (time() - $_SESSION['CREATED'] > 1800) { // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
}

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
