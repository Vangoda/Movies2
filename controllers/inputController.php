<?php 
// This file takes care of validating the form data, as well as saving a new movie if validated.
require_once("../models/movie.php");

if (isset($_GET['deleteID'])) {
    Movie::deleteMovie($_GET['deleteID']);
}else{
//Create a new Movie object whose properties will be filled with data from $_POST.
//During creation of object we also check if the data is even here and return 
//an error if something is missing.
$Movie = new Movie;

//Next we will validate our data, both the image file and information from form.
//We will only validate if all the neccesary data is present, otherwise go back
//to input page with error.
if(empty($_SESSION['error']))
    if($Movie->validate()){
        $Movie->save();
    }
}
//Update where we are coming from.
if(session_status()==PHP_SESSION_NONE){
    session_start();
}

$_SESSION["requestFrom"]="inputController";

header('Location: ../pages/input.php');
die("DON'T MESS WITH THE PAGE!");