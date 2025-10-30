<?PHP
class S {
public static function error($errorUser, $errorSys){
if( function_exists("errorManagement") ){
errorManagement($errorUser, $errorSys);
}else{
die("{$errorUser}, {$errorSys}");
}
}
}