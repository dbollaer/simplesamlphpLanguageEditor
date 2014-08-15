<?php
/**
 * Created by IntelliJ IDEA.
 * User: dbollaer
 * Date: 15.08.14
 * Time: 21:55
 */

if (isset( $_GET['module']) && !empty($_GET['module'])){
$module =$_GET['module'] ;
}else{

    echo "please add the module tot the get request";
    exti();
}

$definitionFile = "$module.definition.json";
$translationFile = "$module.translation.json";

if(!file_exists("$module.definition.json")) {

    echo "the $module.defintion.json does not exist";
    exit;
}


if(!file_exists("$module.translation.json")) {
    echo "the $module.translation.json translation file does not exist";
    exit;
}

if (! (is_writable("$module.definition.json") &&  is_writable("$module.translation.json") )){
 echo "this file does not have access to change it's content";   
 exit;
}


$attributes = json_decode( file_get_contents($definitionFile),true);
$attributesNl=  json_decode( file_get_contents($translationFile),true);


function file_get_contents_utf8($fn) { 
     $content = file_get_contents($fn); 
      return mb_convert_encoding($content, 'UTF-8', 
          mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)); 
} 

function getDiffs($attributes,$key,$language){
    $attributeFile = correctTranslate($attributes[$key][$language] );
    $attributePost =  correctTranslate($_POST[getK($key,$language)]);
    if(  $attributeFile != $attributePost){
        echo '<tr><td>' . $attributeFile . '</td><td> ' . $attributePost . '</td></tr>' ;
        $attributes[$key][$language] = $attributePost;
    }
    return $attributes;
}


function getK($key, $language){
    return  $language.  '_' . $key;
}


echo '<html><body>';

echo "<table>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach($attributes as $key => $value){
       $attributesNl = getDiffs($attributesNl,$key,'nl');
       $attributes = getDiffs($attributes,$key,'en');
    }
    var_dump($attributesNl);
    $attributesNl = addNewRowKey($attributesNl,'nl');
    $attributes =  addNewRowKey($attributes,'en');
    file_put_contents("$definitionFile", json_encode($attributes, JSON_PRETTY_PRINT));
    file_put_contents("$translationFile", json_encode($attributesNl, JSON_PRETTY_PRINT));
$attributes = json_decode( file_get_contents($definitionFile),true);
$attributesNl=  json_decode( file_get_contents($translationFile),true);

}
echo "</table>";
echo '<form method="post" >';

echo '<input type="submit" value="Submit"   >';
echo "<table>";
foreach($attributes as $key => $value){

    echo buildRow($key, 'en', $attributes);
    echo buildRow($key, 'nl', $attributesNl);
}
echo addNewRow('en');
echo addNewRow('nl', false);
echo "</table>";
echo '<input type="submit" value="Submit"   >';
echo '</form>';
echo '</body>';
echo '</html>';

function buildRow($key,$language, $attributes){
    return '<tr><td>'. $language.  ' ' . $key . '</td><td><input type="text" size="250" name="'. $language.  '_' . $key .'" value="'.
   correctTranslate($attributes[$key][$language]). '"></td></tr>';
    //htmlentities($attributes[$key][$language], ENT_QUOTES | ENT_IGNORE, "UTF-8") . '"></td></tr>';
}

function addNewRow($language, $showKey = true){
    $row = '<tr><td>' .$language ;
        if($showKey) {
            $row .=  ' <input type="text"  name="_newkey' . '" />';
        }
        $row .= '</td><td><input type="text" size="250" name="'. $language.  '_newvalue' . '"></td></tr>';
        return $row;
}

function addNewRowKey(&$attributes, $language){

    $key = getPostValue( '_newkey') ;
    $languageValue = getPostValue( $language.  '_newvalue') ;
    if($key && $languageValue){
      $attributes[$key][$language] =  $languageValue; 


    }
    return $attributes;
}


function getPostValue($key){
    if(isset($_POST[$key]) && !empty($_POST[$key]) ){
     return $_POST[$key];
    }
    return false;

}

function correctTranslate($value){

   return  htmlentities( mb_convert_encoding($value, 'HTML-ENTITIES', 'utf-8'));
}