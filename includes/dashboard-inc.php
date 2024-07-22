
<!--  Set any page specific graphics to preload-->

<!--  Set any page specific graphics to preload
<link rel="preload" as="image" href="../webps/ecobrick-team-blank.webp" media="(max-width: 699px)">
<link rel="preload" as="image" href="../svgs/richard-and-team-day.svg">
<link rel="preload" as="image" href="../svgs/richard-and-team-night.svg">
<link rel="preload" as="image" href="../webps/biosphere2.webp">
<link rel="preload" as="image" href="../webps/biosphere-day.webp">-->

<?php require_once ("../meta/$page-$lang.php");?>


<style>


<STYLE>




    .signup-team {
          margin: auto;
            margin-top: auto;
            margin-bottom: auto;
          position: relative;
          z-index: 11;
          background: url(../svgs/signup-team.svg?v=2) no-repeat center;
            background-size: auto;
          background-size: contain;
          height: fit-content;
          text-align: center;
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


    .step-graphic {
    width:fit-content;
    margin:auto;
    }

    @media screen and (min-width: 700px) {
        .preview-text {
            font-size: 1em;
        }

        .brik-type-image {
            height: 300px;
            width: 300px;
        }

        .step-graphic {
            margin-left:0px;
        }
    }

    @media screen and (max-width: 700px) {
        .preview-text {
            font-size: 0.8em;
        }
        .brik-type-image img {
            height: 200px;
            width: 200px;
        }

        #language-code {
            display:none;}
        }
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

#registration-footer {

  display:none !important;
}

#serial-select ul {
  list-style: none;
  padding: 0;
}


.form-item li:hover {
  background: var(--emblem-blue);
  cursor: pointer;
  padding:3px;
}

#serial-select {
  background: var(--advanced-background);
  width: 130px;
  margin-top: -10px;
  padding: 10px 10px 10px 20px;
  border-radius: 0px 0px 10px 10px;
  position: absolute;
  z-index: 100;
  margin-left: 15px;
  display: none;
}

.splash-image {display:flex;}

.splash-image img {margin-right: auto; margin-left: 0px;}


@media screen and (max-width: 700px) {
	.splash-content-block {
        background-color: var(--top-header);
        filter: none !important;
        min-height: 20vh !important;
        height: 20vh !important;

	}

  /*.splash-image {display: none !important;}*/

  /* .splash-image img  {height: 200px;} */
}


@media screen and (min-width: 700px) {
	.splash-content-block {
        background-color: var(--top-header);
        filter: none !important;
        min-height: 20vh !important;
}
}

@media screen and (max-width: 700px) {
.splash-heading {
	font-size: 2.5em !important;
	line-height: 1.1;
	margin: 10px 0px;
	text-align: center;
}
}

@media screen and (min-width: 700px) {
.splash-heading {
	font-size: 3.1em !important;
}
}

#splash-bar {

    background-color: var(--top-header);
    filter: none !important;
    margin-bottom: -200px !important;

}

#main-background {
  background-size: cover;

}

#main {
  background-color: #0003 !important;
}

/*
#form-submission-box {
  font-family: "Mulish", sans-serif;
} */

/* h2 {
  font-family: "Arvo", serif;
  color: var(--h1);
} */

.form-item {
    margin-top: 10px;
    margin-bottom: 10px;
}
.form-caption {
  font-family: "Mulish", sans-serif;
  font-weight: 300;
  color: var(--text-color);
  font-size: 1.0em;
  margin-top: -5px;
}



label {
  font-family: "Mulish", sans-serif;
  font-weight: 500;
  color: var(--text-color);
  font-size: 1.3em;
}


/* .form-item input { */
/*   background: var(--input-background); */
/*   font-size: 1.3em; */
/* } */

.form-item textarea {
  background: var(--input-background);
  font-size: 1.3em;
}

.form-item select {
  background: var(--input-background);
  font-size: 1.2em;
  padding: 5px;
  border-radius: 5px;
  margin-top: 9px;
  margin-bottom: 10px;
}

input[type="text"],
input[type="number"],
textarea,
input[type="date"] {
  font-family: "Mulish", sans-serif;
  font-weight: 300;
  width: 100%;
  padding: 8px;
  margin-bottom: 10px;
  border: 1px solid var(--divider-line);
  border-radius: 5px;
  box-sizing: border-box;
  margin-top: 8px;
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



.form-field-error {
  color: red;
  margin-top: -15px;
  margin-bottom: 20px;
  padding-left: 10px;
  padding-bottom: 15px;
  display: none;
}

.form-container {
  width: 80%;
  background-color: var(--form-background);
  border: 1px solid var(--divider-line);
  border-radius: 15px;
  margin: 0 auto;
  max-width: 1000px;
  z-index: 20;
  font-family: "Mulish", sans-serif;
  position: relative;
  padding-top: 100px; /* Adjust this to make room for the floating image */
}

.signup-team {
  text-align: center;
  width: 100%;
/*   position: absolute; */
/*   top: -60px;  *//* Adjust this to position the image above the form */
/*   left: 50%; */
/*   transform: translateX(-50%); */
/*   z-index: 22; */
  margin-top: -55px;
}

.signup-team img {
  width: 60%;
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

.confirm-button {
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  background: var(--emblem-green);
  font-size: 1.3em;
  margin: auto;
  justify-content: center;
  text-align: center;
  text-decoration: none;
  margin-top: 10px;
  display: flex;
}

.confirm-button:hover {
  background: var(--emblem-green-over);


}


/*upload*/

.form-item {
    border-radius: 5px;
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 10px;
    background-color: #00000015;
}

.form-item label,
.form-item input,
.form-item .form-caption {
    padding: 7px;
}



.form-item .form-caption {
    font-size: 0.9;


}
.input-container {
    position: relative;
    display: inline-block;
    width: 100%
    z-index: 10;
}

#location_address {
    width: 100%;
    padding-right: 30px; /* Make space for the spinner */
}

.spinner {
    display: none;
    position: absolute;
    top: 30%;  /* Center vertically in the input field */
    left: 11px; /* Distance from the right edge of the input field */
    transform: translateY(-50%); /* Ensures the spinner is exactly centered vertically */
    width: 20px;
    height: 20px;
    border: 4px solid rgba(0,0,0,0.1);
    border-top: 4px solid var(--emblem-pink);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); translateY(-50%); }
    100% { transform: rotate(360deg); translateY(-50%); }
}




input[type="text"], input[type="name"], input[type="credential"],  input[type="password"] {
  width: 100%;
  height: 100%;
  padding: 8px 10px;
  margin: 4px 0;
  font-size: 18px !important;
  box-sizing: border-box;
  border: 3px solid var(--button-2-1);
  border-radius: 5px;
  background-color: var(--top-header) !important;
  color: var(--h1);
   border: 2px solid var(--button-2-1) !important;
}

select {
  width: 100%;
  height: 100%;
  padding: 8px 10px;
  margin: 4px 0;
  font-size: 18px !important;
  box-sizing: border-box;
  border: 3px solid var(--button-2-1);
  border-radius: 5px;
  background-color: var(--top-header) !important;
  color: var(--h1);
   border: 2px solid var(--button-2-1) !important;
}


/*
input[type="submit"] {
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background-color: var(--button-2-1);
  background-size: 0% 100%;  *//* Initial background size (progress bar) *//*
  transition: background-size 0.5s ease;  *//* Transition effect for smooth progress *//*
  font-size: 1.3em;
  width: fit-content;
  margin-top: 5px;
}

 *//* Specify the progress bar color *//*
input[type="submit"].progress-bar {
  background: url(../svgs/square-upload-progress.svg) left center repeat-y, gray;  *//* Combined background *//*
  background-size: auto;  *//* Auto size for image background *//*
}


input[type="submit"]:hover {
  background-color: var(--button-2-1-over);
} */

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
}

/* Hover effect for enabled state */
.go-button:hover {
    background-color: var(--button-2-1-over);
}

.underline-link {
color: var(--text-color);
}

</style>





<?php require_once ("../header-2024.php");?>



