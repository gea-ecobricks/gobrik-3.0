/*-----------------------------------

FRENCH SNIPPETS FOR ECOBRICKS.ORG


-----------------------------------*/


// Ampersand (&): Should be escaped as &amp; because it starts HTML character references.
// Less-than (<): Should be escaped as &lt; because it starts an HTML tag.
// Greater-than (>): Should be escaped as &gt; because it ends an HTML tag.
// Double quote ("): Should be escaped as &quot; when inside attribute values.
// Single quote/apostrophe ('): Should be escaped as &#39; or &apos; when inside attribute values.
// Backslash (\): Should be escaped as \\ in JavaScript strings to prevent ending the string prematurely.
// Forward slash (/): Should be escaped as \/ in </script> tags to prevent prematurely closing a script.


const fr_Translations = {


"000-language-code": "FR",
 "000-your": "Votre",
    "000-already-have-account": "Vous avez déjà un compte? <a href=\"login.php\">Connexion</a>",
    "000-select": "Sélectionner...",
    "000-your-password": "Votre mot de passe:",
    "000-forgot-your-password": 'Mot de passe oublié? <a href="#" onclick="showModalInfo(\'reset\')" class="underline-link">Réinitialisez-le.</a>',
    "000-password-wrong": "👉 Le mot de passe est incorrect.",
    "000-no-account-yet": '<p style="font-size:medium;" data-lang-id="000-no-account-yet">Vous n\'avez pas encore de compte? <a href="signup.php">Inscrivez-vous!</a></p>',

    /*Menu of pages*/

 "1000-dashboard": "Tableau de bord",
    "1001-gobrik-tour": "Visite de GoBrik",
    "1000-login": "Connexion",
    "1000-signup": "Inscription",
    "1000-log-ecobrick": "Enregistrer l'écobrique",
    "1000-brikchain": "La Brikchain",
    "1000-latest-ecobricks": "Dernières écobriques",
    "1000-featured-projects": "Projets en vedette",
    "1000-latest-trainings": "Dernières formations",
    "1000-landing-page": "Page d'accueil",
    "1000-about-gobrik": `<a href="https://ecobricks.org/gobrik" target="_blank">Le projet GoBrik</a> est développé par l'<a href="https://ecobricks.org/about" target="_blank">Alliance Globale d'Écobriques</a>, une entreprise terrestre à but non lucratif dédiée à l'accélération de la transition du plastique et du pétro-capital.`,




            /*General Texts*/
            '1000-learn-more': 'Apprendre encore plus',
            '1001-what-are-ecobricks': 'Introduction',
            '1002-faqs-button': 'FAQ',
            '1003-reset-preferences': '❌ réinitialiser le site',
            "1004-gea-vision": "Nous envisageons une transition dans nos foyers, nos communautés et nos entreprises, du plastique vers une harmonie toujours plus verte avec les cycles de la Terre.",
  

    
    
    

    
    /*  INTERFACE CURTAINS  */
    
            /*Search Curtain*/
    
            '100-search-title': 'Recherche',
            '101-search-intro': 'Sélectionnez les langues et les index que vous souhaitez rechercher. Pour rechercher des écobriques, utilisez notre <a href="brikchain.php">recherche brikchain</a>.',
            '102-search-index1': 'Pages : ',
            '103-search-index3': 'Dictionnaires :',
            '104-search-bottom-text': 'Les définitions du dictionnaire sont affichées en dernier',
    
            /* Subscribtion Curtain */
    
            "105-subscription-registration": "",
            "106-hide-subscription-registration": "",
            "107-hide-subscription-image-alt": "Masquer l'inscription à la newsletter Earthen",
            "108-subscription-image-alt-desktop": "Inscription à la newsletter Earthen Ecobrick",
            "110-subscription-title": "Abonnez-vous à Earthen",
            "111-subscription-subtitle": "Suivez notre travail régénératif et nos actualités en vous abonnant à notre newsletter Earthen.",
            "112-subscription-email-placeholder": "Votre e-mail...",
            "113-subscription-name-placeholder": "Votre nom...",
            "114-subscription-caption": "Nous envoyons une nouvelle fois par mois ou selon <a href=\"javascript:void(0)\" onclick=\"previewPrivacy()\">notre stricte politique de confidentialité</a>.",
            "115-subscription-do-not-show-again": "Je me suis déjà inscrit ou je ne suis pas intéressé - ne plus afficher",
            "116-privacy-title": "Vos données sont en sécurité",
            "117-privacy-subtitle": "En tant qu'entreprise terrestre à but non lucratif, nous ne participons en principe à aucune publicité tierce, ni ne connectons vos données à des plateformes sociales qui le font. Vos données ne seront ni vendues ni échangées. Nous utilisons fièrement la plateforme open source Ghost.org pour notre newsletter, que nous hébergeons sur nos propres serveurs. Nos newsletters sont envoyées généralement une fois par mois, nous nous engageons à ne pas dépasser plus d'une par semaine.",
            "118-full-privacy-link": "Notre politique complète",
          
            /* Guided Tour Curtain */
    
            '200-tour-1-header-welcome': 'Bienvenue sur Ecobricks.org !',
            '201-tour-1-description': 'Alors, qu\'est-ce qu\'un écobrique exactement ? Découvrez notre introduction en cinq diapositives de 45 secondes sur les écobriques. Nous couvrirons les bases : ce que sont les écobriques, comment en fabriquer un, l\'exemple terrestre que nous suivons et ce que vous pouvez réaliser avec les écobriques une fois prêts.',
            '202-tour-start-button': 'Commencer ➔',
            '203-tour-2-header': 'Une solution low-tech',
            '204-tour-2-description': 'C\'est assez simple. Un écobrique est une bouteille en plastique densément remplie de plastique utilisé pour créer un bloc de construction réutilisable qui permet la <b>séquestration du plastique</b>. Pour en fabriquer un, tout ce dont vous avez besoin est un bâton et une bouteille.',
            '205-tour-back-button': '↩ Retour',
            '206-tour-next-button': 'Suivant ➔',
            '207-tour-2-reference': 'Définition tirée de <a href="https://en.wikipedia.org/wiki/Ecobricks">wikipedia/wiki/ecobricks</a> | Pour approfondir : <a href="what">ecobricks.org/what</a>',
            '208-tour-3-header': 'La séquestration du plastique ?',
            '209-tour-3-description': 'Les écobriques suivent l\'exemple de la Terre. Au cours des derniers milliards d\'années, la Terre a progressivement capturé le carbone libre pour le concentrer et le séquestrer sous la terre, permettant ainsi à la vie de prospérer. Nous faisons de même avec notre plastique libre (qui est en fait fabriqué à partir de ce carbone ancien !) en l\'emballant et en le séquestrant dans un écobrique.',
            '210-reference-tour-3-description': 'L\'histoire longue du plastique : <a href="/plastic/">ecobricks.org/plastic</a>',
            '211-tour-4-header': 'Plus qu\'éco',
            '212-tour-4-description': 'Les écobriques font plus que simplement empêcher le plastique de polluer la biosphère. L\'écobriqueing empêche également le plastique d\'être traité industriellement - ce qui crée souvent plus de problèmes qu\'il n\'en résout. Le meilleur de tout, les écobriques peuvent être utilisés localement pour construire toutes sortes de belles choses vertes !',
            '213-tour-4-reference': 'Pourquoi nous faisons des écobriques : <a href="build">ecobricks.org/why</a>',
            '214-tour-5-header': 'Construire vert et grandiose',
            '215-tour-5-description': 'Les écobriques sont utilisés de toutes sortes de manières par la communauté mondiale. Aux Philippines, nous construisons des parcs de jeux en forêt alimentaire avec eux. Au Royaume-Uni, nous construisons des maisons rondes en terre. En Indonésie, nous fabriquons des meubles modulaires pour les maisons et les cafés. Assurez-vous de consulter les sections de construction de notre site pour des idées.',
            '216-tour-5-reference': 'Notre page sur les méthodes de construction : <a href="build">ecobricks.org/build</a>',
            '217-tour-6-header': 'Des questions ?',
            '218-tour-6-description': 'Nous sommes là pour aider ! L\'Alliance Globale des Ecobricks est une entreprise de la Terre qui fait avancer le mouvement de transition plastique en maintenant la philosophie et la technologie du mouvement. Nous ne sommes pas là pour le profit. Nous ne sommes pas non plus sponsorisés par des gouvernements ou des entreprises. Nous le faisons pour les gens et la planète.',
            '219-tour-done-button': '✓ Terminé',
            '220-tour-6-reference': 'En savoir plus sur nous <a href="/about">ecobricks.org/about</a>',    
    
    
            
            /* FOOTER TEXTS*/

            
                "400-visionscape-description": "Nous envisageons une grande transition verte passant des méthodes polluantes à des méthodes enrichissantes. Et cela commence avec notre plastique.",
                "401-wikipedia-description": "<img src=\"../icons/wikipedia.svg\" style=\"width:100%\" alt=\"Un article de Wikipedia approfondi sur l'histoire, le concept et la méthodologie de l'écobrique.\" title=\"Un article de Wikipedia approfondi sur l'histoire, le concept et la méthodologie de l'écobrique.\">",
                "402-gobrik description": "<img src=\"../icons/gobrik-icon-white.svg\" style=\"width:100%\" alt=\"Gobrik est une plateforme pour gérer votre écobriquage, vos projets de construction et la transition plastique.\" title=\"Gobrik est une plateforme pour gérer votre écobriquage, vos projets de construction et la transition plastique.\">",
                "403-medium-description": "<img src=\"../icons/medium.svg\" style=\"width:100%\" alt=\"Suivez notre publication Earthen sur Medium\" title=\"Suivez notre publication Earthen sur Medium\">",
                "404-github description": "<img src=\"../icons/github.svg\" style=\"width:100%\" alt=\"Contribuez à notre dépôt Github Ecobricks.org\" title=\"Contribuez à notre dépôt Github Ecobricks.org\">",
                "405-facebook description": "<img src=\"../icons/facebook.svg\" style=\"width:100%\" alt=\"Suivez notre page Facebook\" title=\"Suivez notre page Facebook\">",
                "406-youtube description": "<img src=\"../icons/youtube.svg\" style=\"width:100%\" alt=\"Abonnez-vous à notre chaîne YouTube Ecobricks\" title=\"Abonnez-vous à notre chaîne YouTube Ecobricks\">",
                "407-instagram description": "<img src=\"../icons/instagram.svg\" style=\"width:100%\" alt=\"Instagram: Ecobricks.Plastic.Transition\" title=\"Description de 407-facebook\">",
              
                "409-wikipedia-text": "Également connu sous le nom de Eco Bricks, Eco-Bricks, Ecolladrillos, briks, briques de bouteilles et ecobriques, l'Alliance Globale Ecobrick et <a href=\"https://en.wikipedia.org/wiki/Ecobricks\" target=\"_blank\" rel=\"noopener\">Wikipedia</a> approuvent l'orthographe 'ecobrick' pour désigner la <a href=\"https://en.wikipedia.org/wiki/Plastic_Sequestration\" target=\"_blank\" rel=\"noopener\">séquestration du plastique</a> dans une bouteille PET pour en faire un bloc de construction réutilisable.",
                "410-gobrik-title": "Notre Application Gobrik",
                "411-gobrik-text": "<a href=\"https://gobrik.com\" target=\"_blank\" rel=\"noopener\">GoBrik</a> est une application web servant le mouvement de transition plastique local et mondial. Elle est maintenue et développée par l'Alliance Globale Ecobrick. Apprenez-en plus sur notre <a href=\"https://ecobricks.org/gobrik\">projet Gobrik</a>.",
                "412-earthen-service-title": "Entreprise Terrestre",
                "413-earthen-service-text": "L'<a href=\"https://ecobricks.org/about\" target=\"_blank\">alliance Globale Ecobrick</a> est une entreprise pour la Terre, à but non lucratif, basée en Indonésie. Nous opérons sous <a href=\"https://ecobricks.org/principles\">des principes régénératifs</a>. Par conséquent, nous n'avons aucun parrainage d'entreprise, de société ou de gouvernement. Nos revenus sont générés en fournissant un <a href=\"aes\">service écologique</a> et des <a href=\"trainings\">expériences éducatives</a>",
                "414-tech-partners-title": "Partenaires Technologiques",
                "415-tech-partners-text": "Notre vision de la transition du plastique et du pétrocapital est une collaboration mondiale ! Nous sommes reconnaissants à nos partenaires qui nous ont donné un accès complet à leurs technologies impressionnantes. Merci à <a href=\"https://www.dewaweb.com/\" target=\"_blank\" rel=\"noopener\">DewaWeb Hosting</a> dont les serveurs hébergent nos sites et à <a href=\"https://svgator.com\" target=\"_blank\" rel=\"noopener\">SVGator</a> dont la plateforme d'animation donne vie à nos graphiques.",
              
                "416-banner-earth-enterprise": "<a href=\"https://ecobricks.org/about\" target=\"_blank\"><img src=\"../webps/banners/forearth-dark-350px.webp\" style=\"width:300px\" alt=\"En savoir plus sur notre structure d'Entreprise Terrestre\" loading=\"lazy\" title=\"En savoir plus sur notre structure d'Entreprise Terrestre\"></a>",
                "417-banner-eco-impacts": "<a href=\"https://ecobricks.org/regenreports\" target=\"_blank\"><img src=\"../webps/banners/762-disclose-dark-350px.webp\" style=\"width:300px\" alt=\"Cliquez pour voir le détail complet et en direct de nos impacts écologiques de 2023 sur GoBrik.com\" loading=\"lazy\" title=\"Cliquez pour voir le détail complet et en direct de nos impacts écologiques de 2023 sur GoBrik.com\"></a>",
                "418-banner-open-books": "<a href=\"https://ecobricks.org/open-books\" target=\"_blank\"><img src=\"../webps/banners/openbooks-dark-350px.webp\" style=\"width:300px\" alt=\"Cliquez pour voir notre suivi financier en direct\" loading=\"lazy\" title=\"Cliquez pour voir notre suivi financier en direct\"></a>",
                "419-conclusion-disclosure": "Nous suivons et divulguons notre impact écologique net-vert. Voir notre <a href=\"https://ecobricks.org/en/regenreports.php\" target=\"_blank\">Rapport de Régénération</a> et notre <a href=\"https://www.gobrik.com/#my-catalyst/enterprise-disclosure/5e1f513586a7fe0015e77628/\" target=\"_blank\">comptabilité d'impact dynamique pour 2024.</a>",
                "420-conclusion-contribute": "Le site Ecobricks.org est codé à la main en HTML, PHP MYSQL, CSS et Javascript open source. Contribuez à améliorer cette page en laissant un rapport de bug ou une demande de push sur Github :",
                "421-conclusion-data": "Tout le contenu éducatif de notre site (photos, vidéos et textes) est mis à disposition pour le partage par l'Alliance Ecobrick sous une <a rel=\"license\" href=\"http://creativecommons.org/licenses/by-sa/4.0/\" target=\"_blank\">Licence Creative Commons Attribution-Partage dans les Mêmes Conditions 4.0 International</a>.<br>Veuillez attribuer toute utilisation à \"The Global Ecobrick Alliance, ecobricks.org\" en utilisant la même licence.",
                "422-conclusion-copyright": "Les logos et emblèmes d'Ecobricks.org, GEA, Earthen, AES et Gobrik sont protégés par le droit d'auteur 2010-2024 par l'Alliance Globale Ecobrick."
            
}
        
      