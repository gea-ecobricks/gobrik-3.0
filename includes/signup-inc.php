
<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the translations for each language
$translations = [
    'en' => [
        'alert_message' => "Looks like you already have an account and are logged in! Let's take you to your dashboard.",
    ],
    'fr' => [
        'alert_message' => "Il semble que vous avez déjà un compte et que vous êtes connecté ! Nous vous emmenons à votre tableau de bord.",
    ],
    'id' => [
        'alert_message' => "Sepertinya Anda sudah memiliki akun dan sedang masuk! Mari kita bawa Anda ke dasbor Anda.",
    ],
    'es' => [
        'alert_message' => "¡Parece que ya tienes una cuenta y has iniciado sesión! Vamos a llevarte a tu panel.",
    ]
];

// Set a default language if $lang is not set or if it is an unknown language
if (!isset($translations[$lang])) {
    $lang = 'en'; // Default to English
}

// Get the appropriate translation for the alert message
$alert_message = $translations[$lang]['alert_message'];

// Check if the user is logged in
if (isLoggedIn()) {
    echo "<script>
        alert('{$alert_message}');
        window.location.href = 'dashboard.php';
    </script>";
    exit();
}
?>


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


</STYLE>





<?php require_once ("../header-2024.php");?>



