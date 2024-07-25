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

const id_Page_Translations = {
    "001-login-heading": "Masuk",
    "002-login-subheading": "Selamat datang kembali di GoBrik!",
    "003-login-email": "<input type=\"text\" id=\"credential_value\" name=\"credential_value\" required placeholder=\"Email Anda...\">",
    "004-login-password": " <input type=\"password\" id=\"password\" name=\"password\" required placeholder=\"Kata sandi Anda..\"><p class=\"form-caption\">Lupa kata sandi Anda? <a href=\"#\" onclick=\"showModalInfo('reset')\" class=\"underline-link\">Setel ulang.</a></p><div id=\"password-error\" class=\"form-field-error\" style=\"display:none;\">👉 Kata sandi salah.</div>",
    "006-login-button": "Masuk",
    "000-no-account-yet": "Belum punya akun? <a href=\"signup.php\">Daftar!</a>"
};
