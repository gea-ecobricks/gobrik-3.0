<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.1';
$page = 'messenger';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

//startSecureSession(); // Start a secure session with regeneration to prevent session fixation

// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Run messenger code here

    // Close the database connections
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    // Redirect to login page with the redirect parameter set to the current page
    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "login.php?redirect=' . urlencode($page) . '";
    </script>';
    exit();
}

// Output the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>



<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/messenger-inc.php"); ?>

<div class="splash-title-block"></div>
<div id="splash-bar"></div>
<div id="top-page-image" class="message-birded top-page-image"></div>

<!-- MESSENGER CONTENT -->
<div id="form-submission-box" style="height:fit-content;margin-top: 90px;">
    <div class="form-container">
        <div style="text-align:center;width:100%;margin:auto;">
            <h2 id="greeting">GoBrik Messenger</h2>
            <p id="subgreeting">Welcome to your conversations! <?php echo $buwana_id; ?></p>
        </div>

     <!-- ACTIVE MESSENGER PHP AND HTML GOES HERE-->

        <div class="messenger-container">
    <div class="conversation-list" id="conversation-list">
        <!-- Conversations will be dynamically loaded here -->
    </div>
    <div class="message-thread" id="message-thread">
        <div id="message-list">
            <!-- Messages will be dynamically loaded here -->
        </div>
        <div class="message-input">
            <textarea id="messageInput" placeholder="Type your message..."></textarea>
            <button id="sendButton">Send</button>
        </div>
    </div>
</div>

    </div>
</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2024.php"); ?>


<script>

   // 3. JavaScript/jQuery for Fetching and Displaying Conversations

    $(document).ready(function() {
    const userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP

  function loadConversations() {
    $.ajax({
        url: '../messenger/get_conversations.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            console.log('Response from get_conversations.php:', response);
            if (response.status === 'success') {
                renderConversations(response.conversations);
            } else {
                alert(response.message);
            }
        },
        error: function(error) {
            console.error('Error fetching conversations:', error);
        }
    });
}


    function renderConversations(conversations) {
        const conversationList = $('#conversation-list');
        conversationList.empty();
        conversations.forEach(conv => {
            const convElement = `
                <div class="conversation-item" data-conversation-id="${conv.conversation_id}">
                    <p><strong>${conv.last_message_sender_name}</strong></p>
                    <p>${conv.last_message}</p>
                    <p class="timestamp">${conv.updated_at}</p>
                </div>
            `;
            conversationList.append(convElement);
        });

        // Add click event to each conversation
        $('.conversation-item').on('click', function() {
            const conversationId = $(this).data('conversation-id');
            loadMessages(conversationId);
            $('.conversation-item').removeClass('active');
            $(this).addClass('active');
        });
    }

    // Load conversations on page load
    loadConversations();
});


// 4. JavaScript/jQuery for Fetching and Displaying Messages
    function loadMessages(conversationId) {
    $.ajax({
        url: '../messenger/get_messages.php',
        method: 'GET',
        data: { conversation_id: conversationId, user_id: userId },
        success: function(response) {
            if (response.status === 'success') {
                renderMessages(response.messages);
            } else {
                alert(response.message);
            }
        },
        error: function(error) {
            console.error('Error fetching messages:', error);
        }
    });
}

function renderMessages(messages) {
    const messageList = $('#message-list');
    messageList.empty();
    messages.forEach(msg => {
        const messageClass = msg.sender_id == userId ? 'self' : '';
        const msgElement = `
            <div class="message-item ${messageClass}">
                <p class="sender">${msg.sender_name}</p>
                <p>${msg.content}</p>
                <p class="timestamp">${msg.created_at}</p>
            </div>
        `;
        messageList.append(msgElement);
    });

    // Scroll to the bottom of the message list to show the latest messages
    messageList.scrollTop(messageList.prop("scrollHeight"));
}


    //5. JavaScript/jQuery for Sending Messages

    $('#sendButton').on('click', function() {
    const messageContent = $('#messageInput').val().trim();
    const selectedConversationId = $('.conversation-item.active').data('conversation-id');

    if (messageContent && selectedConversationId) {
        $.ajax({
            url: '../messenger/send_message.php',
            method: 'POST',
            data: {
                conversation_id: selectedConversationId,
                sender_id: userId,
                content: messageContent
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#messageInput').val(''); // Clear the input field
                    loadMessages(selectedConversationId); // Refresh message list
                } else {
                    alert(response.message);
                }
            },
            error: function(error) {
                console.error('Error sending message:', error);
            }
        });
    } else {
        alert('Please select a conversation and enter a message.');
    }
});

//JavaScript/jQuery for Marking Messages as Read

function markMessagesAsRead(conversationId, latestMessageId) {
    $.ajax({
        url: '../messenger/update_message_status.php',
        method: 'POST',
        data: {
            message_id: latestMessageId,
            user_id: userId,
            status: 'read'
        },
        success: function(response) {
            console.log('Messages marked as read:', response);
        },
        error: function(error) {
            console.error('Error marking messages as read:', error);
        }
    });
}
</script>

</body>
</html>