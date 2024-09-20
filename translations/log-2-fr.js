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


/*-----------------------------------
EXTRAITS DE TRADUCTION DE TEXTE POUR GOBRIK.com
-----------------------------------*/

// Esperluette (&) : Doit être échappée en &amp; car elle commence les références de caractères HTML.
// Inférieur à (<) : Doit être échappé en &lt; car il commence une balise HTML.
// Supérieur à (>) : Doit être échappé en &gt; car il termine une balise HTML.
// Guillemet double (") : Doit être échappé en &quot; lorsqu'il est à l'intérieur des valeurs d'attributs.
// Guillemet simple/apostrophe (') : Doit être échappé en &#39; ou &apos; lorsqu'il est à l'intérieur des valeurs d'attributs.
// Barre oblique inversée (\) : Doit être échappée en \\ dans les chaînes JavaScript pour éviter de terminer prématurément la chaîne.
// Barre oblique (/) : Doit être échappée en \/ dans les balises </script> pour éviter de fermer prématurément un script.


const fr_Page_Translations = {

 "001-form-title": "Enregistrer le Numéro de Série et Prendre une Photo",
    "002-form-description-1": "Votre ecobrick a été enregistré avec un poids de ",
    "003-form-description-2": "un volume de ",
    "004-form-description-3": "et une densité de ",
    "005-form-description-4": " Votre ecobrick a reçu le numéro de série :",
    "006-enscribe-label": "Comment souhaitez-vous inscrire le numéro de série sur votre ecobrick ?",
    "007-enscribe-option-1": "Sélectionnez une option...",
    "008-enscribe-option-2": "⭐Marqueur permanent",
    "009-enscribe-option-3": "👎 Marqueur soluble dans l'eau",
    "010-enscribe-option-4": "⭐⭐ Peinture émail",
    "011-enscribe-option-5": "⭐⭐ Vernis à ongles",
    "012-enscribe-option-6": "⭐⭐⭐ Insert en plastique",
    "013-enscribe-option-7": "Autre",
    "014-photo-options-label": "Quel type de photo souhaitez-vous enregistrer de votre ecobrick ?",
    "015-photo-options-option-1": "Sélectionnez une option...",
    "016-photo-options-option-2": "Photo de l’écobrique courant",
    "017-photo-options-option-3": "Une photo selfie",
    "018-photo-options-option-4": "Une photo de base et une photo selfie",
    "019-feature-photo": "Téléchargez une photo de base de l'ecobrick :",
    "020-feature-photo-step-1": "Prenez une photo en portrait vertical",
    "021-feature-photo-step-2": "Assurez-vous que votre photo montre clairement le numéro de série et le poids",
    "022-feature-photo-step-3": "Assurez-vous que votre photo montre la couleur du fond de votre ecobrick",
    "023-feature-photo-step-4": "Assurez-vous que votre photo montre le haut de votre ecobrick",
    "024-feature-photo-step-5": "Assurez-vous que vos données sont inscrites de manière permanente !",
    "025-feature-photo-step-6": "N'utilisez pas d'étiquette externe pour marquer l'ecobrick",
    "026-basic-feature-desc": "Prenez ou sélectionnez une photo de votre ecobrick sérialisé.",
    "027-label-selfie": "Téléchargez une selfie de l'ecobrick :",
    "028-selfie-photo-step-1": "Assurez-vous que votre photo est en format paysage",
    "029-selfie-photo-step-2": "Assurez-vous que votre photo montre clairement le numéro de série et le poids",
    "030-selfie-photo-step-3": "Assurez-vous que votre photo montre la couleur du fond de votre ecobrick",
    "031-selfie-photo-step-4": "Assurez-vous que votre photo montre le haut de votre ecobrick",
    "032-selfie-photo-step-5": "Assurez-vous que vos données sont inscrites de manière permanente !",
    "033-selfie-photo-step-6": "N'utilisez pas d'étiquette externe pour marquer l'ecobrick",
    "034-selfie-photo-step-7": "Et souriez !",
    "035-selfie-upload": '📷 Prendre une photo selfie<input type="file" id="selfie_photo_main" name="selfie_photo_main">',
    "035b-no-file-chosen": "Aucun fichier choisi",
    "036-another-photo-optional": "Téléchargez votre selfie de l'ecobrick.",

// Modales pour vérification de densité

    "underDensityTitle": 'Densité Insuffisante',
    "underDensityMessage": "La densité de votre ecobrick de ${density} est inférieure à la norme GEA de 0,33g/ml. Veuillez vérifier que vous avez correctement entré le poids et le volume. Sinon, repackez votre ecobrick avec plus de plastique pour atteindre la densité minimale. Les directives GEA sont développées pour assurer l'intégrité structurelle, la sécurité incendie et la réutilisabilité d'un ecobrick.",
    "lowDensityTitle": 'Densité Basse',
    "lowDensityMessage": "Attention ! La densité de votre ecobrick de ${density}ml est faible. Elle respecte la norme minimale de 0,33g/ml, mais sa densité le rend moins solide, moins sûr contre les incendies et moins réutilisable qu'il pourrait l'être. Continuez et enregistrez cet ecobrick, mais essayez de compacter plus de plastique la prochaine fois.",
    "greatJobTitle": 'Bon travail !',
    "greatJobMessage": "La densité de votre ecobrick de ${density} est idéale. Elle respecte la norme minimale de 0,33g/ml, ce qui le rend solide, sûr contre les incendies et réutilisable.",
    "highDensityTitle": 'Densité Élevée',
    "highDensityMessage": "Attention, la densité de votre ecobrick de ${density} est très élevée. Votre bouteille de ${volume} emballée avec ${weight} de plastique est inférieure à la densité maximale de 0,73g/ml, mais sa haute densité la rend presque trop solide et trop lourde pour certaines applications d'ecobrick. Continuez, mais gardez cela à l'esprit pour la prochaine fois.",
    "overMaxDensityTitle": 'Au-dessus de la Densité Maximale',
    "overMaxDensityMessage": "La densité de votre ecobrick de ${density} dépasse la norme GEA de 0,73g/ml. Veuillez vérifier que vous avez correctement entré le poids et le volume. Si c'est le cas, repackez votre ecobrick avec moins de plastique. Les directives GEA sont développées pour assurer la sécurité et la réutilisabilité des ecobricks pour toutes les applications à court et long terme.",
    "geaStandardsLinkText": 'Normes GEA',
    "nextRegisterSerial": 'Suivant : Enregistrer le Numéro de Série',
    "goBack": 'Retour',

};
