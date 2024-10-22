
<!--  Set any page specific graphics to preload-->

<?php require_once ("../meta/$page-$lang.php");?>


<!-- Include DataTables and jQuery Libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>



<STYLE>

        #main {
        height: fit-content !important;
        padding-bottom: 100px;
    }


/* MESSENGER CSS */
.hidden {
    display: none;
}


/* Start Conversation Button */
.start-convo-button {
    background: var(--emblem-green);
    color: white;
    padding: 5px 10px;
    border: 1px solid var(--emblem-green);
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    margin: auto;
    justify-content: center;
    text-align: left; /* Aligns text to the left */
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s ease, border 0.3s ease;
    width: 100%;
}

.start-convo-button:hover {
    background: var(--emblem-green-over);
    border-color: var(--emblem-green-over);
}

/* Create Conversation Button */
.create-button {
    background: grey;
    color: white;
    padding: 5px 10px;
    border: 1px solid grey;
    border-radius: 5px;
    cursor: not-allowed; /* Cursor shows as not-allowed when disabled */
    font-size: 1em;
    margin: auto;
    justify-content: center;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s ease, border 0.3s ease;
    width:100%;
}

.create-button:not(:disabled) {
    background: var(--emblem-blue);
    border-color: var(--emblem-blue);
    cursor: pointer;
}

.create-button:not(:disabled):hover {
    background: var(--emblem-blue-over);
    border-color: var(--emblem-blue-over);
}

.no-messages {
    display: flex;
    align-items: center; /* Vertically centers the content */
    justify-content: center; /* Horizontally centers the content */
    color: var(--subdued-text);
    height: 100%;
    text-align: center; /* Ensures text is centered in the element */
    font-size: 1.1em; /* Adjust as needed for better readability */
    padding: 20px; /* Optional: Adds some padding for spacing */
    background: var(--darker); /* Optional: Ensure background matches the message area */
    border-radius: 15px; /* Optional: Rounds the corners if needed */
}



.messenger-container {
    display: flex;
    height: calc(100vh - 150px); /* Adjust height as needed */
    border: 1px solid var(--settings-border);
    background: var(--darker);
    border-radius: 15px;
}

.conversation-list-container {
    width: 30%;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--settings-border);
    background: var(--darker);
}

.start-conversation-container {
    padding: 10px;
    background: var(--darker);
    border-bottom: 1px solid var(--settings-border);
    z-index: 1; /* Ensures it stays above the conversation list when scrolling */
}

#searchBoxContainer {
    margin-top: 10px;
}

#searchResults, #selectedUsers {
    margin-top: 10px;
    max-height: 150px;
    overflow-y: auto;
    border: 1px solid var(--settings-border);
    background: var(--darker);
}

.search-result-item, .selected-user-item {
    padding: 5px;
    cursor: pointer;
    border-bottom: 1px solid var(--settings-border);
    color: var(--text-color);
}

/* .selected-user-item {
    background-color: var(--advanced-background);
} */

.conversation-list {
    flex-grow: 1;
    overflow-y: auto;
    padding: 10px;
}

.conversation-item {
    position: relative; /* Allows the delete button to be positioned relative to this container */
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid var(--settings-border);
    cursor: pointer;
    color: var(--text-color);
}

.conversation-item.active {
    background-color: var(--lighter);
}

.conversation-item strong {
    color: var(--h1); /* Color for the other participants' names */
}

.timestamp {
    font-size: 0.8em;
    color: var(--subdued-text);
}

.message-thread {
    width: 70%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 10px;
}

#message-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    overflow-y: auto;
    flex-grow: 1;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--settings-border);
}


.message-input {
    display: flex;
/*     gap: 10px; */
    margin-top: 10px;
}

#messageInput {
    flex-grow: 1;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid grey;
    background: var(--darker);
    color: var(--text-color);
    font-size: 1.3em;
    border-radius: 10px 0px 0px 10px;
}

#sendButton {
    padding: 10px 15px;
    background-color: var(--emblem-pink);
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-left: -10px;
}

#sendButton:hover {
background-color: var(--emblem-pink-over);
}

.message-item {
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 5px;
    background-color: var(--advanced-background);
    color: var(--text-color);
    min-width: 50%;
}

.message-item {
    position: relative; /* Ensure the ::after element is positioned relative to the message box */
    padding: 10px;
    border-radius: 15px;
    max-width: 80%;
    word-wrap: break-word;
    margin-bottom: 10px;
}

.message-item.self {
    background-color: var(--advanced-background);
    color: #fff;
    align-self: flex-end; /* Aligns to the right */
    text-align: right;
    margin-right: 22px; /* Space to account for the spike */
}

.message-item.self::after {
    content: '';
    position: absolute;
    bottom: -17px; /* Position the spike below the message box */
    right: 22px; /* 22px away from the right edge */
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 18px 18px 0 0; /* Creates a right-angled triangle */
    border-color: var(--advanced-background) transparent transparent transparent; /* Color the spike */
    transform: rotate(-270deg); /* Rotate to create the angled effect */
}

.message-item:not(.self) {
    background-color: var(--emblem-blue);
    color: var(--text-color);
    align-self: flex-start; /* Aligns to the left */
    text-align: left;
    margin-left: 22px; /* Space to account for the spike */
}

.message-item:not(.self)::after {
    content: '';
    position: absolute;
    bottom: -18px; /* Position the spike below the message box */
    left: 22px; /* 22px away from the left edge */
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 18px 0 0 18px; /* Creates a left-angled triangle */
    border-color: var(--emblem-blue) transparent transparent transparent; /* Color the spike */
    transform: rotate(270deg); /* Rotate to create the angled effect */
}



.message-item .sender {
    font-weight: bold;
    color: var(--h1); /* Color for sender names */
}

.message-item .timestamp {
    font-size: 0.8em;
    color: var(--subdued-text);
}


#searchResults {
    position: relative;
    background: var(--darker);
    border: 1px solid var(--settings-border);
    max-height: 200px;
    overflow-y: auto;
    z-index: 2; /* Ensures it appears above other elements */
}

.search-result-item {
    padding: 8px;
    border-bottom: 1px solid var(--settings-border);
    cursor: pointer;
    color: var(--text-color);
}

.search-result-item:hover {
    background-color: var(--advanced-background);
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid var(--settings-border);
    cursor: pointer;
    color: var(--text-color);
}

.conversation-item.active {
    background-color: var(--lighter);
    border-radius: 10px 0px 0px 10px;
}

.conversation-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--text-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 10px 10px auto 0px;
}

.conversation-icon .initial {
    color: var(--same);
    font-size: 1.2em;
    font-weight: bold;
}

.conversation-details {
    flex-grow: 1;
    overflow: hidden; /* Ensures no overflow */
}

.conversation-details strong {
    color: var(--h1); /* Color for the other participants' names */
    font-size: 1em;
}


.delete-conversation {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 0.9em;
    color: var(--subdued-text);
    cursor: pointer;
    background: transparent;
    border: none;
    padding: 2px;
    line-height: 1;
}

.convo-preview-text {
    color: var(--text-color);
    font-size: 0.9em;
    overflow: hidden;
    text-overflow: ellipsis; /* Adds "..." at the end if the text is too long */
    white-space: nowrap; /* Ensures the text stays on a single line */
}

.timestamp {
    font-size: 0.8em;
    color: var(--subdued-text);
    margin-top: 2px;
}

.message-input {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

#messageInput {
    flex-grow: 1;
    padding: 10px;
    border-radius: 10px 0px 0px 10px;
    border: 1px solid grey;
    background: var(--darker);
    color: var(--text-color);
    font-size: 1.2em;
    margin-left: -2px;
}

#sendButton {
    width: 50px; /* Adjust width to fit the triangle */
    background-color: var(--emblem-pink);
    border: none;
    border-radius: 0 15px 15px 0; /* Flat left edge, rounded right edge */
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s ease;
}

#sendButton::before {
    content: '';
    display: block;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 10px 0 10px 20px; /* Creates a right-facing triangle */
    border-color: transparent transparent transparent #fff; /* White triangle color */
    margin-left: 3px; /* Adjust position if needed */
}

#sendButton:hover {
    background-color: var(--emblem-pink-over);
}


.spinner-right {

position: absolute;
  top: 13px;
  right: 11px;
  transform: translateY(-50%);
  width: 15px;
  height: 15px;
  border: 4px solid rgba(0,0,0,0.1);
    border-top-width: 4px;
    border-top-style: solid;
    border-top-color: rgba(0, 0, 0, 0.1);
  border-top: 4px solid var(--emblem-blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  display: none;

  }

.start-convo-button, .toggle-drawer-button {
    display: inline-block;
    padding: 5px 10px;
    background: var(--emblem-green);
    color: white;
    border: 1px solid var(--emblem-green);
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    margin: auto;
    transition: background 0.3s ease, border 0.3s ease;
}

.start-convo-button {
    text-align: left;
    width: calc(100% - 60px); /* Adjust width to accommodate toggle button */
}

.toggle-drawer-button {
    width: 50px;
    text-align: center;
    background: var(--emblem-blue);
    border-radius: 5px;
    margin-left: 5px;
}

.toggle-drawer-button:hover {
    background: var(--emblem-blue-over);
}












    .preview-text {
        font-family: 'Mulish', Arial, Helvetica, sans-serif;
        font-weight: 300;
        -webkit-font-smoothing: antialiased;
        color: var(--text-color);
        margin-top: 15px;
        margin-bottom: 15px;
    }



    @media screen and (min-width: 700px) {
        .preview-text {
            font-size: 1em;
        }

    }

    @media screen and (max-width: 700px) {
        .preview-text {
            font-size: 0.8em;
        }


        #language-code {
            display:none;}
        }
    }





#main-background {
  background-size: cover;

}


/* Media Query for screens under 700px */
@media screen and (max-width: 700px) {
  .form-container {
    width: calc(100% - 40px);
    margin: 0;
    /* border: none; */
    padding: 20px 20px 0 20px;
    max-width: 600px;
    padding: 20px;
    position: relative;
    margin-top: 80px;

  }
}

#featured_image {
  margin-bottom: 8px;
  margin-top: 8px;
  padding: 5px;
  font-size: 1em;
}

#tmb_featured_image {
  margin-bottom: 8px;
  margin-top: 8px;
  padding: 5px;
  font-size: 1em;
}

/* Centering the form vertically on larger screens */
@media screen and (min-width: 701px) {
  /* #form-submission-box {
    display: flex;
    align-items: center;
    justify-content: center;

  } */

  .form-container {
    margin-top: auto;
    margin-bottom: auto;
    padding: 30px;
    margin-top: 110px;

  }
}

.module-btn {
  background: var(--emblem-green);
  width: 100%;
  display: flex;
}

.module-btn:hover {
  background: var(--emblem-green-over);
}


.go-button {
    padding: 10px 20px;
    border: none;
    color: white;
    transition: background-color 0.3s, cursor 0.3s;
     padding: 10px 20px;
  border: none;
  border-radius: 6px;
   font-size: 1.3em;

    background-color: var(--button-2-1);
    cursor: pointer;
    margin: 10px;
}

/* Hover effect for enabled state */
.go-button:hover {
    background-color: var(--button-2-1-over);
}

.underline-link {
color: var(--text-color);
}


#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}




    </style>





<?php require_once ("../header-2024.php");?>



