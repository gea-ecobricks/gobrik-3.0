<?php
include 'lang.php';
$version = '0.258';
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



    <div class="landing-content" >


        <div class="clouds-new2" style=" padding-top:10vh; margin-bottom: -30px;
        padding-bottom: 10px;margin-top:-10px">

             <div class="biosphere"><img src="../webps/biosphere-blank.webp" width="400" height="400" alt="biosphere"></div>

            <div class="main-landing-graphic" style="width:100%;height:43%;"><img src="../webps/ecobrick-team-blanked.webp" style="width:100%;height:43%;" alt="Unite with ecobrickers around the world"></div>

                 </div>



                <div class="big-header">Together we can keep our plastic out of the biosphere.</div>

                <div class="welcome-text">
                GoBrik helps manage your ecobricks, projects and plastic transition so that together we can build our greenest visions.
                </div>

                <div class="sign-buttons" style="display:flex;flex-flow:row;justify-content: center;">

                    <div>
                        <button type="button" aria-label="sign in" class="sign-innn" onclick="location.href='go.php#home'" title="Click here to sign in" style="cursor:pointer;">
                        <i style="background: url(../svgs/bottle-icon.svg) no-repeat; width:20px; height:26px;display: inline-block;background-size:contain;margin-bottom:-5px;margin-right:4px;"></i>Sign in</button>
                    </div>

                    <div>
                        <button type="button" aria-label="Sign up" onclick="location.href='go.php'" class="sign-uppp" style="cursor:pointer;">
                        <i style="background: url(../svgs/strike-icon.svg) no-repeat; width:20px; height:26px;display: inline-block;background-size:contain;margin-bottom: -5px;margin-left:4px;"></i>Sign up</button>
                    </div>

                </div>

                <div class="tree-text" style="padding-bottom:15px;">
                Use your GoBrik account to sign in.
                No account? Sign up for free!
                </div>

            </div><!--landing-content-->






<p data-lang-id="000-testing">Testing one two and three</p>



</div><!--closes main and starry background-->

	<!--FOOTER STARTS HERE-->

	<?php require_once ("../footer-2024.php");?>

</div><!--close page content-->

</body>

</html>
