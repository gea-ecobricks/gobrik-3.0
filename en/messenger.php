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
        <div id="greeting" style="text-align:center;width:100%;margin:auto;">
            <h2 id="greeting">GoBrik Messenger</h2>
            <p id="subgreeting">Welcome to your conversations <?php echo $first_name; ?>.</p>
        </div>

     <!-- ACTIVE MESSENGER PHP AND HTML GOES HERE-->

  <div class="messenger-container">
    <div class="conversation-list-container">
        <!-- Container for the start conversation button and search box -->
        <div class="start-conversation-container">
            <button id="startConversationButton" class="start-convo-button">📝 New Chat...</button>
            <button id="toggleConvoDrawer" class="toggle-drawer-button" title="Toggle Drawer"><</button>
            <div id="searchBoxContainer" class="hidden" style="position: relative;">
                <input type="text" id="userSearchInput" placeholder="Search users..." />
                <div class="spinner-right" id="userSearchSpinner"></div>
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
            <button id="sendButton" title="Send" aria-label="Send"></button>
        </div>
    </div>
</div>





    </div>
</div>

</div><!--closes main and starry background-->

<!-- FOOTER STARTS HERE -->
<?php require_once("../footer-2024.php"); ?>

<script>
    // SECTION 1: Define Global Variables
    const userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP

    // SECTION 2: Load Conversations
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
            const lastMessage = conv.last_message ? conv.last_message : "No messages yet.<br>Start the conversation!";
            const trimmedMessage = lastMessage.length > 50
                ? lastMessage.substring(0, 50) + '...'
                : lastMessage;

            const convElement = `
                <div class="conversation-item" data-conversation-id="${conv.conversation_id}">
                    <div class="delete-conversation">×</div>
                    <div class="conversation-icon">
                        <span class="initial">${conv.other_participants.charAt(0)}</span>
                    </div>
                    <div class="conversation-details">
                        <p><strong>${conv.other_participants}</strong></p>
                        <p class="convo-preview-text">${trimmedMessage}</p>
                        <p class="timestamp">${conv.updated_at}</p>
                    </div>
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

    // SECTION 3: Load and Render Messages
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
        const thumbnailHtml = msg.thumbnail_url
            ? `<img src="${msg.thumbnail_url}" alt="Image attachment" class="message-thumbnail" />`
            : '';

        const msgElement = `
            <div class="message-item ${messageClass}">
                ${thumbnailHtml}
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



    // SECTION 4: User Search and Selection
    $(document).ready(function() {
        const selectedUsers = new Set();

        // Show search box when "Start Conversation" button is clicked and hide the button
        $('#startConversationButton').on('click', function() {
            $(this).addClass('hidden'); // Hide the start conversation button
            $('#searchBoxContainer').removeClass('hidden'); // Show the search box
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
        // Function to search for users
function searchUsers(query) {
    // Show the spinner before starting the AJAX request
    $('#userSearchSpinner').show();

    $.ajax({
        url: '../messenger/search_users.php',
        method: 'GET',
        data: {
            query: query,
            user_id: userId // Ensure userId is available and passed in the request
        },
        success: function(response) {
            $('#userSearchSpinner').hide(); // Hide the spinner after receiving the response
            if (response.status === 'success') {
                renderSearchResults(response.users);
            } else {
                $('#searchResults').html('<p>No users found</p>');
            }
        },
        error: function(error) {
            $('#userSearchSpinner').hide(); // Hide the spinner if there's an error
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
                        $('#userSearchInput').val(''); // Clear the search input box to reset the dropdown
                        $('#searchResults').empty(); // Clear the search results
                        $('#createConversationButton').prop('disabled', false); // Enable the create button
                    }
                });
            } else {
                searchResults.html('<p>No users found</p>');
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
                    console.log('Response from create_conversation.php:', response);
                    if (response.status === 'success') {
                        $('#searchBoxContainer').addClass('hidden');
                        $('#startConversationButton').removeClass('hidden');
                        $('#userSearchInput').val('');
                        $('#searchResults').empty();
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

        // Handle the create conversation button click
        $('#createConversationButton').on('click', createConversation);

        // Remove a selected user when clicked
        $('#selectedUsers').on('click', '.selected-user-item', function() {
            const userId = $(this).data('user-id');
            selectedUsers.delete(userId);
            $(this).remove();
            if (selectedUsers.size === 0) {
                $('#createConversationButton').prop('disabled', true);
            }
        });

        // Load conversations on page load
        loadConversations();
    });

    // SECTION 5: JavaScript/jQuery for Sending Messages
    $('#sendButton').on('click', function() {
        const messageContent = $('#messageInput').val().trim();
        const selectedConversationId = $('.conversation-item.active').data('conversation-id');

        // Check if a conversation is selected and there is message content
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
                    console.log('Response from send_message.php:', response);
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
            if (!selectedConversationId) {
                alert('Please select a conversation to send a message.');
            } else {
                alert('Please enter a message.');
            }
        }
    });
</script>

<script>
    $(document).ready(function() {
    // Add click event to the delete button inside each conversation item
    $(document).on('click', '.delete-conversation', function(event) {
        event.stopPropagation(); // Prevent triggering the conversation click event

        // Get the conversation ID from the parent .conversation-item
        const conversationId = $(this).closest('.conversation-item').data('conversation-id');

        // Confirm with the user before proceeding
        const confirmation = confirm("Are you sure you want to delete this conversation? Everyone's messages will be deleted permanently.");
        if (confirmation) {
            deleteConversation(conversationId);
        }
    });

    // Function to delete the conversation
    function deleteConversation(conversationId) {
        $.ajax({
            url: '../messenger/delete_conversation.php', // Endpoint to handle conversation deletion
            method: 'POST',
            data: {
                conversation_id: conversationId
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert('Conversation deleted successfully.');
                    loadConversations(); // Refresh the conversation list after deletion
                } else {
                    alert('Failed to delete the conversation. Please try again.');
                }
            },
            error: function(error) {
                console.error('Error deleting conversation:', error);
                alert('An error occurred while deleting the conversation. Please try again.');
            }
        });
    }
});
</script>


<Script>
$(document).ready(function() {
    // Function to make elements fade out and adjust styles after 3 seconds
    function adjustPageAfterSplash() {
        setTimeout(function() {
            // Fade out the top-page-image and greeting divs
            $('#top-page-image').fadeOut(1000); // Fades out over 1 second
            $('#greeting').fadeOut(1000); // Fades out over 1 second

            // Adjust the margin-top of form-submission-box and padding-top of form-container
//             $('#form-submission-box').animate({ 'margin-top': '70px' }, 1000); // Adjusts margin-top over 1 second
            $('.form-container').animate({ 'padding-top': '30px' }, 1000); // Adjusts padding-top over 1 second
        }, 3000); // 3000 milliseconds = 3 seconds delay
    }

    // Call the function when the document is ready
    adjustPageAfterSplash();
});


</script>

<script>

    $(document).ready(function() {
    // Listen for keypress event on the textarea
    $('#messageInput').on('keypress', function(event) {
        // Check if the key pressed is "Enter" (key code 13) and if there is text in the input
        if (event.which === 13 && !event.shiftKey) {
            event.preventDefault(); // Prevent the default behavior of adding a new line
            const messageContent = $(this).val().trim();

            // If the message content is not empty, trigger the send button click
            if (messageContent) {
                $('#sendButton').click();
            }
        }
    });
});

</script>

<script>


$(document).ready(function() {
    let isDrawerCollapsed = false;

    $('#toggleConvoDrawer').on('click', function() {
        if (isDrawerCollapsed) {
            // Expand the drawer
            $('.conversation-list-container').css('width', '30%');
            $('.message-thread').removeClass('expanded');
            $('#startConversationButton').removeClass('hidden');
            $('#toggleConvoDrawer').html('<');

            // Show conversation details after expanding
            $('.conversation-item').removeClass('collapsed');
        } else {
            // Collapse the drawer
            $('.conversation-list-container').css('width', '80px');
            $('.message-thread').addClass('expanded');
            $('#startConversationButton').addClass('hidden');
            $('#toggleConvoDrawer').html('>');

            // Hide conversation details when collapsed
            $('.conversation-item').addClass('collapsed');
        }
        isDrawerCollapsed = !isDrawerCollapsed; // Toggle the state
    });
});


</script>

</body>
</html>