<?php
//This is used for string manipulation, translating variable names to readable titles in Croatian and anything
//related to text operation
    class TranslateController{

        //This function will take a string and match it with predefined translation, then return the translated string.
        //In this case we expect the string to be a form field name. If match is not found input string will be returned
        //as is.
        public static function translateVarName($text){
            switch ($text) {
                case 'title':
                    return 'Naslov';
                    break;
                
                case 'genreID':
                    return 'Žanr';
                    break;
                
                case 'year':
                    return 'Godina';
                    break;
                
                case 'runtime':
                    return 'Trajanje';
                    break;
                
                case 'image':
                    return 'Slika';
                    break;
                
                default:
                    return $text;
                    break;
            }
        }
    }
    

?>