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


const es_Page_Translations = {
    "001-login-heading": "Iniciar sesión",
    "002-login-subheading": "¡Bienvenido de nuevo a GoBrik!",
    "003-login-email": "<input type=\"text\" id=\"credential_value\" name=\"credential_value\" required placeholder=\"Tu correo electrónico...\">",
    "004-login-password": " <input type=\"password\" id=\"password\" name=\"password\" required placeholder=\"Tu contraseña..\"><p class=\"form-caption\">¿Olvidaste tu contraseña? <a href=\"#\" onclick=\"showModalInfo('reset')\" class=\"underline-link\">Restablécela.</a></p><div id=\"password-error\" class=\"form-field-error\" style=\"display:none;\">👉 La contraseña es incorrecta.</div>",
    "006-login-button": "Iniciar sesión",
    "000-no-account-yet": "¿Aún no tienes una cuenta? <a href=\"signup.php\">¡Regístrate!</a>"
};

