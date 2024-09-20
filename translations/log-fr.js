/*-----------------------------------
TEXT TRANSLATION SNIPPETS FOR GOBRIK.com
-----------------------------------*/

// Ampersand (&): Should be escaped as &amp; because it starts HTML character references.
// Less-than (<): Should be escaped as &lt; because it starts an HTML tag.
// Greater-than (>): Should be escaped as &gt; because it ends an HTML tag.
// Double quote ("): Should be escaped as &quot; when inside attribute values.
// Single quote/apostrophe ('): Should be escaped as &#39; or &apos; when inside attribute values.
// Backslash (\): Should be escaped as \\ in JavaScript strings to prevent ending the string prematurely.
// Forward slash (/): Should be escaped as \/ in </script> tags to prevent prematurely closing a script.

const fr_Page_Translations = {
    "001-log-title": "Enregistrer un Ecobrick",
    "002-log-subheading": "Partagez votre ecobrick avec le monde !<br>Utilisez ce formulaire pour enregistrer votre ecobrick dans le système brikchain.",
    "005-ecobricker-maker": "Qui a fabriqué cet ecobrick ?",
    "005b-ecobricker-maker-caption": "Indiquez le nom de l'ecobricker. Évitez les caractères spéciaux.",
    "000-field-required-error": "Ce champ est requis.",
    "000-maker-field-too-long-error": "Le nom est trop long. Maximum 255 caractères.",
    "005b-maker-error": "L'entrée contient des caractères non valides. Évitez les guillemets, barres obliques et signes supérieur à, s'il vous plaît.",
    "006-volume-ml": "Volume de l'Ecobrick (en millilitres) :",
    "006-select-volume": "Sélectionnez le volume...",
    "006-volume-ml-caption": "Veuillez indiquer le volume de l'ecobrick en millilitres.",
    "007-weight-g": "Poids de l'ecobrick (en grammes) :",
    "007-weight-g-caption": "Arrondir au gramme supérieur.",
    "007-brand_name": "Quelle marque de bouteille est utilisée pour cet ecobrick ?",
    "000-field-too-long-error": "Cette entrée doit contenir moins de 100 caractères. Tout ce dont nous avons besoin, c'est du nom de la marque de la bouteille, par exemple \"Max Water\".",
    "000-field-invalid-error": "L'entrée contient des caractères non valides. Évitez les guillemets, barres obliques et signes supérieur à, s'il vous plaît.",
    "008-bottom-color": "Couleur du fond de l'ecobrick :",
    "008-bottom-color-caption": "Veuillez sélectionner la couleur du fond de l'ecobrick.",
    "009-sequestration-type": "Quel type d'ecobrick est-ce ?",
    "009-sequestration-type-caption": "Veuillez sélectionner le type d'ecobrick. Apprenez-en plus sur les <a href=\"#\" onclick=\"showModalInfo('ocean')\" class=\"underline-link\">ecobricks océaniques</a>, les <a href=\"#\" onclick=\"showModalInfo('cigbrick')\" class=\"underline-link\">cigbricks</a> et les <a href=\"#\" onclick=\"showModalInfo('regular')\" class=\"underline-link\">ecobricks réguliers</a>.",
    "010-plastic-from": "D'où vient le plastique ?",
    "010-plastic-from-caption": "D'où provient le plastique de votre ecobrick ?",
    "011-location-full": "Où est situé cet ecobrick ?",
    "011-location-full-caption": "Commencez à taper le nom de votre ville ou village, et nous compléterons le reste en utilisant l'API openstreetmaps, ouverte et non corporative. Évitez d'utiliser votre adresse exacte pour des raisons de confidentialité : votre ville, village ou pays suffisent.",

    "009-select-bottom-color": "Sélectionnez la couleur du fond...",
    "010-no-color-set": "Aucune couleur définie délibérément",
    "011-clear": "Clair",
    "012-white": "Blanc",
    "013-black": "Noir",
    "014-yellow": "Jaune",
    "015-orange": "Orange",
    "016-red": "Rouge",
    "017-pink": "Rose",
    "018-purple": "Violet",
    "019-violet": "Violette",
    "020-dark-blue": "Bleu foncé",
    "021-sky-blue": "Bleu ciel",
    "022-brown": "Marron",
    "023-grey": "Gris",
    "024-silver": "Argent",
    "025-gold": "Or",
    "026-cigbrick-beige": "Beige cigbrick",
    "000-field-required-error": "Ce champ est requis.",

    "011-select-ecobrick-type": "Sélectionnez le type d'ecobrick...",
    "012-regular-ecobrick": "Ecobrick régulier",
    "013-cigbrick": "Cigbrick",
    "014-ocean-ecobrick": "Ecobrick océanique",
    "015-select-plastic-source": "Sélectionnez la source du plastique...",
    "016-home": "Maison",
    "017-business": "Entreprise",
    "018-community": "Quartier",
    "019-factory": "Usine",
    "020-beach": "Plage",
    "021-ocean": "Océan",
    "022-river": "Rivière",
    "023-forest": "Forêt",
    "024-field": "Champ",

    "016-submit-button": '<input type="submit" class="submit-button enabled" value="Suivant: Vérification" aria-label="Envoyer le Formulaire">',

//Modals for density check

    "underDensityTitle": 'Sous Densité',
    "underDensityMessage": "La densité de votre ecobrick de ${density} est inférieure à la norme GEA de 0,33 g/ml. Veuillez vérifier que vous avez correctement saisi le poids et le volume. Si ce n'est pas le cas, repackez votre ecobrick avec plus de plastique pour atteindre la densité minimale. Les lignes directrices de la GEA sont développées pour assurer l'intégrité de la construction, la sécurité incendie et la réutilisabilité d'un ecobrick.",
    "lowDensityTitle": 'Densité Faible',
    "lowDensityMessage": "Attention ! La densité de votre ecobrick de ${density}ml est faible. Il respecte la norme minimale de 0,33 g/ml, mais sa faible densité le rend moins solide, moins sûr contre les incendies et moins réutilisable. Continuez et enregistrez cet ecobrick, mais essayez de tasser plus de plastique la prochaine fois.",
    "greatJobTitle": 'Bon travail !',
    "greatJobMessage": "La densité de votre ecobrick de ${density} est idéale. Il respecte la norme minimale de 0,33 g/ml, ce qui le rend solide, sûr contre les incendies et réutilisable.",
    "highDensityTitle": 'Densité Élevée',
    "highDensityMessage": "Attention, la densité de votre ecobrick de ${density} est très élevée. Votre bouteille de ${volume} remplie de ${weight} de plastique est en dessous de la densité maximale de 0,73 g/ml ; cependant, sa densité élevée la rend presque trop solide et trop lourde pour certaines applications de ecobrick. Continuez, mais gardez cela à l'esprit pour la prochaine fois.",
    "overMaxDensityTitle": 'Au-dessus de la Densité Maximale',
    "overMaxDensityMessage": "La densité de votre ecobrick de ${density} dépasse la norme GEA de 0,73 g/ml. Veuillez vérifier que vous avez saisi correctement le poids et le volume. Si c'est le cas, repackez votre ecobrick avec moins de plastique. Les lignes directrices de la GEA sont développées pour assurer la sécurité et la réutilisabilité des ecobricks pour toutes les applications à court et à long terme.",
    "geaStandardsLinkText": 'Normes GEA',
    "nextRegisterSerial": 'Suivant: Numéro de Série',
    "goBack": 'Retourner',
};


