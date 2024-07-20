<?php
include 'lang.php';
$version = '0.26';
$page = 'index';
include '../ecobricks_env.php';

echo '<!DOCTYPE html>
<html lang="' . $lang . '">
<head>
<meta charset="UTF-8">
</head>
<body>
</body>
</html>';
?>


<title>GoBrik | 3.0</title>

<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->


<?php require_once ("../includes/index-inc.php");?>


<div class="clouds-new2">
    <div class="landing-content" style="height:100vh">
        <div class="biosphere"><img src="../webps/biosphere-blanked.webp" width="400" height="400" alt="biosphere"></div>
            <div class="main-landing-graphic" style="width:100%;height:43%;">
                <img src="../webps/ecobrick-team-blanked.webp" style="width:100%;height:43%;" alt="Unite with ecobrickers around the world">
            </div>
        <div class="big-header" data-lang-id="000-lead-header">Together we can keep our plastic out of the biosphere.</div>
        <div class="welcome-text" data-lang-id="001-welcome-text">GoBrik helps manage your ecobricks, projects and plastic transition so that together we can build our greenest visions.</div>
        <div class="sign-buttons" style="display:flex;flex-flow:row;justify-content: center;">
            <div>
                <button type="button" aria-label="sign in" class="sign-innn" onclick="location.href='login.php'" title="Click here to sign in" style="cursor:pointer;">
                <i style="background: url(../svgs/bottle-icon.svg) no-repeat; width:20px; height:26px;display: inline-block;background-size:contain;margin-bottom:-5px;margin-right:4px;"></i><span data-lang-id="002-sign-in">Sign in</span></button>
            </div>

                    <div>
                        <button type="button" aria-label="Sign up" onclick="location.href='go.php'" class="sign-uppp" style="cursor:pointer;">
                        <i style="background: url(../svgs/strike-icon.svg) no-repeat; width:20px; height:26px;display: inline-block;background-size:contain;margin-bottom: -5px;margin-left:4px;"></i><span data-lang-id="003-sign-up">Sign up</span></button>
                    </div>

                </div>

                <div class="tree-text" style="padding-bottom:15px;" data-lang-id="004-account-options">
                Use your GoBrik or Buwana account to sign in.
                No account? Sign up for free!
                </div>

            </div><!--landing-content-->



    <div class="bottom-scope" style="width:100%;height:100%;">
         <div class="landing-content">
            <div class="tree-coins" data-lang-id="005-second-feature-img" ><img src="../webps/2023-tree-blank.webp" style="width:100%;" alt="Build your greenest visions with ecobricks">


            </div>

            <div class="welcome-text" data-lang-id="006-second-text">
                Together we're securing plastic out of the biosphere to make building blocks, generate brikcoins and co-create green spaces.
               <br><br>
               <img src="../svgs/aes-brk.svg" style="width:200px;" width="200" height="77" alt="Introducing Brikcoins and AES Plastic Offsetting">
            </div>

            <div class="tree-text">
                GoBrik provides ecobrickers and their communities with the tools to manage their ecobricking and to quantify its ecological value.
            </div>


        </div><!--closes Landing content-->
    </div>



</div><!--closes main and starry background-->

	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2024.php");?>

</div><!--close page content-->

</body>

</html>
