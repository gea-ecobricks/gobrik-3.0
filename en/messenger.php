<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.128';
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


<!-- MESSENGER CONTENT
<div id="top-page-image" class="message-birded top-page-image"></div>-->
<div id="form-submission-box" style="height:fit-content;margin-top: 90px;">
    <div class="form-container" id="messenger-form-container">


     <!--
     <div id="greeting" style="text-align:center;width:100%;margin:auto;">
            <h2 id="greeting">GoBrik Messenger</h2>
            <p id="subgreeting">Welcome to your conversations <?php echo $first_name; ?>.</p>
        </div>
    ACTIVE MESSENGER PHP AND HTML GOES HERE-->

  <div class="messenger-container">
    <div class="conversation-list-container">
        <!-- Container for the start conversation button and search box -->
        <div class="start-conversation-container">
            <div style="display:flex;flex-flow:row">
                <button id="startConversationButton" class="start-convo-button">
                    <img src="../svgs/gobrik-3-emblem-tight.svg?v=5" alt="GoBrik Emblem" class="button-icon">
                    <span style="margin:auto auto auto 0px;">New Chat...</span>
                </button>
                <button id="toggleConvoDrawer" class="toggle-drawer-button" title="Toggle Drawer">⮜</button>
            </div>

            <div id="searchBoxContainer" class="hidden" style="position: relative;">
                <button id="clearSearchButton" class="clear-search-button" aria-label="Clear Search"></button>

                <input type="text" id="userSearchInput" placeholder="Search users..." />
                <div class="spinner-right" id="userSearchSpinner"></div>
                <div id="searchResults"></div>
                <div id="selectedUsers">
                    <!-- Selected users will appear here -->
                </div>
                <button id="createConversationButton" class="create-button">Create Conversation →</button>
            </div>

        </div>


        <!-- Scrollable container for conversations -->
        <div class="conversation-list" id="conversation-list">
            <!-- Conversations will be dynamically loaded here -->
        </div>
    </div>

    <div class="message-thread" id="message-thread">
        <div id="message-list">

            <div id="messenger-welcome" class="full-convo-message">
            <div class="message-birded" style="width:300px; height:140px;"></div>
            <h4>Welcome to GoBrik messenger.</h4>
            <p style="font-size:1em; margin-top: -20px;">Choose a conversation or start a new one!</p>
            <h4 style="margin-top: -5px;">👈</h4>
        </div>


            <!-- Messages will be dynamically loaded here -->
        </div>
        <div class="message-input-wrapper" style="position: relative; padding: 10px 10px 15px 10px;">
            <textarea id="messageInput" placeholder="Type your message..." rows="1"></textarea>
            <input type="file" id="imageUploadInput" accept="image/jpeg, image/jpg, image/png, image/webp" style="display: none;" />

            <button type="button" id="uploadPhotoButton" class="upload-photo-button" title="Upload Photo" aria-label="Upload Photo">📷</button>
            <button id="sendButton" title="Send" aria-label="Send" class="send-message-button"></button>
            <div id="uploadSpinner" class="upload-spinner hidden"></div>
            <div id="errorIndicator" class="error-indicator hidden">⚠️</div>
            <span id="imageFileName" class="image-file-name"></span>
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
    var userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP

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

   let isFirstRender = true; // Flag to track if it's the first render

function renderConversations(conversations) {
    const conversationList = $('#conversation-list');
    conversationList.empty();

    conversations.forEach((conv, index) => {
        // Use a default message if there is no last message
        const lastMessage = conv.last_message ? conv.last_message : "🥚 No messages yet.";
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

        // Only load the most recent conversation if it's not the first time this function has run
        if (!isFirstRender && index === 0) {
            loadMessages(conv.conversation_id);
            $('.conversation-item').removeClass('active');
            $(`.conversation-item[data-conversation-id="${conv.conversation_id}"]`).addClass('active');
        }
    });

    // Set the flag to false after the first render
    isFirstRender = false;

    // Add click event to each conversation
    $('.conversation-item').on('click', function() {
        const conversationId = $(this).data('conversation-id');
        loadMessages(conversationId);
        $('.conversation-item').removeClass('active');
        $(this).addClass('active');
    });
}



    // SECTION 3: Load message using ajax to grab from database using get_message.  Then  Render Messages is called.  Then conversations are updated.
function loadMessages(conversationId) {
    $.ajax({
        url: '../messenger/get_messages.php',
        method: 'GET',
        data: { conversation_id: conversationId, user_id: userId },
        success: function(response) {
            if (response.status === 'success') {
                const messages = response.messages;
                renderMessages(messages);

                // Check if the conversation is empty and display the "New Chat" message if needed
                if (messages.length === 0) {
                    showNewChatMessage();
                } else {
                    hideNewChatMessage();

                    // Update conversation details with the latest message and timestamp
                    const lastMessage = messages[messages.length - 1];
                    updateConversationDetails(conversationId, lastMessage);
                }
            } else {
                alert(response.message);
            }
        },
        error: function(error) {
            console.error('Error fetching messages:', error);
        }
    });
}

// Function to update conversation details
function updateConversationDetails(conversationId, lastMessage) {
    const lastMessageText = lastMessage.content.length > 50
        ? lastMessage.content.substring(0, 50) + '...'
        : lastMessage.content;
    const updatedAt = lastMessage.created_at;
    const otherParticipants = lastMessage.sender_name;

    // Find the conversation element by its data attribute
    const conversationElement = $(`.conversation-item[data-conversation-id="${conversationId}"]`);

    // Update the details inside the conversation element
    conversationElement.find('.convo-preview-text').text(lastMessageText);
    conversationElement.find('.timestamp').text(updatedAt);
    conversationElement.find('strong').text(otherParticipants);

    // Optionally, move the updated conversation to the top of the list to reflect recent activity
    $('#conversation-list').prepend(conversationElement);
}



// Function to show the "New Chat" message
function showNewChatMessage() {
    const newChatMessage = `
        <div id="no-messages-yet" class="full-convo-message">
            <h1>🐣</h1>
            <h4>This chat is just getting going.</h4>
            <p style="font-size:1em; margin-top: -20px;">Send a message to get cracking!</p>
            <h4>👇</h4>
        </div>
    `;
    $('#message-list').html(newChatMessage); // Insert the message into the message list
}

// Function to hide the "New Chat" message
function hideNewChatMessage() {
    $('#no-messages-yet').remove(); // Remove the new chat message if it exists
}



function renderMessages(messages) {
    const messageList = $('#message-list');
    messageList.empty();

    messages.forEach(msg => {
        const messageClass = msg.sender_id == userId ? 'self' : '';
        const thumbnailHtml = msg.thumbnail_url
            ? `<a href="#" class="thumbnail-link" data-full-url="../${msg.image_url}">
                <img src="../${msg.thumbnail_url}" alt="Image attachment" class="message-thumbnail" />
               </a>`
            : '';

        const msgElement = `
            <div class="message-item ${messageClass}">
                ${thumbnailHtml}
                <p class="sender">${msg.sender_name}</p>
                <p class="the-message-text">${msg.content}</p>
                <p class="timestamp">${msg.created_at}<span id="check-sent" style="color:green">  ✓</span></p>
            </div>
        `;
        messageList.append(msgElement);
    });

    // Scroll to the bottom of the message list to show the latest messages
    messageList.scrollTop(messageList.prop("scrollHeight"));

    // Add click event to open modal for each thumbnail link
    $('.thumbnail-link').on('click', function(event) {
        event.preventDefault();
        const fullUrl = $(this).data('full-url');
        openPhotoModal(fullUrl);
    });
}

$(document).ready(function() {
    const selectedUsers = new Set();

    // Show search box when "Start Conversation" button is clicked and hide the button
    $('#startConversationButton').on('click', function() {
        $(this).addClass('hidden'); // Hide the start conversation button
        $('#searchBoxContainer').removeClass('hidden'); // Show the search box
        $('#userSearchInput').focus();
        $('#toggleConvoDrawer').addClass('hidden');
        $('#clearSearchButton').show(); // Show the clear search button
        toggleCreateButton(); // Ensure the create button state is correct when search starts
    });

    // Handle user search input
    $('#userSearchInput').on('input', function() {
        const query = $(this).val().trim();
        if (query.length >= 3) { // Adjusted to trigger after 3 characters
            searchUsers(query);
        } else {
            $('#searchResults').empty();
        }
    });

    // Handle clear search button click
    $('#clearSearchButton').on('click', function() {
        $('#userSearchInput').val(''); // Clear the input field
        $('#searchResults').empty(); // Clear the search results
        $('#searchBoxContainer').addClass('hidden'); // Hide the search box
        $('#startConversationButton').removeClass('hidden'); // Show the start conversation button
        $('#toggleConvoDrawer').removeClass('hidden'); // Show the toggle button again
        $(this).hide(); // Hide the clear search button
        selectedUsers.clear(); // Clear selected users
        $('#selectedUsers').empty(); // Clear displayed selected users
        toggleCreateButton(); // Ensure the create button is disabled when clearing the search
    });

    // Show the clear button when there's text in the search input
    $('#userSearchInput').on('input', function() {
        if ($(this).val().trim() !== '') {
            $('#clearSearchButton').show();
        } else {
            $('#clearSearchButton').hide();
        }
    });

    // Function to search for users
    function searchUsers(query) {
        $('#userSearchSpinner').show(); // Show the spinner before starting the AJAX request

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
                    $('#selectedUsers').append(`<div class="selected-user-item" data-user-id="${userId}">+ ${userName}</div>`);
                    $(this).remove(); // Remove from search results
                    $('#userSearchInput').val(''); // Clear the search input box to reset the dropdown
                    $('#searchResults').empty(); // Clear the search results
                    toggleCreateButton(); // Enable or disable the create button based on selection
                }
            });
        } else {
            searchResults.html('<p>No users found</p>');
        }
    }

    // Function to enable or disable the create conversation button based on selection
    function toggleCreateButton() {
        if (selectedUsers.size > 0) {
            $('#createConversationButton').prop('disabled', false).removeClass('hidden'); // Enable the create button if users are selected
        } else {
            $('#createConversationButton').prop('disabled', true).addClass('hidden'); // Disable the create button if no users are selected
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
                const conversationId = response.conversation_id; // Extract the conversation ID from the response

                // Hide the search box and reset the interface
                $('#searchBoxContainer').addClass('hidden');
                $('#startConversationButton').removeClass('hidden');
                $('#userSearchInput').val('');
                $('#searchResults').empty();
                $('#selectedUsers').empty();
                $('#toggleConvoDrawer').removeClass('hidden');
                selectedUsers.clear();

                // Refresh the conversations list
                loadConversations();


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
$(document).ready(function() {
    const maxFileSize = 10 * 1024 * 1024; // 10 MB
    const userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP

    // Function to show the spinner
    function showUploadSpinner() {
        $('#sendButton').hide();
        $('#errorIndicator').hide();
        $('#uploadSpinner').show();
    }

    // Function to hide the spinner and show the send button
    function hideUploadSpinner() {
        $('#uploadSpinner').hide();
        $('#sendButton').show();
    }

    // Function to show the error indicator
    function showErrorIndicator() {
        $('#uploadSpinner').hide();
        $('#sendButton').hide();
        $('#errorIndicator').show();
    }

    // Handle send button click for messages
    $('#sendButton').on('click', function() {
        const messageContent = $('#messageInput').val().trim();
        const selectedConversationId = $('.conversation-item.active').data('conversation-id');
        const file = $('#imageUploadInput')[0].files[0];

        // Check if a conversation is selected
        if (selectedConversationId && (messageContent || file)) {
            const formData = new FormData();
            formData.append('conversation_id', selectedConversationId);
            formData.append('sender_id', userId);
            formData.append('content', messageContent);

            // If a valid file is selected, append it to the FormData
            if (file && validateFile(file)) {
                formData.append('image', file);
                showUploadSpinner();
            }

            // Submit the message along with any attached image
            $.ajax({
                url: '../messenger/send_message.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Response from send_message.php:', response);
                    if (response.status === 'success') {
                        $('#messageInput').val(''); // Clear the input field
                        loadMessages(selectedConversationId); // Refresh message list
                        hideUploadSpinner(); // Hide spinner on success
                        resetUploadButton(); // Reset upload button if image was attached
                    } else {
                        hideUploadSpinner();
                        showErrorIndicator(); // Show error indicator if there's an issue
                        setTimeout(hideErrorIndicator, 3000); // Hide the error after 3 seconds
                    }
                },
                error: function(error) {
                    console.error('Error sending message:', error);
                    hideUploadSpinner();
                    showErrorIndicator();
                    setTimeout(hideErrorIndicator, 3000); // Hide the error after 3 seconds
                }
            });
        } else {
            alert('Please select a conversation and enter a message or attach an image.');
        }
    });

    // Function to validate the file type and size
    function validateFile(file) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        return validTypes.includes(file.type) && file.size <= maxFileSize;
    }

    // Function to hide the error indicator and show the send button
    function hideErrorIndicator() {
        $('#errorIndicator').hide();
        $('#sendButton').show();
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

    // Adjust the initial state based on screen width
    function adjustDrawerState() {
        if (window.innerWidth < 800) {
            // Start with the conversation list at full width and hide the message thread
            $('.conversation-list-container').css('width', '100%');
            $('.message-thread').hide();
            $('#startConversationButton').removeClass('hidden');
            $('#toggleConvoDrawer').html('⮞'); // Button indicates collapse
            isDrawerCollapsed = true;
        } else {
            // On larger screens, start with the drawer at 30% and message thread visible
            $('.conversation-list-container').css('width', '30%');
            $('.message-thread').show();
            $('#startConversationButton').removeClass('hidden');
            $('#toggleConvoDrawer').html('⮜'); // Button indicates expand
            isDrawerCollapsed = false;
        }
    }

    // Adjust drawer state on window resize
    $(window).on('resize', function() {
        adjustDrawerState();
    });

    // Initial setup based on window size
    adjustDrawerState();

    $('#toggleConvoDrawer').on('click', function() {
        if (isDrawerCollapsed) {
            if (window.innerWidth < 800) {
                // On mobile, expand to full width for the message thread, hide the drawer and the startConversationButton
                $('.conversation-list-container').css('width', '0');
                $('.message-thread').css('width', '100%').show();
                $('#startConversationButton').addClass('hidden');
                $('#toggleConvoDrawer').html('⮞');
            } else {
                // On larger screens, expand the drawer to 30% width
                $('.conversation-list-container').css('width', '30%');
                $('.message-thread').css('width', '70%').show();
                $('#toggleConvoDrawer').html('⮜');
                $('.conversation-item').removeClass('collapsed');
                $('.conversation-item').addClass('expanded');
                $('#startConversationButton').removeClass('hidden');
            }

            // Show conversation details after expanding

        } else {
            if (window.innerWidth < 800) {
                // On mobile, show only the conversation list and hide the message thread
                $('.conversation-list-container').css('width', '100%');
                $('.message-thread').hide();
                $('#startConversationButton').removeClass('hidden');
                $('#toggleConvoDrawer').html('>'); // Indicate that the drawer can be expanded
                $('.conversation-item').removeClass('collapsed');
                $('.conversation-item').addClass('expanded');
            } else {
                // On larger screens, collapse conversations to minimal view
                $('.conversation-list-container').css('width', '80px');
                $('.message-thread').addClass('expanded');
                $('#toggleConvoDrawer').html('>'); // Indicate that the drawer can be expanded
                $('#startConversationButton').addClass('hidden');
                $('.message-thread').css('width', 'calc(100% - 60px)').show();

            }

            // Hide conversation details when collapsed
            $('.conversation-item').addClass('collapsed');
        }

        isDrawerCollapsed = !isDrawerCollapsed; // Toggle the state
    });
});



</script>

<script src="../scripts/messenger.js?v=2.6"></script>




</body>
</html>