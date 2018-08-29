<?php
//This will take care of accesing database and creating movie objects
//It will also hold methods for validating various proeperties of a movie
require_once("../models/connection.php");
require_once("../controllers/translateController.php");
class Movie
{
    //Declaring properties of Movie object
    public $title;
    public $genreID;
    public $year;
    public $runtime;
    public $imageFile;

    //When the new Movie object is created we want to immediatelly check if the values in the form exist
    //and if they do assign them to object properties.
    function __construct(){
        if(session_status()==PHP_SESSION_NONE){
            session_start();
        }
        //Reset error array before validation.
        $_SESSION["error"] = array();
        //echo "<pre>".var_dump($_POST)."</pre>";
        //echo "object created";
        //var_dump($_SESSION);
        
        $_SESSION['postData'] = $_POST;
        $_SESSION['postDataTitle'] = $_POST['title'];
        $_SESSION['file'] = $_FILES['image'];
        
        //Validate form fields. If a field iy empty error message is generated and
        //saved to session var to be displayed. 
        if(!empty($_POST['title']) AND !empty($_POST['runtime']) AND $_FILES['image']['size']!=0){
        $this->title = $_POST["title"];
        $this->genreID = $_POST["genreID"];
        $this->year = $_POST["year"];
        $this->runtime = $_POST["runtime"];
        $this->imageFile = $_FILES['image'];

        //echo "<pre>".var_dump($_SESSION["error"])."</pre>";

        }else{
            foreach ($_POST as $formField=>$value) {
                if(empty($value)){
                    array_push($_SESSION["error"],"Polje ".TranslateController::translateVarName($formField)." je prazno");
                }
            }
            if(!file_exists($this->imageFile['tmp_name']) || !is_uploaded_file($this->imageFile['tmp_name'])){
                array_push($_SESSION["error"],"Niste odabrali datoteku za upload");
            }
        }    
    }//end of __construct()

    //This will return an array with movies from database that start with selected letter.
    public static function fillMovieList($selection){
        
        $connection = DB::connectDb();

        //I will not use prepared statement as I have already checked that $selection is good and it would mess with wildcard character
        //in my querry. 
        //var_dump($selection);
        $query = "SELECT * FROM movies WHERE title LIKE '".$selection."%'";
        $movieList = array();

        //Because it doesn't work  with self::$movieList=$connection->query($query);
        foreach ($connection->query($query) as $row) {
            array_push($movieList,$row);
        }
        
        //echo $query;
        //self::$movieList=$connection->query($query);
        return $movieList;
    }//end of fillMovieList()

    //Helper function for generating dropdown based on switch. 
    public static function createDropdown($switch){

        $connection = DB::connectDb();

        switch ($switch) {
            case 'genres':
                $query = "SELECT * FROM genres";
                foreach ($connection->query($query) as $genre) {
                    echo "<option value='".$genre["id"]."'>".$genre["name"]."</option>";
                }
                break;
            
            case 'year':
                foreach (range(1900,date("Y")) as $year) {
                    echo "<option value='".$year."'>".$year."</option>";
                }
                break;

            default:
                echo "You have to specify type of dropdown as a string";
                break;
        }        
    }//end of createDropdown()


    //Validation functions
    public function validate(){
        if ($this->validateImage() AND $this->validateFormData()) {
            return true;
        }else{
            return false;
        }
    }//end of validate()
    
    //Verify that image file is good.
    private function validateImage(){
        try {
   
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (!isset($this->imageFile['error']) OR is_array($this->imageFile['error'])){
                throw new RuntimeException('Netočni parametri');
            }
        
            // Check $this->imageFile['error'] value.
            switch ($this->imageFile['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('Datoteka nije poslana');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Maksimalna veličina slike je 10MB');
                default:
                    throw new RuntimeException('Nepoznata greška');
            }
        
            // Check for the file size.
            if ($this->imageFile['size'] > 10485760) {
                throw new RuntimeException('Maksimalna veličina slike je 10MB');
            }
        
            // DO NOT TRUST $this->imageFile['mime'] VALUE !!
            // Check MIME Type by yourself. Create a new finfo object
            // which contains file information. Compare MIME type with
            // allowed file types.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $typeAllowed = array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            );
            $extension = array_search($finfo->file($this->imageFile['tmp_name']),$typeAllowed,true);
            //Save extension to session so we can access it later.
            $_SESSION['fileExtension']=$extension;

            if ($extension === false) {
                throw new RuntimeException('Datoteka mora biti slika');
            }
            return true;
        } catch (RuntimeException $e) {
            array_push($_SESSION['error'],$e->getMessage());
        }
        return false;
    }// end of validateImage()
    
    private function validateFormData(){
        //missing code
        if(
            $this->validateTitle() AND
            $this->validateGenre() AND
            $this->validateYear() AND
            $this->validateRuntime()
        ){
            return true;
        }else {
            return false;
        }
    }//end of validateFormData()


    //Functions for saving form data and file.
    public function save(){
        $this->saveImage();
    }

    private function saveImage(){
            // You should name it uniquely.
            // DO NOT USE $this->imageFile['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.
            if (!move_uploaded_file(
                $this->imageFile['tmp_name'],
                sprintf('../images/%s.%s',
                    sha1_file($this->imageFile['tmp_name']),
                    $_SESSION['fileExtension']
                )
            )) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
        
            echo 'File is uploaded successfully.';
    }//end of saveImage()
}
