<?PHP
botsStop();
IpsStop();
if( file_exists('../_tmp/err/stop.total') ){
$txt = rtrim(file_get_contents('../_tmp/err/stop.total'));
if( $txt=="" ) $txt = "Sistema parado por mantenimiento";
die("{$txt}");
}
if( !file_exists('../_datos/config/setup.inc') ){
}
if( $_SERVER["REQUEST_METHOD"]=="--GET" ){
$bak = "";
$db_path = '';
$_ENV[SYS]['context'] = 1;
$_SESSION["share"]['db_path'] = $db_path;
$_SESSION["SessionMaxLife"] = SETUP::$System["SessionMaxLife"];
if( $_SESSION["SessionMaxLife"]!=-1 ){
$_SESSION["SessionMaxLife"] = date("U")+(1000*1800);
}
_SaveSessionDDBB();
}else{
}
if( (isset($_POST['context'])		  		&& $_POST['context']=='closed') ||
(isset($_POST[$_SESSION["_Remember_"]]) && $_POST[$_SESSION["_Remember_"]]=='RecordarClave')
){
include($Dir_.'login_ser_web.php');
eEnd();
}
$_ENV["login"] = parse_ini_file( DIRCONFIG."login.ini" );
if( $_SERVER['QUERY_STRING']=='' && empty($_SESSION["_TMP_"]) ){
$_SESSION["context"] = 1;
$_SESSION["_TMP_"] = 1;
$_SESSION["QueryString"] = $_SERVER['QUERY_STRING'];
$_SESSION["protocolHttp"] = "http".((is_https_via_proxy()) ? "s":"");
include($Dir_.'navegador.php');
}else if( $_SERVER['QUERY_STRING']=='login1' && $_SESSION["_TMP_"]==1 ){
$_SERVER['QUERY_STRING'] = $_SESSION["QueryString"];
$_SESSION["context"] = 2;
$_SESSION["_TMP_"] = 2;
$_SESSION["QueryString"] = "";
$_SESSION["device"] = $_POST['device'];
Device::sendLogin( SETUP::$Login["RegisteredDevice"] );
include($Dir_.'login_web.php');
}else if( $_SERVER['QUERY_STRING']=='login2' && ($_SESSION["_TMP_"]==2 || $_SESSION["_TMP_"]==3) ){
$_SESSION["context"] = 3;
$_SESSION["_TMP_"] = 3;
include($Dir_.'login_ser_web.php');
}else{
eJS('try{top.S.theSessionHasExpired();}catch(e){document.write("La sessión ha expirado");}');
eSessionClose(1);
}
eEnd();
function botsStop(){
if( empty($_SERVER['HTTP_USER_AGENT']) ){
header("HTTP/1.0 404 Not Found");
http_response_code(404);
_hackerLog("ROBOTS: ".$_SERVER['HTTP_USER_AGENT']);
exit;
}
$dim = file(DIREDES."data/bots.txt");
for($i=0; $i<count($dim); $i++){
$dim[$i] = trim($dim[$i]);
}
$file = "../_datos/config/bots.txt";
if( file_exists($file) ){
$addDim = file($file);
$dim = array_merge($dim, $addDim);
}
$bots = implode("|", $dim);
if( preg_match('/^('.$bots.')/iu', $_SERVER['HTTP_USER_AGENT']) ){
header("HTTP/1.0 404 Not Found");
http_response_code(404);
_hackerLog("ROBOTS: ".$_SERVER['HTTP_USER_AGENT']);
exit;
}
}
function IpsStop(){
$file = "../_datos/config/ips_deny.txt";
if( !file_exists($file) ){
return;
}
$dim = file($file);
for($i=0; $i<count($dim); $i++){
$dim[$i] = trim($dim[$i]);
}
if( in_array($_SESSION["ip"], $dim) ){
header("HTTP/1.0 404 Not Found");
http_response_code(404);
_hackerLog("IP: ".$_SESSION["ip"]);
exit;
}
}
function denyAccess($deny=0){
if( gettype($deny)=="string" ){
include($deny);
exit;
}
if( mt_rand(1, 100) > $deny ){
return;
}
header("HTTP/1.1 403 Forbidden");
http_response_code(403);
die('Access denied');
}
function is_https_via_proxy(){
if( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ){
return true;
}
if( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ){
return true;
}
if( isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https' ){
return true;
}
return false;
}
?>