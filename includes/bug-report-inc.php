
<!--  Set any page specific graphics to preload-->

<?php require_once ("../meta/$page-$lang.php");?>


<!-- Include DataTables and jQuery Libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>



<STYLE>

/*         #main { */
/*         height: fit-content !important; */
/*         padding-bottom: 100px; */
/*     } */


/* MESSENGER CSS */
/* .hidden { */
/*     display: none; */
/* } */

#bugReportInput {
    width: 100%; /* Ensure the input fills the wrapper */
    padding: 10px;
    border-radius: 5px;
    border: 1px solid grey;
    background: var(--darker);
    color: var(--text-color);
    font-size: 1.1em;
    margin-bottom: 10px;
    resize: vertical;
    box-sizing: border-box; /* Include padding and border in width calculations */
}

#bugReportSubmit {
    background: var(--emblem-green);
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    border: 1px solid var(--emblem-green);
    cursor: pointer;
    font-size: 1em;
    transition: background 0.3s ease, border 0.3s ease;
    margin: auto;
    display: block;
}

#bugReportSubmit:hover {
    background: var(--emblem-green-over);
    border-color: var(--emblem-green-over);
}

#feedbackMessage {
    margin-top: 10px;
    font-size: 1em;
    color: var(--emblem-green);
    text-align: center;
}

.bug-report-input-wrapper {
    position: relative;
    width: 100%; /* Ensure the wrapper takes up the full available width */
    box-sizing: border-box; /* Include padding and border in width calculations */
}

#uploadPhotoButton {
    position: absolute;
    bottom: 20px; /* Adjusted to be 10px above the bottom of the form field */
    right: 10px;
    width: 40px;
    height: 40px;
    background: grey;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 1.2em;
    display: flex;
    align-items: center; /* Center the icon vertically */
    justify-content: center; /* Center the icon horizontally */
    cursor: pointer;
    transition: background 0.3s ease;
}

#uploadPhotoButton:hover {
    background: var(--emblem-blue);
}

#uploadPhotoButton.uploading::after {
    content: '';
    position: absolute;
    top: 50%; /* Center the spinner vertically */
    left: 50%; /* Center the spinner horizontally */
    transform: translate(-50%, -50%); /* Adjusts for perfect centering */
    width: 20px;
    height: 20px;
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-top: 4px solid var(--emblem-blue);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.image-file-name {
    position: absolute;
    bottom: 25px; /* Adjusted position for better alignment with the upload button */
    right: 60px; /* Position to the left of the button */
    font-size: 0.8em;
    color: var(--subdued-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px; /* Adjust as needed */
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}




/*





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


 *//* Media Query for screens under 700px *//*
@media screen and (max-width: 700px) {
  .form-container {
    width: calc(100% - 40px);
    margin: 0;
     *//* border: none; *//*
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

 *//* Centering the form vertically on larger screens *//*
@media screen and (min-width: 701px) {
   *//* #form-submission-box {
    display: flex;
    align-items: center;
    justify-content: center;

  } *//*

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

 *//* Hover effect for enabled state *//*
.go-button:hover {
    background-color: var(--button-2-1-over);
}

.underline-link {
color: var(--text-color);
} */


#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}




    </style>





<?php require_once ("../header-2024.php");?>



