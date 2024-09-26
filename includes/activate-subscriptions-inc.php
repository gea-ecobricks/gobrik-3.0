
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>


<STYLE>

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


//SUBSCRIPTION STYLES


/* Container for subscription boxes */
.subscription-boxes {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

/* Individual subscription box */
.subscription-box {
    display: flex;
    flex-direction: row;
    border: 1px solid grey;
    border-radius: 8px;
    padding: 15px;
    align-items: center;
    transition: border-color 0.3s;
    cursor: pointer;
    width: calc(50% - 20px); /* Two columns when screen width is above 1000px */
}

/* Icon inside the box */
.subscription-icon {
    width: 70px;
    height: 70px;
    border-radius: 4px;
    margin-right: 15px;
}

/* Content area of the box */
.subscription-content {
    flex: 1;
}

/* Style for titles, authors, descriptions, and languages */
.subscription-content h4 {
    margin: 0;
}

.subscription-by {
    font-family: 'Mulish', sans-serif;
    font-size: 12px;
    color: grey;
    margin: 5px 0;
    text-align: left;
}

.subscription-language {
    font-family: 'Mulish', sans-serif;
    font-size: 12px;
    color: grey;
    margin: 5px 0;
    text-align: right;
}

/* Light colors for the icons */
.light-red {
    background-color: #ffcccc;
}

.light-yellow {
    background-color: #ffffcc;
}

.light-blue {
    background-color: #cceeff;
}

.light-green {
    background-color: #ccffcc;
}

/* Hover effect changes border color */
.subscription-box:hover {
    border-color: currentColor;
}

/* When box is selected, change border to strong color */
.subscription-box.active[data-color="red"] {
    border-color: red;
}

.subscription-box.active[data-color="yellow"] {
    border-color: yellow;
}

.subscription-box.active[data-color="blue"] {
    border-color: blue;
}

.subscription-box.active[data-color="green"] {
    border-color: green;
}

/* Responsive behavior: single column for screen widths below 1000px */
@media (max-width: 1000px) {
    .subscription-box {
        width: 100%;
    }
}


</style>





<?php require_once ("../header-2024.php");?>



