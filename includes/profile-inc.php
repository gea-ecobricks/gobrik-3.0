
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>


<!-- jQuery and jQuery UI (required for autocomplete) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<STYLE>



    #main {
        height: fit-content;
    }



  #language-code {
            display:none;}
        }
    }



/* Suggestions box container */
#community-suggestions {
    background-color: var(--main-background); /* White background for suggestions */
    width: calc(100% - 30px); /* Make the suggestions box the same width as the input */
    max-height: 150px; /* Limit the height */
    overflow-y: auto; /* Allow scrolling if suggestions exceed max height */
    z-index: 1000; /* Ensure the dropdown is above other elements */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for the dropdown */
    margin-top: -3px;
}

/* Individual suggestion items */
.suggestion-item {
    padding: 10px;
    cursor: pointer;
    font-size: 1em;
    color: var(--text-color);
    background-color: var(--background-main); /* White background for individual items */
}

.suggestion-item:hover {
    background-color: var(--button-2-1); /* Light gray background on hover */
}

#watershed-suggestions {
    background-color: var(--main-background); /* White background for suggestions */
    width: calc(100% - 30px); /* Make the suggestions box the same width as the input */
    max-height: 150px; /* Limit the height */
    overflow-y: auto; /* Allow scrolling if suggestions exceed max height */
    z-index: 1000; /* Ensure the dropdown is above other elements */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for the dropdown */
    margin-top: -3px;
}


.dropdown {
  float: right;
  overflow: hidden;
  margin-bottom: -10px;
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



#buwana-account {
    display: flex; /* Use flexbox for layout */
    flex-wrap: wrap; /* Allow wrapping if the content overflows */
    gap: 20px; /* Add spacing between columns */
}

.left-column, .right-column {
    flex: 1; /* Make columns flexible */
    min-width: 250px; /* Minimum width for responsiveness */
}

@media screen and (min-width: 900px) {
    #buwana-account {
        flex-direction: row; /* Align items in a row for larger screens */
    }
    .left-column {
        max-width: 40%; /* Set maximum width for the left column */
    }
    .right-column {
        max-width: 60%; /* Set maximum width for the right column */
    }
}

@media screen and (max-width: 900px) {
    #buwana-account {
        flex-direction: column; /* Stack columns for smaller screens */
    }
}

.form-item {
    margin-bottom: 15px; /* Spacing between form items */
}

.submit-button-container {
    text-align: center;
    width: 100%;
}



</style>





<?php require_once ("../header-2024.php");?>



