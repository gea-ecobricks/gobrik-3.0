

<?php require_once ("../meta/log-3-$lang.php");?>


<STYLE>


    #main {
        height: fit-content !important;
  padding-bottom: 100px;
    }


/* Ensure the parent container can resize and show content that expands */
#upload-success-message {
    position: relative;
    overflow: visible; /* Allows content to grow beyond its bounds */
    transition: height 0.3s ease; /* Smooth transition for height change */
}

.photo-container {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: visible; /* Make sure the container grows with the rotated image */
    margin: 0 auto;
    text-align: center;
    background: var(--lighter);
    width: 100%;  /* Adjust as needed */
    height: auto; /* Adjust as needed */
}

.rotatable-photo {
    max-width: 100%; /* Ensure image does not exceed the container width */
    height: auto;
    transition: transform 0.3s ease; /* Smooth transition for the rotation */
    display: block;
}

/* Rotate Controls */
.rotate-controls {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Rotate and Confirm Buttons */
.rotate-button, .confirm-button {
    font-size: 1.1em;
    color: var(--text-color);
    background-color: grey;
    border-radius: 50%;
    width: 40px;    /* Define fixed width to ensure circle shape */
    height: 40px;   /* Define fixed height to ensure circle shape */
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 5px;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s, background-color 0.2s;
}

.rotate-button:hover, .confirm-button:hover {
    opacity: 1;
}

/* Ensure the button text stays centered */
.rotate-button > span, .confirm-button > span {
    line-height: 1;
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



#upload-progress-button {
color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background-color: #12b712;
  background-size: 0% 100%;
  transition: background-size 0.5s ease;
  font-size: 1.3em;
  width: 100%;
  margin-top: 30px;
  }


  /* Style for the text form */
#add-vision-form {
    width: 100%;
/*     margin-top: 20px; */
}

/* Style for the textarea */
#vision_message {
    width: 100%;
    font-size: 1.3em;
    border-radius: 15px;
    padding: 10px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    resize: vertical; /* Allows vertical resizing only */
    max-width: 100%;
    min-height: 100px; /* Ensures a comfortable size for typing */
    line-height: 1.5em; /* Increases space between lines for readability */
}

/* Style for the character counter */
#character-counter {
    text-align: right;
    font-size: 0.9em;
    color: grey;
    margin-top: 5px;
}

/* Button group style */
.button-group {
    display: flex;
    gap: 10px;
    width: 100%;
    margin-top: 10px;
}

.confirm-button {
    flex-grow: 1;
    text-align: center;
    padding: 10px;
    cursor: pointer;
}

#skip-button {
    background: grey;
}


</style>

<?php require_once ("../header-2024.php");?>

