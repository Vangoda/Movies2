<?php
//This file takes care of validating selection from index page
// and returning results to the index page.

require("../controllers/indexController.php");
require("../models/movie.php");

//If there is no selection person should not be on this page.
//We will redirect them back to index page so they can make a selection.
indexController::validateSelection("isSet");

$selection = indexController::validateSelection("value");

$movieList = Movie::fillMovieList($selection);
//We will save our list to session variable so we can use it on index page
session_start();
$_SESSION['movieList'] = $movieList;
//Update location
$_SESSION["requestFrom"]="selectionController";
//Redirect to index
header('Location: ../pages/index.php');
