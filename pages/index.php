<?php
require("include/head.php");
require("include/navbar.php");
require("../controllers/indexController.php");
require("../controllers/errorController.php");

?>
<div class="container col-lg-12"><!-- This is main DIV with all the content -->
    <div class="row justify-content-center">
        <div class="col-8 text-center"> <!-- This will be our letter selection bar-->
            <ul>
                <?php IndexController::drawList("A","Z"); ?>
            </ul>
        </div>
    </div>
    <?php
        //Display error box with errors if any.
        ErrorController::errorHandler();
        //Update where we are coming from.
        if(session_status()==PHP_SESSION_NONE){
            session_start();
        }
        $_SESSION["requestFrom"]="index";
    ?>
    <div class="container col-6">
        <?php indexController::drawMovieList(); ?>
    </div>
</div>


<?php require("include/footer.php"); ?>