<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.123';
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
    const userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP
    const maxFileSize = 10 * 1024 * 1024; // 10 MB

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
                console.log('Response from get_messages.php:', response);
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
                ? `<a href="#" class="thumbnail-link" data-full-url="../${msg.image_url}">
                    <img src="../${msg.thumbnail_url}" alt="Image attachment" class="message-thumbnail" />
                   </a>`
                : '';

            const msgElement = `
                <div class="message-item ${messageClass}">
                    ${thumbnailHtml}
                    <p class="sender">${msg.sender_name}</p>
                    <p class="the-message-text">${msg.content}</p>
                    <p class="timestamp">${msg.created_at}</p>
                </div>
            `;
            messageList.append(msgElement);
        });

        messageList.scrollTop(messageList.prop("scrollHeight"));

        $('.thumbnail-link').on('click', function(event) {
            event.preventDefault();
            const fullUrl = $(this).data('full-url');
            openPhotoModal(fullUrl);
        });
    }

    // SECTION 4: User Search and Selection
    $(document).ready(function() {
        const selectedUsers = new Set();

        $('#startConversationButton').on('click', function() {
            $(this).addClass('hidden');
            $('#searchBoxContainer').removeClass('hidden');
            $('#userSearchInput').focus();
        });

        $('#userSearchInput').on('input', function() {
            const query = $(this).val().trim();
            if (query.length >= 4) {
                searchUsers(query);
            } else {
                $('#searchResults').empty();
            }
        });

        function searchUsers(query) {
            $('#userSearchSpinner').show();
            $.ajax({
                url: '../messenger/search_users.php',
                method: 'GET',
                data: { query: query, user_id: userId },
                success: function(response) {
                    $('#userSearchSpinner').hide();
                    if (response.status === 'success') {
                        renderSearchResults(response.users);
                    } else {
                        $('#searchResults').html('<p>No users found</p>');
                    }
                },
                error: function(error) {
                    $('#userSearchSpinner').hide();
                    console.error('Error searching users:', error);
                    $('#searchResults').html('<p>An error occurred while searching.</p>');
                }
            });
        }

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

                $('.search-result-item').on('click', function() {
                    const userId = $(this).data('user-id');
                    const userName = $(this).text();
                    if (selectedUsers.size < 5) {
                        selectedUsers.add(userId);
                        $('#selectedUsers').append(`<div class="selected-user-item" data-user-id="${userId}">${userName}</div>`);
                        $(this).remove();
                        $('#userSearchInput').val('');
                        $('#searchResults').empty();
                        $('#createConversationButton').prop('disabled', false);
                    }
                });
            } else {
                searchResults.html('<p>No users found</p>');
            }
        }

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

        $('#createConversationButton').on('click', createConversation);

        $('#selectedUsers').on('click', '.selected-user-item', function() {
            const userId = $(this).data('user-id');
            selectedUsers.delete(userId);
            $(this).remove();
            if (selectedUsers.size === 0) {
                $('#createConversationButton').prop('disabled', true);
            }
        });

        loadConversations();
    });

    // SECTION 5: JavaScript/jQuery for Sending Messages
    $(document).ready(function() {
        console.log('User ID is:', userId);

        $('#sendButton').on('click', function() {
            const messageContent = $('#messageInput').val().trim();
            const selectedConversationId = $('.conversation-item.active').data('conversation-id');
            const file = $('#imageUploadInput')[0].files[0];

            console.log('Selected Conversation ID:', selectedConversationId);

            if (selectedConversationId && (messageContent || file)) {
                const formData = new FormData();
                formData.append('conversation_id', selectedConversationId);
                formData.append('sender_id', userId);
                formData.append('content', messageContent);

                if (file && validateFile(file)) {
                    formData.append('image', file);
                    showUploadSpinner();
                }

                $.ajax({
                    url: '../messenger/send_message.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Response from send_message.php:', response);
                        if (response.status === 'success') {
                            $('#messageInput').val('');
                            loadMessages(selectedConversationId);
                            hideUploadSpinner();
                            resetUploadButton();
                        } else {
                            hideUploadSpinner();
                            showErrorIndicator();
                            setTimeout(hideErrorIndicator, 3000);
                        }
                    },
                    error: function(error) {
                        console.error('Error sending message:', error);
                        hideUploadSpinner();
                        showErrorIndicator();
                        setTimeout(hideErrorIndicator, 3000);
                    }
                });
            } else {
                alert('Please select a conversation and enter a message.');
            }
        });

        function validateFile(file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            return validTypes.includes(file.type) && file.size <= maxFileSize;
        }

        function showUploadSpinner() {
            $('#sendButton').hide();
            $('#errorIndicator').hide();
            $('#uploadSpinner').show();
        }

        function hideUploadSpinner() {
            $('#uploadSpinner').hide();
            $('#sendButton').show();
        }

        function showErrorIndicator() {
            $('#uploadSpinner').hide();
            $('#sendButton').hide();
            $('#errorIndicator').show();
        }

        function hideErrorIndicator() {
            $('#errorIndicator').hide();
            $('#sendButton').show();
        }

        function resetUploadButton() {
            $('#uploadPhotoButton')
                .html('📸')
                .css('background', '#434343')
                .removeClass('attachment-added remove-attachment')
                .attr('title', 'Upload Photo');
        }

        $('#uploadPhotoButton').on('click', function() {
            if (!$(this).hasClass('remove-attachment')) {
                $('#imageUploadInput').click();
            } else {
                resetUploadButton();
                $('#imageFileName').text('');
                $('#imageUploadInput').val('');
            }
        });

        $('#imageUploadInput').on('change', function(event) {
            const file = event.target.files[0];
            if (file && validateFile(file)) {
                $('#imageFileName').text(file.name);
                showUploadSuccess();
            } else {
                alert('🤔 Hmmm... looks like this isn\'t an image file, or else it\'s over 10MB. Please try another file.');
                resetUploadButton();
            }
        });

        function showUploadSuccess() {
            $('#uploadPhotoButton')
                .html('✔️')
                .css('background', 'var(--emblem-green)');

            setTimeout(function() {
                $('#uploadPhotoButton')
                    .html('📎')
                    .css('background', 'grey')
                    .addClass('attachment-added remove-attachment')
                    .attr('title', 'Click to remove attachment');
            }, 1000);
        }
    });

    // SECTION 6: Adjust Drawer for Mobile and Desktop
    $(document).ready(function() {
        let isDrawerCollapsed = false;

        function adjustDrawerState() {
            if (window.innerWidth < 800) {
                $('.conversation-list-container').css('width', '100%');
                $('.message-thread').hide();
                $('#toggleConvoDrawer').html('⮞');
                isDrawerCollapsed = true;
            } else {
                $('.conversation-list-container').css('width', '30%');
                $('.message-thread').show();
                $('#toggleConvoDrawer').html('⮜');
                isDrawerCollapsed = false;
            }
        }

        $(window).on('resize', adjustDrawerState);
        adjustDrawerState();

        $('#toggleConvoDrawer').on('click', function() {
            if (isDrawerCollapsed) {
                if (window.innerWidth < 800) {
                    $('.conversation-list-container').css('width', '0');
                    $('.message-thread').css('width', '100%').show();
                    $('#toggleConvoDrawer').html('>');
                } else {
                    $('.conversation-list-container').css('width', '30%');
                    $('.message-thread').css('width', '70%').show();
                    $('#toggleConvoDrawer').html('<');
                }
            } else {
                if (window.innerWidth < 800) {
                    $('.conversation-list-container').css('width', '100%');
                    $('.message-thread').hide();
                    $('#toggleConvoDrawer').html('>');
                } else {
                    $('.conversation-list-container').css('width', '80px');
                    $('.message-thread').css('width', 'calc(100% - 60px)').show();
                    $('#toggleConvoDrawer').html('>');
                }
            }

            isDrawerCollapsed = !isDrawerCollapsed;
        });
    });
</script>


<script src="../scripts/messenger.js?v=2"></script>




</body>
</html>