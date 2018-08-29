<?php
    require("include/head.php");
    require("include/navbar.php");
    require("../controllers/errorController.php");
    require_once("../models/movie.php");
?>

<div class="container col-lg-12"><!-- This is main DIV with all the content -->

    <?php 
        ErrorController::errorHandlerSession();
        //Update session variable which we use to track our previous page.
        //This has to come after the logic of the page but before any links or
        //other redirects so the next page know where we came from.
        if(session_status()==PHP_SESSION_NONE){
            session_start();
        }
        $_SESSION["requestFrom"]="input";
    ?>

    <div class="container col-lg-10 pt-5">
        <form enctype="multipart/form-data" action="../controllers/inputController.php" method="post">
        <!-- DEFINE MAX UPLOAD SIZE HERE  -->
        <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
            <div class="row">
                <div class="form-group form-inline col-6">
                    <label for="Input1">Naslov:</label>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" name="title" id="Input1" placeholder="Ime filma...">
                    </div> 
                </div><!--"form-group form-inline col-6"-->

                <div class="form-group form-inline col-6">
                    <label for="Input2">Žanr:</label>
                    <div class="col-lg-6">
                            <select class="form-control" name="genreID" id="Input2">
                                <?php Movie::createDropdown("genres") ?>
                            </select>
                    </div>    
                </div><!--"form-group form-inline col-6"-->

                <div class="form-group form-inline col-6">
                    <label for="Input3">Godina:</label>
                    <div class="col-lg-6">
                            <select class="form-control" name="year" id="Input3">
                                <?php Movie::createDropdown("year") ?>
                            </select>
                    </div>    
                </div><!--"form-group form-inline col-6"-->

                <div class="form-group form-inline col-6">
                    <label for="Input4">Trajanje:</label>
                    <div class="col-lg-6">
                        <input type="text" class="form-control" name="runtime" id="Input4" placeholder="Trajanje filma u minutama...">
                    </div>    
                </div><!--"form-group form-inline col-6"-->

                <div class="form-group form-inline col-6">
                    <label for="Input5">Slika:</label>
                    <div class="col-lg-6">
                        <input type="file" class="form-control-file" id="Input5" name="image" placeholder="Putanja do slike...">
                    </div>    
                </div><!--"form-group form-inline col-6"-->

                <div class="col-8 ">
                    <input type="submit">
                </div><!--"col-8 text-center"-->            
            </div>
        </form>
</div>
<?php 
    if (isset($_SESSION['genreCount'])) {
        echo'<pre>'.var_dump($_SESSION['genreCount']).'<pre>';
    }
    require("include/footer.php") 
?>