<?php

include 'lang.php';
$version = '0.342';
$page = 'signup';
include '../ecobricks_env.php';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
';
?>


<title>Signup | GoBrik 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->



<?php require_once ("../includes/signup-inc.php");?>

<div class="clouds-new2" style=" margin-bottom:25px;background-color:var(--general-background);">

        <div id="landing-content" style="width:100%;height:100vh;">

            <div data-lang-id="000-testing">Testing one two three four</div>

        </div><!--closes Landing content-->
    </div>



</div><!--closes main and starry background-->

	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2024.php");?>

</div><!--close page content-->

</body>

</html>
