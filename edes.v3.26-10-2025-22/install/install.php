<?PHP
define('_ERROR_REPORTING', 5);
error_reporting(_ERROR_REPORTING);
ini_set('display_errors', 1);
ini_set('track_errors'  , false);
ini_set('allow_url_include', false);
ini_set('log_errors', 1);
ini_set('error_log' , '../_tmp/_err_log.txt');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
date_default_timezone_set("Europe/Madrid");
$_InstallWithUrl = "";
if( PHP_SAPI === "cli" ){
if( $argc!=2 && $argc!=3 ){
die("Faltan parámetros\n");
}
$dim = explode("/", $argv[1]);
if( count($dim)==3 || count($dim)==4 ){
$_KeyLogin = $dim[0];
$_KeyPassword  = $dim[1];
$_InstallWithUrl = $dim[2];
$_InstallPort = "";
if( !empty($dim[3]) ){
$_InstallPort = $dim[3];
}
if( !file_exists("../install/{$_InstallWithUrl}_setup.ini") ){
copy("../install/setup.ini" , "../install/{$_InstallWithUrl}_setup.ini");
copy("../install/setup.sql" , "../install/{$_InstallWithUrl}_setup.sql");
copy("../install/setup.tree", "../install/{$_InstallWithUrl}_setup.tree");
$str = <<<html
Se han generado tres archivos en la carpeta /edes.v3/install/ para la configuración de la aplicación que vas a crear:
- [folder]_setup.ini: contiene las variables de configuración de la aplicación.
- [folder]_setup.sql: define las estructuras SQL que se integrarán en la base de datos.
- [folder]_setup.tree: establece el árbol de opciones por defecto. No es necesario modificar este archivo.
Revisa y adapta los archivos de configuración a los parámetros específicos de tu intranet. Una vez hechos los cambios, ejecuta nuevamente el comando de instalación.
html;
echo $str;
exit;
}
include('../t/cr.gs');
}
}else{
$dim = explode("/", $_SERVER["QUERY_STRING"]);
if( (count($dim)==3 || count($dim)==4) && $_SERVER['REQUEST_METHOD']=="GET" ){
if( $_SERVER["QUERY_STRING"]==implode("/", $dim) ){
$_KeyLogin = $dim[0];
$_KeyPassword  = $dim[1];
$_InstallWithUrl = $dim[2];
$_InstallPort = "";
if( !empty($dim[3]) ){
$_InstallPort = $dim[3];
}
if( !file_exists("../install/{$_InstallWithUrl}_setup.ini") ){
copy("../install/setup.ini" , "../install/{$_InstallWithUrl}_setup.ini");
copy("../install/setup.sql" , "../install/{$_InstallWithUrl}_setup.sql");
copy("../install/setup.tree", "../install/{$_InstallWithUrl}_setup.tree");
include("../h/install.html");
exit;
}
include('../t/cr.gs');
}
}
}
die("Parámetros erróneos\n");
?>