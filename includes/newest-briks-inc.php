
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>


<!-- Include DataTables and jQuery Libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>



<STYLE>

.serial-button {
    color: var(--text-color);
    border: 1px solid;
    padding: 10px;
    border-radius: 5px;
    background: rgba(116, 202, 244, 0.32); /* var(--darker); */
    cursor: pointer;
    position: relative; /* Enables positioning of pseudo-elements */
    transition: background 0.3s ease;
    text-decoration: none; /* Remove underline from links */
    display: inline-block; /* Makes button behave like a block element */
    overflow: hidden; /* Ensures that the content stays within the button */
}

.serial-button:hover {
    background: rgba(116, 202, 244, 0.8); /* var(--lighter); */
}

/* Hide the original text when hovering */
.serial-button:hover > span {
    visibility: hidden; /* Hide the serial number text */
}

/* Display the magnifying glass on hover */
.serial-button::before {
    content: "🔎";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0; /* Start hidden */
    transition: opacity 0.2s ease; /* Smooth transition */
    font-size: 1.2em; /* Adjust size as needed */
    line-height: 1;
}

.serial-button:hover::before {
    opacity: 1; /* Show the magnifying glass */
}



/* Media query for screens less than 769px wide */
@media screen and (max-width: 768px) {
    /* Hide the "Location" and "Weight" table headers */
    #latest-ecobricks th:nth-child(2), /* Weight column header */
    #latest-ecobricks th:nth-child(3)  /* Location column header */ {
        display: none;
    }

    /* Hide the "Location" and "Weight" table cells */
    #latest-ecobricks td:nth-child(2), /* Weight column cell */
    #latest-ecobricks td:nth-child(3)  /* Location column cell */ {
        display: none;
    }
}

    #main {
        height: fit-content;
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




        /* Style for the DataTables length menu (dropdown) */
        .dataTables_length {
            margin-bottom: 20px;
        }
        .dataTables_length label {
            font-size: 14px;
            font-weight: bold;
            display: ruby;
        }
        .dataTables_length select {
            padding: 5px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-left: 5px;
        }

        /* Style for the DataTables search input */
        .dataTables_filter {
            margin-bottom: 20px;
        }
        .dataTables_filter label {
            font-size: 14px;
            font-weight: bold;
        }
        .dataTables_filter input {
            padding: 5px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-left: 5px;
            width: 250px; /* Adjust width as needed */
        }

        /* Hide certain columns based on screen size */
        @media (min-width: 769px) and (max-width: 1200px) {
            #latest-ecobricks th:nth-child(3),
            #latest-ecobricks td:nth-child(3),
            #latest-ecobricks th:nth-child(4),
            #latest-ecobricks td:nth-child(4),
            #latest-ecobricks th:nth-child(5),
            #latest-ecobricks td:nth-child(5) {
                display: none;
            }
        }

        @media (max-width: 768px) {
            #latest-ecobricks th:nth-child(2),
            #latest-ecobricks td:nth-child(2),
            #latest-ecobricks th:nth-child(3),
            #latest-ecobricks td:nth-child(3),
            #latest-ecobricks th:nth-child(4),
            #latest-ecobricks td:nth-child(4),
            #latest-ecobricks th:nth-child(5),
            #latest-ecobricks td:nth-child(5) {
                display: none;
            }
        }
    </style>





<?php require_once ("../header-2024.php");?>



