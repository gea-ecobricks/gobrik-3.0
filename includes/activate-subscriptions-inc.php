
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>


<STYLE>

/* Container for subscription boxes */
.subscription-boxes {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

/* Individual subscription box */
.sub-box {
    display: flex;
    border: 1px solid rgba(128, 128, 128, 0.5);
    border-radius: 10px;
    padding: 15px;
    align-items: center;
    transition: border 0.5s;
    cursor: pointer;
    width: calc(50% - 20px); /* Two columns when screen width is above 1000px */
    box-sizing: border-box;
}

/* Icon inside the box */
.sub-icon {
    width: 70px;
    height: 70px;
    background-color: grey;
    border-radius: 4px;
    margin-right: 15px;
}

/* Content area of the box */
.sub-content {
    text-align: left;
    flex: 1;
}

/* Style for titles, authors, descriptions, and languages */
.sub-name {
    margin: 0;
}

.sub-sender-name {
    font-family: 'Mulish', sans-serif;
    font-size: 12px;
    color: grey;
    margin: 5px 0;
    text-align: left;
}

.sub-description {
    margin: 5px 0;
}

.subscription-language {
    font-family: 'Mulish', sans-serif;
    font-size: 12px;
    color: grey;
    margin: 5px 0;
    text-align: left;
}

/* Hover effect changes border color */
.sub-box:hover {
    border: 2px solid green;
}

/* When box is selected, change border to strong color */
.sub-box.selected {
    border: 2px solid green;
    border-color: rgba(0, 128, 0, 1);
}

/* Responsive behavior: single column for screen widths below 1000px */
@media (max-width: 1000px) {
    .sub-box {
        width: 100%;
    }
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



