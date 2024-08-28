
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>


<STYLE>

#main {
    height: fit-content;
}


.module-btn {
  background: var(--emblem-green);
  width: 100%;
  display: flex;
}

.module-btn:hover {
  background: var(--emblem-green-over);
}

#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}

form input:focus {
  border: 2px solid #160E21;
  box-shadow: 0px 0px 8px 4px #78F4F4;

  }

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

#dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    z-index: 100;
    width: 200px;
}

.dropdown-item {
    padding: 10px;
    cursor: pointer;
}

.dropdown-item.disabled {
    color: #999;
    cursor: not-allowed;
}

.dropdown-item:hover:not(.disabled) {
    background-color: #f0f0f0;
}



/* TOGGLE LOGIN SWITCH */

 .toggle-container {
            position: relative;
            width: 400px;
            height: 45px;
            background-color: grey;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.3);
            margin: auto;
        }

        .toggle-container input[type="radio"] {
            display: none;
        }

        .toggle-button {
            position: absolute;
            top: 0;
            height: 100%;
            line-height: 45px; /* Center text vertically */
            color: white;
            opacity: 0.25;
            transition: width 0.5s ease-in-out, opacity 0.3s ease-in-out;
            z-index: 1;
            font-size: 15px;
            cursor: pointer; /* Change cursor to pointer */
            font-family: 'Mulish',sans-serif;
        }

        .toggle-button.password {
            left: 0;
            width: 75%; /* Initial width */
            text-align: center;
        }
        .toggle-button.code {
            right: 0;
            width: 25%; /* Initial width */
            text-align: center;
        }
        .toggle-container .slider {
            position: absolute;
            background-color: green;
            border-radius: 25px;
            width: 75%;
            height: 100%;
            transition: all 0.5s ease-in-out;
            z-index: 0;
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.2),
                        inset 0 -2px 5px rgba(0, 0, 0, 0.3);
        }

        #password:checked ~ .slider {
            left: 0;
        }
        #code:checked ~ .slider {
            left: 25%;
        }
        #password:checked ~ .toggle-button.password {
            opacity: 1;
            width: 75%; /* Reduced width when selected */
        }
        #password:checked ~ .toggle-button.code {
            opacity: 0.25;
            width: 25%; /* Expanded width when the other option is selected */
        }
        #code:checked ~ .toggle-button.code {
            opacity: 1;
            width: 75%; /* Reduced width when selected */
        }
        #code:checked ~ .toggle-button.password {
            opacity: 0.25;
            width: 25%; /* Expanded width when the other option is selected */
        }

        /* New Button Styles */
        .login-button-75, .code-button-75 {
            position: absolute;
            top: 0;
            height: 100%;
            border: none;
            border-radius: 25px;
            color: white;
            font-size: 15px;
            line-height: 45px;
            text-align: center;
            background-color: green;
            box-shadow: 0 5px 6px rgba(0, 0, 0, 0.4), inset 0 -2px 1px rgba(0, 0, 0, 0.4);
            transition: opacity 0.5s ease-in-out;
            z-index: 1;
            cursor: pointer;
        }

        .login-button-75 {
            width: 75%;
            left: 0;
            font-size:17px;
        }

        .login-button-75:hover {
        border: 5px #14ff00 outset;
        line-height: 45px;
        }
        .code-button-75 {
            width: 75%;
            right: 0;
            opacity: 0;
            font-size:17px;
        }

        .hidden {
            display: none;
        }



  .code-boxes {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .code-box {
        text-align: center;
        font-family: 'Arvo', serif;
        font-size: 2em;
        max-width: 3em;
    }


</style>


<?php require_once ("../header-2024.php");?>



