/*-----------------------------------
FRAGMENTOS DE TRADUCCIÓN DE TEXTO PARA GOBRIK.com
-----------------------------------*/

// Ampersand (&): Debe escaparse como &amp; porque inicia referencias de caracteres HTML.
// Menor que (<): Debe escaparse como &lt; porque inicia una etiqueta HTML.
// Mayor que (>): Debe escaparse como &gt; porque finaliza una etiqueta HTML.
// Comillas dobles ("): Deben escaparse como &quot; dentro de los valores de los atributos.
// Comillas simples/apóstrofe ('): Deben escaparse como &#39; o &apos; dentro de los valores de los atributos.
// Barra invertida (\): Debe escaparse como \\ en cadenas de JavaScript para evitar finalizar la cadena prematuramente.
// Barra diagonal (/): Debe escaparse como \/ en etiquetas </script> para evitar cerrar prematuramente un script.


const es_Page_Translations = {

 "001-form-title": "Registrar Serie y Tomar Foto",
    "002-form-description-1": "Tu ecobrick ha sido registrado con un peso de ",
    "003-form-description-2": "un volumen de ",
    "004-form-description-3": "y una densidad de ",
    "005-form-description-4": " Tu ecobrick ha sido asignado con el número de serie:",
    "006-enscribe-label": "¿Cómo te gustaría inscribir el número de serie en tu ecobrick?",
    "007-enscribe-option-1": "Selecciona uno...",
    "008-enscribe-option-2": "Marcador permanente",
    "009-enscribe-option-3": "Marcador soluble en agua 👎",
    "010-enscribe-option-4": "Pintura de esmalte",
    "011-enscribe-option-5": "Esmalte de uñas",
    "012-enscribe-option-6": "Inserto de plástico",
    "013-enscribe-option-7": "Otro",
    "014-photo-options-label": "¿Qué tipo de foto te gustaría registrar de tu ecobrick?",
    "015-photo-options-option-1": "Selecciona uno...",
    "016-photo-options-option-2": "Una foto básica del ecobrick",
    "017-photo-options-option-3": "Una foto selfie",
    "018-photo-options-option-4": "Una foto básica y una foto selfie",
    "019-feature-photo": "Sube una foto básica del ecobrick:",
    "020-feature-photo-step-1": "Toma una foto de retrato vertical",
    "021-feature-photo-step-2": "Asegúrate de que tu foto muestre claramente el número de serie y el peso",
    "022-feature-photo-step-3": "Asegúrate de que tu foto muestre el color del fondo de tu ecobrick",
    "023-feature-photo-step-4": "Asegúrate de que tu foto muestre la parte superior de tu ecobrick",
    "024-feature-photo-step-5": "¡Asegúrate de que tus datos estén inscritos de forma permanente!",
    "025-feature-photo-step-6": "No utilices una etiqueta externa para marcar el ecobrick",
    "026-basic-feature-desc": "Toma o selecciona una foto de tu ecobrick serializado.",
    "027-label-selfie": "Sube una selfie del ecobrick:",
    "028-selfie-photo-step-1": "Asegúrate de que tu foto sea horizontal (paisaje)",
    "029-selfie-photo-step-2": "Asegúrate de que tu foto muestre claramente el número de serie y el peso",
    "030-selfie-photo-step-3": "Asegúrate de que tu foto muestre el color del fondo de tu ecobrick",
    "031-selfie-photo-step-4": "Asegúrate de que tu foto muestre la parte superior de tu ecobrick",
    "032-selfie-photo-step-5": "¡Asegúrate de que tus datos estén inscritos de forma permanente!",
    "033-selfie-photo-step-6": "No utilices una etiqueta externa para marcar el ecobrick",
    "034-selfie-photo-step-7": "¡Y sonríe!",
    "035-selfie-upload": '📷 Tomar Foto Selfie<input type="file" id="selfie_photo_main" name="selfie_photo_main">',
    "035b-no-file-chosen": "Ningún archivo elegido",
    "036-another-photo-optional": "Sube tu selfie del ecobrick.",

// Modales para verificación de densidad

    "underDensityTitle": 'Baja Densidad',
    "underDensityMessage": "La densidad de tu ecobrick de ${density} está por debajo del estándar GEA de 0.33g/ml. Por favor, verifica que hayas ingresado el peso y el volumen correctamente. Si no, reempaqueta tu ecobrick con más plástico para alcanzar la densidad mínima. Las pautas de la GEA están desarrolladas para asegurar la integridad estructural, seguridad contra incendios y reutilización de un ecobrick.",
    "lowDensityTitle": 'Densidad Baja',
    "lowDensityMessage": "¡Cuidado! La densidad de tu ecobrick de ${density}ml es baja. Cumple con el estándar mínimo de 0.33g/ml, sin embargo, su densidad lo hace menos sólido, seguro contra incendios y reutilizable de lo que podría ser. Continúa y registra este ecobrick, pero intenta empaquetar más plástico la próxima vez.",
    "greatJobTitle": '¡Buen trabajo!',
    "greatJobMessage": "La densidad de tu ecobrick de ${density} es ideal. Cumple con el estándar mínimo de 0.33g/ml, lo que lo hace sólido, seguro contra incendios y reutilizable.",
    "highDensityTitle": 'Alta Densidad',
    "highDensityMessage": "Cuidado, la densidad de tu ecobrick de ${density} es muy alta. Tu botella de ${volume} empaquetada con ${weight} de plástico está por debajo de la densidad máxima de 0.73g/ml, sin embargo, su alta densidad lo hace casi demasiado sólido y pesado para ciertas aplicaciones de ecobrick. Continúa, pero tenlo en cuenta para la próxima vez.",
    "overMaxDensityTitle": 'Por Encima de la Densidad Máxima',
    "overMaxDensityMessage": "La densidad de tu ecobrick de ${density} supera el estándar GEA de 0.73g/ml. Verifica que hayas ingresado correctamente el peso y el volumen. Si es así, reempaqueta tu ecobrick con menos plástico. Las pautas de la GEA están desarrolladas para asegurar la seguridad y usabilidad de los ecobricks para todas las aplicaciones a corto y largo plazo.",
    "geaStandardsLinkText": 'Normas GEA',
    "nextRegisterSerial": 'Siguiente: Registrar Serie',
    "goBack": 'Volver',

};
