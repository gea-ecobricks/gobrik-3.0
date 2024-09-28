<?php
// Get the user's data from the POST request
$buwana_id = $_POST['buwana_id'] ?? null;
$credential_key = $_POST['credential_key'] ?? null;
$first_name = $_POST['first_name'] ?? ''; // Get the first name from POST
$subscribed_newsletters = json_decode($_POST['subscribed_newsletters'] ?? '[]', true);
$ghost_member_id = $_POST['ghost_member_id'] ?? null;

// Ensure we have the user's email
if (empty($credential_key)) {
    die('User email could not be retrieved.');
}

// Retrieve selected subscriptions from the form submission
$selected_subscriptions = $_POST['subscriptions'] ?? [];

// Determine which newsletters to subscribe to and which to unsubscribe from
$to_subscribe = array_diff($selected_subscriptions, $subscribed_newsletters);

// If subscribed_newsletters is empty, treat this as a new user subscription
if (empty($subscribed_newsletters)) {
    // Pass $first_name to the function
    subscribeUserToNewsletter($credential_key, $to_subscribe, $first_name);
} else {
    // If subscribed_newsletters is not empty, use the provided member ID to update subscriptions
    if ($ghost_member_id) {
        updateSubscribeUser($ghost_member_id, $selected_subscriptions);
    } else {
        error_log('Error: Member ID is missing for updating subscriptions.');
    }
}

// Update users_tb buwana database record
if ($buwana_id) {
    $update_user_query = "UPDATE users_tb SET account_status = 'registered and subscribed, no login', terms_of_service = 1 WHERE buwana_id = ?";
    $stmt_update_user = $buwana_conn->prepare($update_user_query);
    $stmt_update_user->bind_param("i", $buwana_id);
    $stmt_update_user->execute();
    $stmt_update_user->close();
}

// Update tb_ecobrickers record
if ($buwana_id) {
    $select_ecobricker_query = "SELECT gea_status FROM tb_ecobrickers WHERE buwana_id = ?";
    $stmt_select_ecobricker = $buwana_conn->prepare($select_ecobricker_query);
    $stmt_select_ecobricker->bind_param("i", $buwana_id);
    $stmt_select_ecobricker->execute();
    $stmt_select_ecobricker->bind_result($gea_status);
    $stmt_select_ecobricker->fetch();
    $stmt_select_ecobricker->close();

    if (empty($gea_status)) {
        $gea_status = 'Gobriker';
    } else {
        $gea_status = 'Ecobricker';
    }

    $update_ecobricker_query = "UPDATE tb_ecobrickers SET gea_status = ?, earthen_registered = 1, account_notes = CONCAT(IFNULL(account_notes, ''), ' registered and subscribed, no login') WHERE buwana_id = ?";
    $stmt_update_ecobricker = $buwana_conn->prepare($update_ecobricker_query);
    $stmt_update_ecobricker->bind_param("si", $gea_status, $buwana_id);
    $stmt_update_ecobricker->execute();
    $stmt_update_ecobricker->close();
}

// Redirect the user to the login page with the required parameters after processing
header('Location: login.php?status=firsttime&id=' . urlencode($buwana_id));
exit();

?>
