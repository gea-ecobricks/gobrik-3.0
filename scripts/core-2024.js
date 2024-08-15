
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



    /*SHOW MODALS*/


function showModalInfo(type, lang) {
    const modal = document.getElementById('form-modal-message');
    const photobox = document.getElementById('modal-photo-box');
    const messageContainer = modal.querySelector('.modal-message');
    const modalBox = document.getElementById('modal-content-box');
    let content = '';
    photobox.style.display = 'none';

    switch (type) {
        case 'terms':
            content = `
                <div style="font-size: small;">
                    <?php include "../files/terms-${lang}.php"; ?>
                </div>
            `;
            modal.style.position = 'absolute';
            modal.style.overflow = 'auto';
            modalBox.style.textAlign = 'left';
            modalBox.style.maxHeight = 'unset';
            modalBox.style.marginTop = '30px';
            modalBox.style.marginBottom = '30px';
            modalBox.scrollTop = 0;
            modal.style.alignItems = 'flex-start';
            break;

        case 'earthen':
            switch(lang) {
                case 'fr':
                    content = `
                        <img src="../svgs/earthen-newsletter-logo.svg" alt="Bulletin Earthen" height="250px" width="250px" class="preview-image">
                        <div class="preview-title">Bulletin Earthen</div>
                        <div class="preview-text">Lancé en 2016 au pays du peuple Igorot, Earthen est notre bulletin bimensuel du mouvement de régénération de la terre. Nous partageons les dernières nouvelles du monde des ecobricks et de la construction en terre, de la science et de la philosophie régénératives. Nous vous tenons également informé des annonces et nouvelles importantes de GoBrik. Gratuit. Désabonnement facile à tout moment.</div>
                    `;
                    break;
                case 'es':
                    content = `
                        <img src="../svgs/earthen-newsletter-logo.svg" alt="Boletín de Earthen" height="250px" width="250px" class="preview-image">
                        <div class="preview-title">Boletín de Earthen</div>
                        <div class="preview-text">Iniciado en 2016 en la tierra del pueblo Igorot, Earthen es nuestro boletín bimensual del movimiento regenerativo de la tierra. Compartimos las últimas noticias del mundo de los ecobricks y la construcción con tierra, la ciencia y la filosofía regenerativas. También te mantenemos al día con los principales anuncios y noticias de GoBrik. Gratis. Fácil de darse de baja en cualquier momento.</div>
                    `;
                    break;
                case 'id':
                    content = `
                        <img src="../svgs/earthen-newsletter-logo.svg" alt="Buletin Earthen" height="250px" width="250px" class="preview-image">
                        <div class="preview-title">Buletin Earthen</div>
                        <div class="preview-text">Dimulai pada tahun 2016 di tanah orang Igorot, Earthen adalah buletin dua bulanan kami tentang gerakan regeneratif bumi. Kami berbagi berita terbaru dari dunia ecobricks dan bangunan tanah, sains, dan filosofi regeneratif. Kami juga memberi Anda pembaruan tentang pengumuman dan berita utama dari GoBrik. Gratis. Mudah untuk berhenti berlangganan kapan saja.</div>
                    `;
                    break;
                default: // 'en' or any other
                    content = `
                        <img src="../svgs/earthen-newsletter-logo.svg" alt="Earthen Newsletter" height="250px" width="250px" class="preview-image">
                        <div class="preview-title">Earthen Newsletter</div>
                        <div class="preview-text">Started in 2016 in the land of the Igorot people, Earthen is our bi-monthly newsletter of the earthen regenerative movement. We share the latest news from the world of ecobricks and earth building, regenerative science, and philosophy. We also keep you up to date with major GoBrik announcements and news. Free. Easy to unsubscribe at any time.</div>
                    `;
            }
            break;

        case 'ecobrick':
            switch(lang) {
                case 'fr':
                    content = `
                        <img src="../webps/faqs-400px.webp" alt="Termes et Types Ecobrick" height="200px" width="200px" class="preview-image">
                        <div class="preview-title">Le Terme</div>
                        <div class="preview-text">En 2016, les leaders de la transition plastique du monde entier ont convenu d'utiliser le terme "ecobrick" sans trait d'union ni majuscule comme terme de référence cohérent et standardisé dans le guide et leurs documents. Ainsi, les ecobrickers du monde entier pourraient se référer avec un seul mot au même concept et les recherches sur le web ainsi que les hashtags accéléreraient la diffusion mondiale. Consultez wikipedia.org/ecobricks pour l'histoire complète.</div>
                    `;
                    break;
                case 'es':
                    content = `
                        <img src="../webps/faqs-400px.webp" alt="Términos y Tipos de Ecobrick" height="200px" width="200px" class="preview-image">
                        <div class="preview-title">El Término</div>
                        <div class="preview-text">En 2016, los líderes de la transición plástica de todo el mundo acordaron usar el término "ecobrick" sin guion y sin mayúscula como el término de referencia coherente y estandarizado en la guía y sus materiales. De esta manera, los ecobrickers de todo el mundo podrían referirse con una sola palabra al mismo concepto y las búsquedas en la web, así como los hashtags, acelerarían la difusión global. Consulte wikipedia.org/ecobricks para la historia completa.</div>
                    `;
                    break;
                case 'id':
                    content = `
                        <img src="../webps/faqs-400px.webp" alt="Istilah dan Jenis Ecobrick" height="200px" width="200px" class="preview-image">
                        <div class="preview-title">Istilah</div>
                        <div class="preview-text">Pada tahun 2016, para pemimpin transisi plastik di seluruh dunia sepakat untuk menggunakan istilah 'ecobrick' tanpa tanda hubung dan huruf kapital sebagai istilah standar yang konsisten dalam panduan dan materi mereka. Dengan cara ini, ecobrickers di seluruh dunia dapat merujuk dengan satu kata ke konsep yang sama dan pencarian web serta tagar akan mempercepat penyebaran global. Lihat wikipedia.org/ecobricks untuk sejarah lengkapnya.</div>
                    `;
                    break;
                default: // 'en' or any other
                    content = `
                        <img src="../webps/faqs-400px.webp" alt="Ecobrick Term and Types" height="200px" width="200px" class="preview-image">
                        <div class="preview-title">The Term</div>
                        <div class="preview-text">In 2016, plastic transition leaders around the world agreed to use the non-hyphenated, non-capitalized term ‘ecobrick’ as the consistent, standardized term of reference in the guidebook and their materials. In this way, ecobrickers around the world would be able to refer with one word to the same concept, and web searches and hashtags would accelerate global dissemination. See wikipedia.org/ecobricks for the full history.</div>
                    `;
            }
            break;

        default:
            content = '<p>Invalid term selected.</p>';
    }

    messageContainer.innerHTML = content;

    // Show the modal and update other page elements
    modal.style.display = 'flex';
    document.getElementById('page-content').classList.add('blurred');
    document.getElementById('footer-full').classList.add('blurred');
    document.body.classList.add('modal-open');
}
