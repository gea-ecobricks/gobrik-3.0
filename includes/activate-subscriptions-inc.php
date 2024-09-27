

<?php require_once ("../meta/$page-$lang.php");?>


<STYLE>

       #main {
        height: fit-content !important;
  padding-bottom: 100px;
    }





/* Container for subscription boxes */
/* Container for subscription boxes */
.subscription-boxes {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

/* Individual subscription box */
.sub-box {
    display: flex;
    position: relative;
    border: 1px solid rgba(128, 128, 128, 0.5);
    border-radius: 10px;
    padding: 15px;
    transition: border 0.5s, background-color 0.5s, color 0.5s;
    cursor: pointer;
    width: calc(50% - 20px); /* Two columns when screen width is above 1000px */
    box-sizing: border-box;
    background-color: transparent; /* Default background */
    align-items: stretch;
}

/* Checkbox for selection */
.sub-checkbox {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
    cursor: pointer;
    opacity: 1; /* Ensure checkbox is visible */
}

/* Label for the checkbox */
.checkbox-label {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 20px;
    height: 20px;
    border: 1px solid grey;
    border-radius: 4px;
    cursor: pointer;
}

/* Style for checked state */
.sub-checkbox:checked + .checkbox-label {
    background-color: green;
    border-color: green;
}

/* Image covering 25% of the sub-box */
.sub-image {
    width: 25%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-color: grey; /* Default grey background */
    border-radius: 10px 0 0 10px;
    margin-right: 15px;
}

/* Custom images for specific sub-box IDs */
#62943b9aad0b695aa46139b0 .sub-image {
    background-image: url('../webps/earthen-newsletter-image.webp');
}

#6621d4f55e227d049e56f404 .sub-image {
    background-image: url('../webps/trainer-newsletter-image.webp');
}

#662352b4d27acf008a160ac2 .sub-image {
    background-image: url('../webps/pelatih-newsletter-image.webp');
}

#663b20e9d27acf008a250eb0 .sub-image {
    background-image: url('../webps/ayyew-newsletter-image.webp');
}

/* Content area of the box */
.sub-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: left;
}

/* Sub-header to group the icon and title */
.sub-header {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

/* Icon inside the sub-header */
.sub-icon {
    width: 40px;
    height: 40px;
    background-size: cover;
    background-position: center;
    background-color: grey; /* Default grey background */
    border-radius: 4px;
    margin-right: 10px;
}

/* Custom icons for specific sub-box IDs */
#62943b9aad0b695aa46139b0 .sub-icon {
    background-image: url('../icons/earthen-newsletter-icon.webp');
}

#6621d4f55e227d049e56f404 .sub-icon {
    background-image: url('../icons/trainer-newsletter-icon.webp');
}

#662352b4d27acf008a160ac2 .sub-icon {
    background-image: url('../icons/pelatih-newsletter-icon.webp');
}

#663b20e9d27acf008a250eb0 .sub-icon {
    background-image: url('../icons/ayyew-newsletter-icon.webp');
}

/* Grouping text elements */
.sub-header-text {
    display: flex;
    flex-direction: column;
}

/* Text styles */
.sub-name {
    font-size: 1.3em;
    font-family: 'Mulish', sans-serif;
    color: var(--h1);
    margin: 0;
}

.sub-sender-name {
    font-size: 0.9em;
    font-family: 'Mulish', sans-serif;
    color: var(--subdued-text);
    margin-top: 2px;
}

.sub-description {
    font-size: 1em;
    font-family: 'Mulish', sans-serif;
    color: var(--text-color);
    margin: 10px 0;
}

.sub-lang {
    font-size: 0.9em;
    font-family: 'Mulish', sans-serif;
    color: var(--subdued-text);
}

/* Hover effect changes text color */
.sub-box:hover .sub-name,
.sub-box:hover .sub-sender-name,
.sub-box:hover .sub-description,
.sub-box:hover .sub-lang {
    color: var(--h1);
}

/* Hover effect changes background color */
.sub-box:hover {
    background-color: var(--lighter);
}

/* When box is selected, set background color */
.sub-box.selected {
    background-color: var(--darker);
}

/* Responsive behavior: single column for screen widths below 1000px */
@media (max-width: 1000px) {
    .sub-box {
        width: 100%;
    }
}

/* Submit button at the bottom of the form */
.submit-button {
    display: block;
    margin: 20px auto;
    padding: 10px 20px;
    background-color: green;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.submit-button:hover {
    background-color: darkgreen;
}




//Next


















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
    #second-code-confirm {
        display: none;
    }


    .hidden {
        display: none;
    }
    .error {
        color: red;
    }
    .success {
        color: green;
    }


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



</style>





<?php require_once ("../header-2024.php");?>



