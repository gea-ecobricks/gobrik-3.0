<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" >

<!-- Meta tags for page display and search engine listing
AND UNIQUE to HTML Pages-->

<title>GoBrik</title>
<meta name="keywords" content="gobrik, ecobrick app, eco, brick, eco brick, ecobrick, eco-brick, eco, bricks, eco brick, ecobricks, eco-bricks, brik, briks, plastic, plastic management, carbon sequestration,  plastic solved, drop off, exchange, marketplace, plastic sequestration, aes plastic, plastic offsetting, ecological accounting, plastic accounting">
<meta name="description" content="Manage your ecobricks, projects and plastic transition. By putting our plastic to good use, together we can build our greenest visions.">
<meta name="author" content="Global Ecobrick Alliance">

<!-- Facebook Open Graph Tags for social sharing-->

<meta property="og:url"           content="https://www.gobrik.com">
<meta property="og:type"          content="app">
<meta property="og:title"         content="GoBrik">
<meta property="og:description"   content="Manage your ecobricks, projects and plastic transition. By putting our plastic to good use, together we can build our greenest visions." >
<meta property="og:image"         content="https://www.gobrik.com/images/social-banner-1200px.png" >
<meta property="fb:app_id"  content="1781710898523821" >
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="1000" />
<meta property="og:image:alt"     content="A metaphorical road winding into the distance with various ecobrick and earth constructions along side it and the GoBrik logo floating above"/>
<meta property="og:locale" content="en_GB, id_ID, es_ES" />

<link rel="preload" as="image" href="https://gobrik.com/svgs/Happy-turtle-dolphin-opti2.svg">

<style>
    body, html {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, sans-serif;
}

.container {
    text-align: center;
}

.logo {
    width: 200px;
    height: 200px;
    background-size: cover;
    background-position: center;
}

.subtitle {
    font-size: 1.2em;
    margin-top: 20px;
}

/* Default to dark mode (also for no preference) */
body {
    background-color: black;
    color: darkgrey;
}

.logo {
    background-image: url('svgs/bottle-loader-night-2.svg');
}

/* Light mode styles */
@media (prefers-color-scheme: light) {
    body {
        background-color: white;
        color: darkgrey;
    }
    .logo {
        background-image: url('svgs/bottle-loader-day-7.svg');
    }
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const userLang = navigator.language || navigator.userLanguage;
        let langDir = 'en/';

        if (userLang.includes('fr')) {
            langDir = 'fr/';
        } else if (userLang.includes('id')) {
            langDir = 'id/';
        } else if (userLang.includes('es')) {
            langDir = 'es/';
        } else if (userLang.includes('en')) {
            langDir = 'en/';
        } else {
            langDir = 'en/';
        }

        window.location.href = langDir;
    }, 2000);
});

</script>

</head>
<body>
    <div class="container">
        <img src="svgs/bottle-loader-day7.svg" alt="Global Ecobrick Alliance Logo" class="logo">
        <p class="subtitle">by the Global Ecobrick Alliance</p>
    </div>
</body>
</html>
