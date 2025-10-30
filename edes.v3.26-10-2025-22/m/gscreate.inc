<?PHP
if( !isset($_SESSION["tmp"]['gscreate']) ){
include("acount.php");
if( empty($gscreate["login"]) ){
die('ERROR: Missing define "login" in "acount.php"');
}
if( empty($gscreate["password"]) ){
die('ERROR: Missing define "password" in "acount.php"');
}
if( empty($gscreate["dateOfExpiry"]) ){
die('ERROR: Missing define "dateOfExpiry" in "acount.php"');
}
if( $gscreate["dateOfExpiry"]<date("Y-m-d") ){
die('ERROR: Data "dateOfExpiry" has expired in "acount.php"');
}
list(, $login, $pass) = explode("/", $_SERVER['QUERY_STRING']);
if( $gscreate["login"]!=$login || $gscreate["password"]!=$pass ){
die("ERROR: Wrong login or password");
}
$_User = -1; $_Tree = -1; $_Node = -1; $_Connection_ = 0; $_Web_ = '';
$_SESSION["_User"]		= $_User;
S::$_User = $_SESSION["_User"];
$_SESSION["_Node"] 		= $_Node;
$_SESSION["_Connection_"] = $_Connection_;
$_SESSION["_Tree"]		= $_Tree;
$_SESSION["_WEB_"]		= $_Web_;
$_SESSION["tmp"]['gscreate']		= true;
$__='{#}eDes{#}';
$_ENV[SYS]['context']		= md5(time());
$_SESSION["_UserLogin"]	= $login;
}
include($Dir_.'t/cr.gs');
eEnd();
if( isset($_SESSION["_CDI_"]) ){
if( $_gsTron ) eTron('Open:[t/cr.gs]');
_LoadSqlIni('_');
if( !isset($_SESSION["_WEB_"]) ){
$_User = -1; $_Tree = -1; $_Node = -1; $_Connection_ = 0; $_Web_ = '';
$_SESSION["_User"] = $_User;
$_SESSION["_Node"] = $_Node;
$_SESSION["_Connection_"] = $_Connection_;
$_SESSION["_Tree"] = $_Tree;
$_SESSION["_WEB_"] = $_Web_;
}
$__='{#}eDes{#}';
include($Dir_.'t/cr.gs');
}else{
include($Dir_.'t/cr.gs');
}
eEnd();
?>