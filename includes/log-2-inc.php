

<?php require_once ("../meta/log-2-$lang.php");?>


<STYLE>

#page-content {
    filter: blur(5px); /* Apply blur by default */
    transition: filter 0.3s ease; /* Smooth transition for the blur */
}

body {
overflow: hidden;
}




    #main {
        height: fit-content !important;
  padding-bottom: 100px;
    }


        .advanced-box-content {
    padding: 2px 15px 15px 15px;
    max-height: 0;  /* Initially set to 0 */
    overflow: hidden;  /* Hide any overflowing content */
    transition: max-height 0.5s ease-in-out;  /* Transition effect */
	font-size:smaller;
	margin-top:-10px;
}


.dropdown {
  float: right;
  overflow: hidden;
  margin-bottom: -10px;
}

#splash-bar {
  background-color: var(--top-header);
  filter: none !important;
  margin-bottom: -200px !important;
}
.photo-upload-container {
    width: 100%;
    padding: 10px;
    background-color: var(--lighter);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px; /* Adds space between elements */
    margin-bottom: 30px;
}

.custom-file-upload {
    display: inline-block;
    padding: 10px 20px;
    font-size: 1.3rem;
    color: var(--h1);
    background-color: grey;
    border: 2px solid transparent;
    border-radius: 6px;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s, border-color 0.3s;
}

.custom-file-upload:hover {
    background-color: var(--accordion-background); /* Lighten the grey background on hover */
    border-color: var(--text-color); /* Changes border color on hover */
}

.custom-file-upload input[type="file"] {
    display: none; /* Hide the actual file input */
}

.file-name {
    margin-top: 8px;
    font-size: 1rem;
    color: var(--text-color);
}

.form-caption {
    font-size: 1rem;
    color: var(--text-color);
/*     text-align: center; */
}



/* #upload-progress-button { */
/* color: white; */
/*   padding: 10px 20px; */
/*   border: none; */
/*   border-radius: 4px; */
/*   cursor: pointer; */
/*   background-color: #12b712; */
/*   background-size: 0% 100%; */
/*   transition: background-size 0.5s ease; */
/*   font-size: 1.3em; */
/*   width: 100%; */
/*   margin-top: 30px; */
/*   } */

/*   #upload-progress-button.progress-bar { */
/*     background-color: #4caf50;  *//* Green background to show progress */
/*     background-size: 0% 100%;  *//* Start with 0% background */
/*     transition: background-size 0.4s ease;  *//* Smooth transition */
/* } */







input[type="submit"] {
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background-color: #12b712; /* Initial background color */
  background-size: 0% 100%; /* Initial background size (progress bar) */
  transition: background-size 0.5s ease; /* Transition effect for smooth progress */
  font-size: 1.3em;
  width: 100%;
  margin-top: 30px;
}

/* Specify the progress bar color */
input[type="submit"].progress-bar {
  background: url(../svgs/square-upload-progress.svg) left center repeat-y, gray; /* Combined background */
  background-size: auto; /* Auto size for image background */
}


input[type="submit"]:hover {
  background-color: green;
}

.spinner-photo-loading {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: #ffffff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}



</style>

<?php require_once ("../header-2024.php");?>

