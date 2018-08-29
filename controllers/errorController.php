<?php
//Takes care of errors. Display and validation
class ErrorController{

    //Function that removes designated character from a string. 1sr argument should be a string from which a character should be removed.
    //array of characters to remove.
    private static function stripText($text,$charList){
        foreach ($charList as $char) {
            $text = str_replace($char, '', $text);
        }
        return $text;
    }

    // Will return true if ErrorMsg contains a string of alfanumeric characters.
    // In all other casses someone is messing with our page and function will return false.
    public static function validateErrorMsg($errorMsg){

        //List of accepted characters and symbols
        $charList = array(' ',',','.','!','?','-','š','đ','č','ć','ž','Š','Đ','Č','Ć','Ž');
        
        //We will remove allowed characters from the string and test it with ctype_alnum
        //which should return true if the stripped string contains only letters and numbers
        //Limitation is that it will consider croatian letters not alfanumeric so we striped them.
        $errorMsgStripped = self::stripText($errorMsg,$charList);
        if(ctype_alnum($errorMsgStripped)){
            return true;
        }else{
            return false;
        }
    }

    public static function errorHandler(){
        if(!empty($_GET['error'])){
            $errorMsg = $_GET['error'];
            if(self::validateErrorMsg($errorMsg)){
                include ("../pages/include/errorBox.php");
            }else{
                die('Nešto nije u redu sa error porukom!');
            }
        }
    }
    public static function errorHandlerSession(){
        //var_dump($_SESSION["error"]);
        if(session_status()==PHP_SESSION_NONE){
            session_start();
        }
        //echo '<pre>'.var_dump($_SESSION['requestFrom']).'</pre>';
        if(!empty($_SESSION["error"]) AND $_SESSION['requestFrom']=='inputController'){
            foreach ($_SESSION["error"] as $errorMsg) {
                if(self::validateErrorMsg($errorMsg)){
                    include ("../pages/include/errorBox.php");
                    //var_dump($errorMsg);
                }else{
                    die('Nešto nije u redu sa error porukom!');
                }
                //var_dump($errorMsg);
            }
            //Clear the error message variable after displaying all the messages.
            $_SESSION['error'] = array();
        }
        //echo "<pre>".var_dump($_SESSION["error"])."</pre";
    }
}