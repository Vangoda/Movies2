<?php
//This will take care of accesing database and creating movie objects
//It will also hold methods for validating various proeperties of a movie
require_once("../models/connection.php");
require_once("../controllers/translateController.php");
require_once("../controllers/errorController.php");
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

    //Fetch all the movies from database
    public static function fillMovieTable(){
        $connection = DB::connectDb();
        $stmt = $connection->prepare('SELECT * FROM movies');
        $stmt->execute();
        return $stmt->fetchAll();
    }

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
        } catch (RuntimeException $e) {
            array_push($_SESSION['error'],$e->getMessage());
            return false;
        }
        return true;
        
    }// end of validateImage()
    
    //Return true if all the data in Form is correct, false in any other case.
    private function validateFormData(){
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

    //Validation of the title field
    private function validateTitle(){

        //We will not a accept a title if we already have it in base
        //If the title already exists we will display error message.
        $connection = DB::connectDb();
        $stmt = $connection->prepare("SELECT * FROM movies WHERE title = :title");
        $stmt->execute([':title' => $this->title]);
        if($stmt->fetchAll()){
           array_push($_SESSION['error'],'Film sa istim nazivom već postoji');
           return false;
        }

        //Start validation
        //List of accepted characters and symbols
        $charList = array(' ',',','.','!','?','-','š','đ','č','ć','ž','Š','Đ','Č','Ć','Ž');
        //We will need the string without special characters and symbols to do an alnum check
        $titleStriped = ErrorController::stripText($this->title,$charList);
        try{
            if (strlen($this->title) < 1 OR strlen($this->title) > 20) {
                throw new RuntimeException('Duljina naslova mora biti najmanje 1, a najvise 20 znakova');
            }
            if (!ctype_alnum($titleStriped)) {
                throw new RuntimeException('Naslov ne smije sadrzavati specijalne znakove');
            }
        }catch(RuntimeException $e){
            array_push($_SESSION['error'],$e->getMessage());
            return false;
        }
        return true;
    }

    //Validation of the genre field
    private function validateGenre(){

        //Open connection to database and cont how manz genres there are. Value of genre
        //field cannot be higher than the count of genres. It also has to have a positive value
        //and it must be an integer.
        $connection = DB::connectDb();
        $query = "SELECT * FROM genres";
        $genreList = array();
        foreach ($connection->query($query) as $row) {
            array_push($genreList,$row);
        }
        $genreCount = count($genreList);
        $_SESSION['genreCount']=$genreCount;

        try{
            if (!is_numeric($this->genreID)) {
                throw new RuntimeException('Molim Vas da odaberete zanr iz izbornika, predali ste krivi tip varijable');
            }
            if ($this->genreID <= 0 OR $this->genreID > $genreCount) {
                //$_SESSION['genreCount']=$genreCount;
                throw new RuntimeException('Žanr mora biti neki od ponuđenih, nekako ste poslali vrijednost koja ne postoji u bazi');
            }
        }catch(RuntimeException $e){
            array_push($_SESSION['error'],$e->getMessage());
            return false;
        }
        return true;
    }

    //Validation of the year field
    private function validateYear(){
        try{
            if ($this->year < 1900 OR $this->year >= date("Y")) {
                throw new RuntimeException('Godina mora biti broj izmedu sadasnje i 1900te');
            }
        }catch(RuntimeException $e){
            array_push($_SESSION['error'],$e->getMessage());
            return false;
        }
        return true;
    }

    //Validate the runtime field
    private function validateRuntime(){
        try{
            if ($this->runtime < 1 OR $this->runtime >1000) {
                throw new RuntimeException('Trajanje filma mora biti broj ne veci od 1000 i ne manji od 1');
            }
        }catch(RuntimeException $e){
            array_push($_SESSION['error'],$e->getMessage());
            return false;
        }
        return true;
    }

    //Functions for saving form data and file.
    public function save(){
        if(
        $this->saveImage() AND
        $this->saveFormData()
        ){
            session_start();
            $_SESSION['saveStatus'] = true;
            return true;
        }
        $_SESSION['saveStatus'] = false;
        return false;
    }

    private function saveImage(){
            // You should name it uniquely.
            // DO NOT USE $this->imageFile['name'] WITHOUT ANY VALIDATION !!
            // We will hash the file name to ensure it is unique. Function will
            // return TRUE if it saves the file succesfully, otherwise throw an exception.
            try{
                $hashedName = sprintf('../images/%s.%s',sha1_file($this->imageFile['tmp_name']),$_SESSION['fileExtension']);
                if (!move_uploaded_file(
                    $this->imageFile['tmp_name'],
                    $hashedName
                    )
                ) {
                    throw new RuntimeException('Failed to move uploaded file.');
                }
            } catch (RuntimeException $e){
                array_push($_SESSION['error'],$e->getMessage());
                return false;
            }
            $this->imageFile['fullPath'] = $hashedName;
            return true;
    }//end of saveImage()

    private function saveFormData(){session_start();
        try{
            $connection = DB::connectDb();
            $stmt = $connection->prepare('INSERT INTO movies (title,genreID,year,runtime,imgPath) VALUES (:title,:genreID,:year,:runtime,:imgPath)');
            if(!$stmt->execute(
                [
                ':title'=>$this->title , 
                ':genreID'=>$this->genreID,
                ':year'=>$this->year ,
                'runtime'=>$this->runtime,
                'imgPath'=>$this->imageFile['fullPath']
                ]
                )){
                    throw new RuntimeException('Insertion into database failed');
                }
            return true;
        }catch(RuntimeException $e){
            array_push($_SESSION['error'],$e->getMessage());
            return false;
        }
    }

    public static function deleteMovie($id){
        session_start();
        try{
            $connection = DB::connectDb();
            $stmt = $connection->prepare('DELETE FROM movies WHERE id = :id');
            if (!$stmt->execute([':id'=>$id])) {
                throw new RuntimeException('Brisanje nije uspjelo');
            }
            $_SESSION['deleteStatus']=true;
            return true;
        }catch(RuntimeException $e){
            array_push($_SESSION['error'],$e->getMessage());
            $_SESSION['deleteStatus']=false;
            return false;
        }
    }
}
