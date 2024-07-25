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
    "001-login-heading": "Connexion",
    "002-login-subheading": "Bienvenue à nouveau sur GoBrik !",
    "003-login-email": "<input type=\"text\" id=\"credential_value\" name=\"credential_value\" required placeholder=\"Votre e-mail...\">",
    "004-login-password": " <input type=\"password\" id=\"password\" name=\"password\" required placeholder=\"Votre mot de passe..\"><p class=\"form-caption\">Vous avez oublié votre mot de passe ? <a href=\"#\" onclick=\"showModalInfo('reset')\" class=\"underline-link\">Réinitialisez-le.</a></p><div id=\"password-error\" class=\"form-field-error\" style=\"display:none;\">👉 Le mot de passe est incorrect.</div>",
    "006-login-button": "Connexion",
    "000-no-account-yet": "Vous n'avez pas encore de compte ? <a href=\"signup.php\">Inscrivez-vous !</a>"
};
