<?php

class IndexController{
    //Draw list with letters in alphabetical order, we must define range
    public static function drawList($start,$end){
        foreach (range($start,$end) as $char) {
            echo "<li class='list-inline-item vDivider selectionList ";
            if($char==$end){echo"vDividerRight";}
            echo "'><a href=../controllers/selectionController.php?selection=".$char.">".$char."</a></li>";
        }
    }

    public static function drawMovieList(){
        //echo '<pre>' . var_export($_SESSION["movieList"], true) . '</pre>';
        if(!empty($_SESSION["movieList"])){
            foreach ($_SESSION["movieList"] as $movie) {
                include ("../pages/include/movieBox.php");
            }
        }
    }

    //This function takes care of validating selection on the index page
    public static function validateSelection($switch){
        switch ($switch) {
            case 'isSet':
                if(empty($_GET)){
                    header('Location: ../pages/index.php');
                    die("DON'T MESS WITH THE PAGE!");
                }
                break;
            
            case 'value':
                if (ctype_upper($_GET["selection"]) and strlen($_GET["selection"])==1) {
                    return $_GET["selection"];
                }else{
                    header('Location: ../pages/index.php?error=SELECTION DID NOT PASS VALIDATION');
                    die("DON'T MESS WITH THE PAGE!");
                }
                break;
        }
    }
}