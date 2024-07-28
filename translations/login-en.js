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


const en_Page_Translations = {
    "001-login-heading": "Welcome back!",
    "002-login-subheading": "Login with your GoBrik or Buwana account credentials.",
    "003-login-email": "<input type=\"text\" id=\"credential_key\" name=\"credential_key\" required placeholder=\"Your e-mail...\">",
    "004-login-password": " <input type=\"password\" id=\"password\" name=\"password\" required placeholder=\"Your password..\"><p class=\"form-caption\">Forget your password? <a href=\"#\" onclick=\"showModalInfo('reset')\" class=\"underline-link\">Reset it.</a></p></div>",
    "006-login-button": "Login",
    "000-no-account-yet": "Don't have an account yet? <a href=\"signup.php\">Signup!</a>"
};


