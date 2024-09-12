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


// English translations

const en_Page_Translations = {


//first activate page

    "0001-activate-notice": "since you've last logged in, we've made a massive upgrade to GoBrik.",
    "0002-activate-explantion-1": "Our old version of GoBrik ran on corporate servers and code.   We've let this pass pass away.",
    "0002-activate-explantion-2": "In its place, we have migrated all our data to our own independent, self-run server.  Our new GoBrik 3.0 is now 100% open source fully focused on ecological accountability.  We've also developed our own Buwana login system as an alternative to Google and Apple login.  To join us on the regenerated GoBrik with <?php echo htmlspecialchars($email_addr); ?>, please take a minute to upgrade to a Buwana account.",
    "0003-activate-button": '<input type="submit" id="submit-button" value="🍃 Upgrade Account!" class="submit-button activate">',
    "0004-buwana-accounts": "Buwana accounts are designed with ecology, security, and privacy in mind. Soon, you'll be able to login to other great regenerative apps movement in the same way you login to GoBrik!.",
    "0005-new-terms": "New Buwana & GoBrik Terms of Service",
    "0005-regen-blog": "Why?  Read our 'Great GoBrik Regeneration' blog post.",
    "0006-github-code": "New Github Source Code Repository",
    "0007-not-interested": "If you're not interested and would like your old ",
    "0009-that-too": " account completely deleted, you can do that too.",
    "0010-delete-button": 'Delete My Account',
    "0011-warning": "WARNING: This cannot be undone.",


//activate-2

  "001-alright": "Alright",
  "002-lets-confirm": "let's confirm your email.",
  "003-to-create": "To create your Buwana GoBrik account we need to confirm your chosen credential. This is how we'll keep in touch and keep your account secure. Click the send button and we'll send an account activation code to:",
  "004-send-email-button": '<input type="submit" name="send_email" id="send_email" value="📨 Send Code" class="submit-button activate">',
  "006-enter-code": "Please enter your code:",
  "007-check-email": "Check your email",
  "008-for-your-code": "for your account confirmation code. Enter it here:",
  "009-no-code": "Didn't get your code? You can request a resend of the code in",
  "010-email-no-longer": "Do you no longer use this email address?<br>If not, you'll need to <a href=\"signup.php\">create a new account</a> or contact our team at support@gobrik.com.",
  "011-change-email": "Want to change your email?",
  "012-go-back-new-email": "Go back to enter a different email address.",

  // Activate-3.php


    "014-your-continent": "On what continent do you live?",
    "015-continent-place-holder": "Select your continent...",
    "014-your-country": "In what country do you reside?",
    "015-country-place-holder": "Select your country of residence...",
    "014-your-watershed": "In what river basin do you live?",
    "015-watershed-place-holder": "Select your river basin...",
    "016-dont-know": "I am not sure",
    "016-dont-know-alt": "Not listed",
    "018-what-is-watershed": "Almost everyone lives in one of Earth's 200 main watersheds. See if you can locate yours! Learn more about ",
    "019-watershed": "watersheds",
    "016-submit-complete-button": "Complete Setup",
    "020-no-watershed-worries": '<input type="submit" id="submit-button" value="Complete Setup" class="submit-button enabled"><p style="font-size:smaller;">Can\'t find your watershed?  No worries! We\'re still working on adding them.</p>',




};


