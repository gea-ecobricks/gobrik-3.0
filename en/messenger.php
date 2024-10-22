<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.11';
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
            <p id="subgreeting">Welcome to your conversations <?php echo $first_name; ?>.</p>
        </div>

     <!-- ACTIVE MESSENGER PHP AND HTML GOES HERE-->

  <div class="messenger-container">
    <div class="conversation-list-container">
        <!-- Container for the start conversation button and search box -->
        <div class="start-conversation-container">
            <button id="startConversationButton" class="start-convo-button">📝 New Chat...</button>
            <div id="searchBoxContainer" class="hidden">
                <input type="text" id="userSearchInput" placeholder="Search users..." />
                <div id="searchResults"></div>
                <div id="selectedUsers">
                    <!-- Selected users will appear here -->
                </div>
                <button id="createConversationButton" disabled class="create-button">+ Create Conversation</button>
            </div>
        </div>

        <!-- Scrollable container for conversations -->
        <div class="conversation-list" id="conversation-list">
            <!-- Conversations will be dynamically loaded here -->
        </div>
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
   // Define userId as a global variable
   const userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP

   // JavaScript/jQuery for Fetching and Displaying Conversations
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
    conversations.forEach((conv, index) => {
        // Use a default message if there is no last message
        const lastMessage = conv.last_message ? conv.last_message : "No messages yet. Start the conversation!";
        const trimmedMessage = lastMessage.length > 50
            ? lastMessage.substring(0, 50) + '...'
            : lastMessage;

        const convElement = `
            <div class="conversation-item" data-conversation-id="${conv.conversation_id}">
                <p><strong>${conv.other_participants}</strong></p>
                <p>${trimmedMessage}</p>
                <p class="timestamp">${conv.updated_at}</p>
            </div>
        `;
        conversationList.append(convElement);

        // Automatically load the most recent conversation if it's the first time loading
        if (index === 0) {
            loadMessages(conv.conversation_id);
            $('.conversation-item').removeClass('active');
            $(`.conversation-item[data-conversation-id="${conv.conversation_id}"]`).addClass('active');
        }
    });

    // Add click event to each conversation
    $('.conversation-item').on('click', function() {
        const conversationId = $(this).data('conversation-id');
        loadMessages(conversationId);
        $('.conversation-item').removeClass('active');
        $(this).addClass('active');
    });
}


   // JavaScript/jQuery for Fetching and Displaying Messages
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
    if (messages.length > 0) {
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
    } else {
        // Display a default message when no messages are present
        messageList.html('<div class="no-messages">No messages yet! Send a message to get the conversation going...</div>');
    }
}


   // Function for creating a new conversation
   function createConversation() {
       const participantIds = Array.from(selectedUsers);
       $.ajax({
           url: '../messenger/create_conversation.php',
           method: 'POST',
           data: {
               created_by: userId,
               participant_ids: JSON.stringify(participantIds)
           },
           success: function(response) {
               if (response.status === 'success') {
                   $('#searchBoxContainer').addClass('hidden');
                   $('#userSearchInput').val('');
                   $('#selectedUsers').empty();
                   selectedUsers.clear();
                   loadConversations(); // Refresh the conversations list
               } else {
                   alert(response.message);
               }
           },
           error: function(error) {
               console.error('Error creating conversation:', error);
           }
       });
   }

   // User search and selection
   $(document).ready(function() {
       const selectedUsers = new Set();

       // Show search box when "Start Conversation" button is clicked
       $('#startConversationButton').on('click', function() {
           $('#searchBoxContainer').toggleClass('hidden');
           $('#userSearchInput').focus();
       });

       // Handle user search input
       $('#userSearchInput').on('input', function() {
           const query = $(this).val().trim();
           if (query.length >= 4) {
               searchUsers(query);
           } else {
               $('#searchResults').empty();
           }
       });

       // AJAX request to search for users
       function searchUsers(query) {
           $.ajax({
               url: '../messenger/search_users.php',
               method: 'GET',
               data: {
                   query: query,
                   user_id: userId // Ensure userId is available and passed in the request
               },
               success: function(response) {
                   if (response.status === 'success') {
                       renderSearchResults(response.users);
                   } else {
                       $('#searchResults').html('<p>No users found</p>');
                   }
               },
               error: function(error) {
                   console.error('Error searching users:', error);
                   $('#searchResults').html('<p>An error occurred while searching.</p>');
               }
           });
       }

       // Render search results as a dropdown list
       function renderSearchResults(users) {
           const searchResults = $('#searchResults');
           searchResults.empty();
           if (users.length > 0) {
               users.forEach(user => {
                   if (!selectedUsers.has(user.buwana_id)) {
                       const userElement = `<div class="search-result-item" data-user-id="${user.buwana_id}">${user.first_name} ${user.last_name}</div>`;
                       searchResults.append(userElement);
                   }
               });

               // Add click event for each search result item
               $('.search-result-item').on('click', function() {
                   const userId = $(this).data('user-id');
                   const userName = $(this).text();
                   if (selectedUsers.size < 5) {
                       selectedUsers.add(userId);
                       $('#selectedUsers').append(`<div class="selected-user-item" data-user-id="${userId}">${userName}</div>`);
                       $(this).remove(); // Remove from search results
                       $('#createConversationButton').prop('disabled', false);
                   }
               });
           } else {
               searchResults.html('<p>No users found</p>');
           }
       }

       // Remove a selected user when clicked
       $('#selectedUsers').on('click', '.selected-user-item', function() {
           const userId = $(this).data('user-id');
           selectedUsers.delete(userId);
           $(this).remove();
           if (selectedUsers.size === 0) {
               $('#createConversationButton').prop('disabled', true);
           }
       });

       // Handle the create conversation button click
       $('#createConversationButton').on('click', function() {
    const participantIds = Array.from(selectedUsers); // Convert the selected users to an array
    console.log('Creating conversation with:', participantIds); // Debugging line

    $.ajax({
        url: '../messenger/create_conversation.php',
        method: 'POST',
        data: {
            created_by: userId,
            participant_ids: JSON.stringify(participantIds)
        },
        success: function(response) {
            console.log('Response from create_conversation.php:', response); // Debugging line
            if (response.status === 'success') {
                $('#searchBoxContainer').addClass('hidden');
                $('#userSearchInput').val('');
                $('#selectedUsers').empty();
                selectedUsers.clear();
                loadConversations(); // Refresh the conversations list
            } else {
                alert(response.message);
            }
        },
        error: function(error) {
            console.error('Error creating conversation:', error);
           }
    });
});


       // Load conversations on page load
       loadConversations();
   });
</script>



</body>
</html>