<?php
// This is used to open a connection to database. It is a singleton.
class DB {
    private static $servername = "localhost"; // 127.0.0.1
    private static $username = "root";
    private static $password = "";
    private static $database = "kolekcija";
    private static $pdoInstance;

    private static function getPDOInstance() {
        if (self::$pdoInstance == null) {
          self::$pdoInstance = new PDO("mysql:dbname=".self::$database.";host=localhost;port=3306", self::$username, self::$password);
        }
        return self::$pdoInstance;
      }

    public static function connectDb(){
        try{
            $connection = self::getPDOInstance();
            } catch(Exception $e){
                die($e);
            }
            return $connection;
    }
}
?>