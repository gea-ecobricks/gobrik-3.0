
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

.selected-user-item {
    background-color: var(--advanced-background);
}

.conversation-list {
    flex-grow: 1;
    overflow-y: auto;
    padding: 10px;
}

.conversation-item {
    padding: 10px;
    border-bottom: 1px solid var(--settings-border);
    cursor: pointer;
    color: var(--text-color);
}

.conversation-item.active {
    background-color: var(--advanced-background);
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
    overflow-y: auto;
    flex-grow: 1;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--settings-border);
}

.message-input {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

#messageInput {
    flex-grow: 1;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid grey;
    background: var(--darker);
    color: var(--text-color);
}

#sendButton {
    padding: 10px 15px;
    background-color: #007BFF;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.message-item {
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 5px;
    background-color: var(--advanced-background);
    color: var(--text-color);
}

.message-item.self {
    background-color: #007BFF;
    color: #fff;
    align-self: flex-end;
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



