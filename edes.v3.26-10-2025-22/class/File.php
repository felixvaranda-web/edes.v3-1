<?PHP
class File {
private static $removeAuthorize = true;
private static $fileToSave = null;
public static function authorizeDownload( $file, $fileOnFly=false ){
if( $fileOnFly ){
$_SESSION["download-file"] = $file;
return;
}
self::setNameFile();
if( self::$removeAuthorize ){
self::$removeAuthorize = false;
@unlink( self::$fileToSave );
}
error_log("{$file}\n", 3, self::$fileToSave);
}
public static function authorized(){
list(, $fileToDownload) = preg_split('/[:&]+/u', $_SERVER["QUERY_STRING"]);
if( isset($_SESSION["download-file"]) && $_SESSION["download-file"]==$fileToDownload ){
return;
}
self::setNameFile();
$dimFile = file(self::$fileToSave, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if( !in_array($fileToDownload, $dimFile) ){
_hackerLog("Path traversal: original file [".implode(", ", $dimFile)."]");
exit;
}
}
private static function setNameFile(){
if( self::$fileToSave==null ){
self::$fileToSave = "../_tmp/php/__download.{$_ENV['_CONTEXT']}.{$_SESSION['_User']}";
}
}
}