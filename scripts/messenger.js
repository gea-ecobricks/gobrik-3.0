  $(document).ready(function() {

    // Validate the uploaded file
  }
        function validateFile(file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            return validTypes.includes(file.type) && file.size <= maxFileSize;
        }


        // Handle file upload button click
        $('#uploadPhotoButton').on('click', function() {
            if (!$(this).hasClass('remove-attachment')) {
                $('#imageUploadInput').click(); // Trigger file input
            } else {
                resetUploadButton();
                $('#imageFileName').text(''); // Clear file name
                $('#imageUploadInput').val(''); // Reset file input
            }
        });

        // Handle file selection
        $('#imageUploadInput').on('change', function(event) {
            const file = event.target.files[0];
            if (file && validateFile(file)) {
                $('#imageFileName').text(file.name);
                showUploadSuccess();
            }
        });

        // Update the upload button to indicate success
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

        // Reset the upload button to its original state
        function resetUploadButton() {
            $('#uploadPhotoButton')
                .html('📸')
                .css('background', 'grey')
                .removeClass('attachment-added remove-attachment')
                .attr('title', 'Upload Photo');
        }

        // Handle hover behavior for attachment removal state
        $('#uploadPhotoButton').hover(
            function() {
                if ($(this).hasClass('remove-attachment')) {
                    $(this).html('❌');
                }
            },
            function() {
                if ($(this).hasClass('remove-attachment')) {
                    $(this).html('📎');
                }
            }
        );

        }