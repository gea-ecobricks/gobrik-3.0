
function redirectToWelcome() {
    window.location.href = "index.php";
}


document.addEventListener("scroll", function() {
    var scrollPosition = window.scrollY || document.documentElement.scrollTop;
  
    // Check if the user has scrolled more than 1000px
    if (scrollPosition > 1000) {
      var footer = document.getElementById('footer-full');
      if (footer) {
        footer.style.display = 'block'; // Show the footer
      }
    }
  });


        let lastScrollTop = 0;


        window.onscroll = function() {
            scrollLessThan40();
            scrollMoreThan40();
            // showHideHeader();
        };

        function scrollLessThan40() {
    if (window.pageYOffset <= 40) {
        document.getElementById("header").style.height = "85px";
        document.getElementById("header").style.borderBottom = "none";
        document.getElementById("header").style.boxShadow = "none";
        // document.getElementById("main").style.marginTop = "40px";
        document.getElementById("gea-logo").style.width = "190px";
        document.getElementById("gea-logo").style.height = "40px";
        // document.getElementById("gea-logo").style.height = "56px";
        document.getElementById("logo-gobrik").style.opacity = "1";
        document.getElementById("header").style.top = "0"; // Set top to 0

         document.getElementById("settings-buttons").style.padding = "17px 43px 17px 12px";
        document.getElementById("settings-buttons").style.padding = "16px 43px 16px 12px";
//        document.getElementById("main-header-buttons").style.marginTop = "0px";
        document.getElementById("language-menu-slider").style.top = "-15px";  
        document.getElementById("login-menu-slider").style.top = "-15px";

    }
}

function scrollMoreThan40() {
    if (window.pageYOffset >= 40) {
        document.getElementById("header").style.height = "60px";
        document.getElementById("header").style.borderBottom = "var(--header-accent) 0.5px solid";
        document.getElementById("header").style.boxShadow = "0px 0px 15px rgba(0, 0, 10, 0.805)";
        // document.getElementById("main").style.marginTop = "0px";
        document.getElementById("gea-logo").style.width = "170px";
        document.getElementById("gea-logo").style.height = "35px";
        document.getElementById("logo-gobrik").style.opacity = "0.9";
//        document.getElementById("settings-buttons").style.marginTop = "3px";
        document.getElementById("settings-buttons").style.padding = "14px 43px 16px 12px";
//        document.getElementById("main-header-buttons").style.marginTop = "-5px";
        document.getElementById("language-menu-slider").style.top = "-35px";  
        document.getElementById("login-menu-slider").style.top = "-35px";
    }
}


//
//
//function pageMeasureBar() {
//        let scrollPercentage = (window.pageYOffset / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
//        document.getElementById("progress-bar").style.width = scrollPercentage + "%";
//    }


    
/* RIGHT SETTINGS OVERLAY */

function openSideMenu() {
  document.getElementById("main-menu-overlay").style.width = "100%";
  document.getElementById("main-menu-overlay").style.display = "block";
  document.body.style.overflowY = "hidden";
  document.body.style.maxHeight = "101vh";

  var modal = document.getElementById('main-menu-overlay');

function modalShow () {
   modal.setAttribute('tabindex', '0');
   modal.focus();
}

function focusRestrict ( event ) {
  document.addEventListener('focus', function( event ) {
    if ( modalOpen && !modal.contains( event.target ) ) {
      event.stopPropagation();
      modal.focus();
    }
  }, true);
}
}

/* Close when someone clicks on the "x" symbol inside the overlay */
function closeSettings() {
  document.getElementById("main-menu-overlay").style.width = "0%";
  document.body.style.overflowY = "unset";
document.body.style.maxHeight = "unset";
  //document.body.style.height = "unset";
} 

function modalCloseCurtains ( e ) {
  if ( !e.keyCode || e.keyCode === 27 ) {
    
  document.body.style.overflowY = "unset";
  document.getElementById("main-menu-overlay").style.width = "0%";
  /*document.getElementById("knack-overlay-curtain").style.height = "0%";*/

  }
}

document.addEventListener('keydown', modalCloseCurtains);




/*FORM MODALS*/








/*Updates certain colors to the Dark or Light theme*/
// function updateLogoColor() {
//   const svg = document.querySelector("html");
//   const elementsWithColor = svg.querySelectorAll("[fill='#646464']");

//   for (let element of elementsWithColor) {
//     if (element.getAttribute("fill") === "#646464") {
//       element.setAttribute("fill", "var(--logo-color)");
//     }
//   }
// }

/* ---------- ------------------------------
LANGUAGE SELECTOR
-------------------------------------------*/

function showLangSelector() {
    hideLoginSelector();

    var slider = document.getElementById('language-menu-slider');
    var currentMarginTop = window.getComputedStyle(slider).marginTop;
    slider.style.display = 'flex';
    slider.style.marginTop = currentMarginTop === '70px' ? '0px' : '70px';

    // Set zIndex of top-page-image
    var topPageImage = document.querySelector('.top-page-image');
    if (topPageImage) {
        topPageImage.style.zIndex = '25';
    }

    // Prevent event from bubbling to document
    event.stopPropagation();

    // Add named event listener for click on the document
    document.addEventListener('click', documentClickListener);
}

function hideLangSelector() {
    var slider = document.getElementById('language-menu-slider');
    slider.style.marginTop = '0px'; // Reset margin-top to 0px

    // Set zIndex of top-page-image
    var topPageImage = document.querySelector('.top-page-image');
    if (topPageImage) {
        topPageImage.style.zIndex = '35';
    }

    // Remove the named event listener from the document
    document.removeEventListener('click', documentClickListener);
}

// Named function to be used as an event listener
function documentClickListener() {
    hideLangSelector();
}

/* ---------- ------------------------------
SERVICE SELECTOR
-------------------------------------------*/

function showLoginSelector() {
    hideLangSelector();

    var slider = document.getElementById('login-menu-slider');
    var currentMarginTop = window.getComputedStyle(slider).marginTop;
    slider.style.display = 'flex';
    slider.style.marginTop = currentMarginTop === '70px' ? '0px' : '70px';

    // Set zIndex of top-page-image
    var topPageImage = document.querySelector('.top-page-image');
    if (topPageImage) {
        topPageImage.style.zIndex = '25';
    }

    // Prevent event from bubbling to document
    event.stopPropagation();

    // Add named event listener for click on the document
    document.addEventListener('click', documentClickListenerLogin);
}

function hideLoginSelector() {
    var slider = document.getElementById('login-menu-slider');
    slider.style.marginTop = '0px'; // Reset margin-top to 0px

    // Set zIndex of top-page-image
    var topPageImage = document.querySelector('.top-page-image');
    if (topPageImage) {
        topPageImage.style.zIndex = '35';
    }

    // Remove the named event listener from the document
    document.removeEventListener('click', documentClickListenerLogin);
}

// Named function to be used as an event listener
function documentClickListenerLogin() {
    hideLoginSelector();
}

function goBack() {
    window.history.back();
}




document.querySelectorAll('.x-button').forEach(button => {
    button.addEventListener('transitionend', (e) => {
        // Ensure the transitioned property is the transform to avoid catching other transitions
        if (e.propertyName === 'transform') {
            // Check if the button is still being hovered over
            if (button.matches(':hover')) {
                button.style.backgroundImage = "url('../svgs/x-button-night-over.svg?v=3')";
            }
        }
    });

    // Optionally, revert to the original background image when not hovering anymore
    button.addEventListener('mouseleave', () => {
        button.style.backgroundImage = "url('../svgs/x-button-night.svg?v=3')";
    });
});



//ECOBRICK MODAL PREVIEW

function ecobrickPreview(imageUrl, brik_serial, weight, owner, location) {
    const modal = document.getElementById('form-modal-message');
    const contentBox = modal.querySelector('.modal-content-box'); // This is the part we want to hide
    const photoBox = modal.querySelector('.modal-photo-box'); // This is where we'll show the image
    const photoContainer = modal.querySelector('.modal-photo'); // The container for the image

    // Hide the content box and show the photo box
    contentBox.style.display = 'none'; // Hide the content box
    photoBox.style.display = 'block'; // Make sure the photo box is visible

    // Clear previous images from the photo container
    photoContainer.innerHTML = '';

    // Create and append the ecobrick image to the photo container
    var img = document.createElement('img');
    img.src = 'https://ecobricks.org/' + imageUrl;
    img.alt = "Ecobrick " + brik_serial;
    img.style.maxWidth = '90%';
    img.style.maxHeight = '75vh';
    img.style.minHeight = "400px";
    img.style.minWidth = "400px";
    img.style.margin = 'auto';
    // img.style.backgroundColor = '#8080802e';
    photoContainer.appendChild(img);

    // Add ecobrick details and view details button inside photo container
    var details = document.createElement('div');
    details.className = 'ecobrick-details';
    details.innerHTML = '<p>Ecobrick ' + brik_serial + ' | ' + weight + ' of plastic sequestered by ' + owner + ' in ' + location + '.</p>' +
                        '<a href="brik.php?serial_no=' + brik_serial + '" class="preview-btn" style="margin-bottom: 50px;height: 25px;padding: 5px;border: none;padding: 5px 12px;">ℹ️ View Full Details</a>';
    photoContainer.appendChild(details);

    // Hide other parts of the modal that are not used for this preview
    modal.querySelector('.modal-content-box').style.display = 'none'; // Assuming this contains elements not needed for this preview

    // Show the modal
    modal.style.display = 'flex';

    //Blur out background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');
}




//TUCK AND HIDE:  This code tucks the top banner image under the header after a scroll of 30 px

    window.onscroll = function() {
        scrollLessThan30();
        scrollMoreThan30();
        // showHideHeader();
    };

    function scrollLessThan30() {
        if (window.pageYOffset <= 30) {
    var topPageImage = document.querySelector('.top-page-image');
                if (topPageImage) {
                topPageImage.style.zIndex = "35";
            }
        }
    }

    function scrollMoreThan30() {
        if (window.pageYOffset >= 30) {
    var topPageImage = document.querySelector('.top-page-image');
                if (topPageImage) {
                topPageImage.style.zIndex = "25";
            }
        }
    }



document.addEventListener("DOMContentLoaded", function() {
    // Select all elements with the class 'toggle-password'
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');

    togglePasswordIcons.forEach(function(icon) {
        icon.addEventListener('click', function() {
            // Find the associated input field using the 'toggle' attribute
            const input = document.querySelector(icon.getAttribute('toggle'));
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.textContent = '🔓'; // Change to unlocked emoji
                } else {
                    input.type = 'password';
                    icon.textContent = '🔒'; // Change to locked emoji
                }
            }
        });
    });
});



/* PASSWORD RESET MODAL  */

function showPasswordReset(type, lang = 'en', email = '') {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    let content = '';
    photobox.style.display = 'none';

    switch (type) {
        case 'reset':
            let title, promptText, buttonText, errorText;

            switch (lang) {
                case 'fr':
                    title = "Réinitialiser le mot de passe";
                    promptText = "Entrez votre email pour réinitialiser votre mot de passe :";
                    buttonText = "Réinitialiser le mot de passe";
                    errorText = "🤔 Hmmm... nous ne trouvons aucun compte utilisant cet email !";
                    break;
                case 'es':
                    title = "Restablecer la contraseña";
                    promptText = "Ingrese su correo electrónico para restablecer su contraseña:";
                    buttonText = "Restablecer la contraseña";
                    errorText = "🤔 Hmmm... no podemos encontrar una cuenta que use este correo electrónico!";
                    break;
                case 'id':
                    title = "Atur Ulang Kata Sandi";
                    promptText = "Masukkan email Anda untuk mengatur ulang kata sandi Anda:";
                    buttonText = "Atur Ulang Kata Sandi";
                    errorText = "🤔 Hmmm... kami tidak dapat menemukan akun yang menggunakan email ini!";
                    break;
                default: // 'en'
                    title = "Reset Password";
                    promptText = "Enter your email to reset your password:";
                    buttonText = "Reset Password";
                    errorText = "🤔 Hmmm... we can't find an account that uses this email!";
                    break;
            }

            content = `
                <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                    <h1>🔓</h1>
                </div>
                <div class="preview-title">${title}</div>
                <form id="resetPasswordForm" action="reset_password.php" method="POST" onsubmit="return validateForm()">
                    <div class="preview-text" style="font-size:medium;">${promptText}</div>
                    <input type="email" name="email" required value="${email}">
                    <div style="text-align:center;width:100%;margin:auto;margin-top:10px;margin-bottom:10px;">
                        <div id="no-buwana-email" class="form-warning" style="margin-top:5px;margin-bottom:5px;" data-lang-id="010-no-buwana-email">${errorText}</div>
                        <button type="submit" class="submit-button enabled">${buttonText}</button>
                    </div>
                </form>
            `;
            break;

        default:
            content = '<p>Invalid term selected.</p>';
    }

    messageContainer.innerHTML = content;

    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}


 window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);

    // Check if the 'email_not_found' parameter exists in the URL
    if (urlParams.has('email_not_found')) {
        // Get the email from the URL parameters
        const email = urlParams.get('email') || '';

        // Get the language from the backend (PHP) or default to 'en'
        const lang = '<?php echo $lang; ?>'; // Make sure this is echoed from your PHP

        // Show the reset modal with the pre-filled email and appropriate language
        showPasswordReset('reset', lang, email);

        // Wait for the modal to load, then display the "email not found" error message
        setTimeout(() => {
            const noBuwanaEmail = document.getElementById('no-buwana-email');
            if (noBuwanaEmail) {
                noBuwanaEmail.style.display = 'block';
            }
        }, 100);
    }
};
