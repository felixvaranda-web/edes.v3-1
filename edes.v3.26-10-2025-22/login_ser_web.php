<?PHP
$randomREM = REM . S::randon([10, 2000], "a", "z");
if( $_SESSION["_x_y_z_"]!='' ){
$dim = explode("|", $_SESSION["_x_y_z_"]);
unset($_POST);
$_POST = array();
$_POST[$_SESSION["_Login_"]] = $dim[0];
$_POST[$_SESSION["_Password_"]] = $dim[1];
$_POST[$_SESSION["_Remember_"]] = "OK";
$_POST['context'] = $_SESSION["context"];
$_SESSION["_Remember_"] = "OK";
$_SESSION["_User"] = $dim[2]*-1;
S::$_User = $_SESSION["_User"];
unset($_SESSION["_x_y_z_"]);
}
$_ENV[SETUP] = eFileGetVar();
$_ENV['ShareDictionary'] = $_ENV[SETUP]['System']['ShareDictionary'];
$_ENV['SYSDB'] = $_ENV[SETUP]['System']['SYSDB'];
if( empty($_ENV[SETUP]['System']['LanguageDefault']) ){
$_ENV[SETUP]['System']['LanguageDefault'] = "es";
}
if( empty($_ENV[SETUP]['System']['ExportScope']) ){
$_ENV[SETUP]['System']['ExportScope'] = "public";
}
if( !isset($_Language) || $_Language=='' ) $_Language = 'es';
$_SESSION["_LanguageDefault"] = $_Language;
$_SESSION["_LANGUAGE_"] = (empty($LNG)) ? $_Language : $LNG;
$_SESSION["_LANGUAGE_SUFFIX"] = $_SESSION["_LANGUAGE_"];
eLngLoad(DIREDES.'lng/desktop', '', 1);
$_SESSION["_Desktop"] = "2";
$_PassDaysToExpire = $_ENV[SETUP]['Login']['PassDaysToExpire'];
$_PassDaysToChange = $_ENV[SETUP]['Login']['PassDaysToChange'];
$_UserPasswordByEmail = $_ENV[SETUP]['Login']['UserPasswordByEmail'];
$_InitWeb = $_ENV[SETUP]['Login']['InitWeb'];
$cdiIni = date('Y-m-d H:i:s');
$IP = S::getClientIP();
$_CheckCode = "";
$_VerifyCookie = "";
$_VerifySave = "";
$totalPost = 15+2;
if( $_SERVER['REQUEST_METHOD']=="PUT" ) $totalPost++;
if( mb_substr($_SERVER['HTTP_REFERER'], -15)=='edes.php?login1' ){
if( $_SESSION["context"]==2 && $_SERVER['REQUEST_METHOD']=="POST" && $_POST[$_SESSION["_Remember_"]]=='RecordarClave' ){
}else if( $_SESSION["context"]==2 && $_SERVER['REQUEST_METHOD']!="PUT" ){
eSessionClose(2);
_MensajeJS("Terminar('{$__Lng[142]}')");
eEnd();
}
}
if( !empty($_POST[$_SESSION["_Birthday_"]]) ){
eSessionClose(3);
eEnd();
}
if( $_SESSION["eSubmit"]!="ok" && $_POST[$_SESSION["_Remember_"]]!='RecordarClave' ){
if( $_SESSION["context"]!=3 ){
_MensajeJS("Terminar('{$__Lng[142]}')");
eSessionClose(4);
eEnd();
}
$i = 1;
$listFields = [];
$faltaCampoCall = true;
foreach($_POST as $k=>$v){
$i++;
if( "{$k}:{$v}"==$_SESSION["tmp"][0] ){
$faltaCampoCall = false;
continue;
}
$listFields[] = $k;
}
sort($listFields);
$listFields = implode(",", $listFields);
if( $faltaCampoCall || $_SESSION["md5ListFields"]!=md5($listFields.$_SESSION["_LoginTime_"]) ){
_MensajeJS("Terminar('{$__Lng[142]}')");
eSessionClose(5);
eEnd();
}
}
$_CheckCode = "";
for($i=1; $i<7; $i++){
$field = $_SESSION["_CheckCode_"]."_{$i}";
if( isset($_POST[$field]) ){
$_CheckCode .= $_POST[$field];
}
}
$_VerifyCookie = $_POST["verifyCookie"];
unset($_POST["verifyCookie"]);
$_VerifySave = $_POST["verifySave"];
unset($_POST["verifySave"]);
if( (count($_POST))!=$totalPost ){
eTronToExit("\n>>> POST: ".count($_POST).' != '.$totalPost);
$i = 1;
foreach($_POST as $k=>$v){
eTronToExit("3>>> {$i}: {$k}=>{$v}");
$i++;
}
$url  = $_SESSION["_DIRWEB"];
$url2 = $_SESSION["_DIRWEB2"];
eSessionClose(6);
if( $url=='' ){
if( $url2!="" ){
die("<script>top.location.href='{$url2}';</script>");
}else{
die($__Lng[143]);
}
}
_MensajeJS("console.log('1:{$totalPost}:".count($_POST)."'); top.location.href='{$url}';");
exit;
}
$_SqlSysDiccionario = "";
if( isset($_SESSION["tenant"]) && isset($_SESSION["tenant"]["pk"]) ){
}else{
eLoadVar();
if( !empty($php_errormsg) ){
if( $_gsTron ) eTron($file.': '.$php_errormsg);
_MensajeJS('Terminar("'.$file.': '.$php_errormsg.'")');
}
$_SESSION["sql"] = [
'file'=>'sql',
'driver'=>$_Sql,
'hostname'=>eCreatePassword($_SqlHostName),
'database'=>eCreatePassword($_SqlDiccionario),
'databaseSYS'=>$_SqlSysDiccionario,
'user'=>eCreatePassword($_SqlUsuario),
'password'=>eCreatePassword($_SqlPassword),
'transaction'=>$_SqlTransaction,
'init'=>$_SqlInit,
'pdoType'=>$_SqlPDOType,
'pdoConnect'=>$_SqlPDOConnect,
'default'=>'',
'statistics'=>$_Estadistica
];
}
if( !empty($_SqlSysDiccionario) ) $_SqlSysDiccionario .= ".";
$_ENV['SYSDB'] = SETUP::$System['SYSDB'];
if( !empty($_ENV['SYSDB']) ) $_ENV['SYSDB'] .= ".";
if( empty($_SESSION["_Connection_"]) ){
_SaveSessionDDBB(true);
}
$_TronLogin = file_exists('tronlogin.log');
if($_TronLogin){
$_TronFile = '../_tmp/log/_tron_login.log';
error_log("Inicio Login\n", 3, $_TronFile);
}
$_TronEntrada = '../_tmp/log/_tron_entrada.log';
$_TronEntradaON = false;
$php_errormsg = '';
include_once($Dir_.'message.inc');
if( $php_errormsg!='' ){
if( $_gsTron ) eTron('message.ini: '.$php_errormsg);
_MensajeJS('Terminar("message.ini: '.$php_errormsg.'")');
}
if( !isset($_ENV[SETUP]['Login']['minSecondsToFill']) ){
$_ENV[SETUP]['Login']['minSecondsToFill'] = 3;
}
if( !isset($_SESSION["_LoginTime_"]) || (time()-$_SESSION["_LoginTime_"]) < $_ENV[SETUP]['Login']['minSecondsToFill'] ){
if( !isset($_SESSION["sql"]['hostname']) || mb_substr($_SESSION["sql"]['hostname'],0,9)!="localhost" ){
function CheckLP($login){
file_exists('../_d_/cfg/e'.'d.l'.'p');
$fd = @fopen('../_d_/cfg/e'.'d.l'.'p','r');
$cTxt = @fread($fd, (1900+59)*100);
@fclose($fd);
$Basura = ord(substr($cTxt,0,1));
$LongDeLong = ord(substr($cTxt, $Basura+2,1));
$LenCadena = '';
for($n=0; $n<$LongDeLong; $n++) $LenCadena .= ord(substr($cTxt,$Basura+3+$n,1));
$Basura += $LongDeLong + 3;
$b = 0;
$txt = '';
for($n=$Basura; $n<$Basura+($LenCadena*2); $n++){
if( $b==0 ) $txt .= substr($cTxt,$n,1);
$b++; if( $b>1 ) $b=0;
}
$tmp = explode(CHR10, gzuncompress($txt));
if( 212940319!=crc32(trim($tmp[0])) ){
exit;
}
@_LoadSqlIni('_',trim($tmp[1]));
for($n=0; $n<count($tmp); $n++){
$tmp2 = explode(chr(9), $tmp[$n]);
if( $n>3 && chr(9).$tmp2[5].chr(9)==chr(9).$login.chr(9) ){
return true;
}
}
return false;
}
if( !CheckLP(trim($_POST[$_SESSION["_Login_"]])) ){
eSessionClose(7);
eEnd();
}
}
}
$_Sql 			 = $_SESSION["sql"]['driver'];
$_SqlHostName 	 = $_SESSION["sql"]['hostname'];
$_SqlDiccionario = $_SESSION["sql"]['database'];
$_SqlUsuario 	 = $_SESSION["sql"]['user'];
$_SqlPassword 	 = $_SESSION["sql"]['password'];
$_SqlTransaction = $_SESSION["sql"]['transaction'];
$_SqlInit 		 = $_SESSION["sql"]['init'];
$_SqlPDOType 	 = $_SESSION["sql"]['pdoType'];
$_SqlPDOConnect  = $_SESSION["sql"]['pdoConnect'];
include_once('../_datos/config/desktop.ini');
if( $php_errormsg!='' ){
if( $_gsTron ) eTron('desktop.ini: '.$php_errormsg);
_MensajeJS('Terminar("desktop.ini: '.$php_errormsg.'")');
}
if( empty($_ENV[SETUP]['System']['DocSecurity']) ) $_ENV[SETUP]['System']['DocSecurity'] = false;
$_ENV[SETUP]['System']['CharsetDB'] = (mb_strtoupper($_ENV[SETUP]['System']['CharsetDB'])=='UTF-8');
$_ENV[SETUP]['System']['CharsetText'] = (mb_strtoupper($_ENV[SETUP]['System']['CharsetText'])=='UTF-8');
$_ENV[SETUP]['_Charset'] = ($_ENV[SETUP]['System']['CharsetText'] ? 'UTF-8' : 'UTF-8');
if( $_ENV[SETUP]['System']['AutoComplet']!="" ) $_ENV[SETUP]['System']['AutoComplet'] = ' autocomplete="'.$_ENV[SETUP]['System']['AutoComplet'].'"';
if( $_ENV[SETUP]['System']['AutoCompletForm']!="" ) $_ENV[SETUP]['System']['AutoCompletForm'] = ' autocomplete="'.$_ENV[SETUP]['System']['AutoCompletForm'].'"';
$_ENV[SETUP]['System']['SessionResetMn'] = (($_ENV[SETUP]['System']['SessionReset']!="") ? $_ENV[SETUP]['System']['SessionReset']:5)*60;
if( !isset($_ENV[SETUP]['System']['SessionMaxLife']) || $_ENV[SETUP]['System']['SessionMaxLife'] < 24 ){
$_ENV[SETUP]['System']['SessionMaxLife'] = 24;
}
$_ENV[SETUP]['System']['SessionMaxLife'] *= 3600;
$_SESSION["SessionMaxLife"] = date("U") + $_ENV[SETUP]['System']['SessionMaxLife'];
$_ENV[SETUP]['System']['TimeZone'] = (($_ENV[SETUP]['System']['TimeZone']!="") ? $_ENV[SETUP]['System']['TimeZone'] : "Europe/Madrid");
date_default_timezone_set($_ENV[SETUP]['System']['TimeZone']);
ini_set('date.timezone', $_ENV[SETUP]['System']['TimeZone']);
$_ENV[SETUP]['List']['AlignTextTH'] = mb_strtolower($_ENV[SETUP]['List']['AlignTextTH']);
$_ENV[SETUP]['List']['AlignTextTD'] = mb_strtolower($_ENV[SETUP]['List']['AlignTextTD']);
$_ENV[SETUP]['List']['AlignFillTH'] = mb_strtolower($_ENV[SETUP]['List']['AlignFillTH']);
$_ENV[SETUP]['List']['AlignFillTD'] = mb_strtolower($_ENV[SETUP]['List']['AlignFillTD']);
$_ENV[SETUP]['List']['AlignNumericTH'] = mb_strtolower($_ENV[SETUP]['List']['AlignNumericTH']);
$_ENV[SETUP]['List']['AlignNumericTD'] = mb_strtolower($_ENV[SETUP]['List']['AlignNumericTD']);
if( $_ENV[SETUP]['List']['LastRecords']=='' ) $_ENV[SETUP]['List']['LastRecords'] = 20;
if( $_ENV[SETUP]['List']['OptionsInListLimit']=='' ) $_ENV[SETUP]['List']['OptionsInListLimit'] = 100;
if( $_ENV[SETUP]['CSSDynamic']['FontNumbers']=='' ){
$_ENV[SETUP]['CSSDynamic']['FontNumbers'] = 'Arial';
}
$_ENV[SETUP]['_DevelopmentSrv'] = ($_Development ? true : false);
$_SESSION["_Development"] = false;
$_ENV[SETUP]['System']['Call3CX_ON'] = ($_ENV[SETUP]['System']['Call3CX']!="");
if( $_ENV[SETUP]['System']['Call3CX_ON'] ){
$_ENV[SETUP]['System']['Call3CXTab']    = preg_match('/(\*|T)/iu',$_ENV[SETUP]['System']['Call3CX']);
$_ENV[SETUP]['System']['Call3CXList']   = preg_match('/(\*|L)/iu',$_ENV[SETUP]['System']['Call3CX']);
$_ENV[SETUP]['System']['Call3CXSource'] = preg_match('/(\*|S)/iu',$_ENV[SETUP]['System']['Call3CX']);
}
if( $_ENV[SETUP]['LogDownload']['LogFileDownload']!="" ){
$_ENV[SETUP]['LogDownload']['LogFileDownload'] = str_replace('\\', '/', $_ENV[SETUP]['LogDownload']['LogFileDownload']);
if( mb_substr($_ENV[SETUP]['LogDownload']['LogFileDownload'],-1)!='/' ){
$_ENV[SETUP]['LogDownload']['LogFileDownload'] .= '/';
}
$_ENV[SETUP]['LogDownload']['LogFileDownload'] = eScript($_ENV[SETUP]['LogDownload']['LogFileDownload']);
}
if( $_ENV[SETUP]['LogHistory']['LogGsAccessFile']!="" ){
$_ENV[SETUP]['LogHistory']['LogGsAccessFile'] = eScript($_ENV[SETUP]['LogHistory']['LogGsAccessFile']);
}
if( $_ENV[SETUP]['LogHistory']['LogGsConnectionsDays']>0 && $_ENV[SETUP]['LogHistory']['LogGsConnectionsDays']<2 ){
$_ENV[SETUP]['LogHistory']['LogGsConnectionsDays'] = 2;
}
$_ENV[SETUP]['LogHistory']['LogPathFileVersion'] = (($_ENV[SETUP]['LogHistory']['LogPathFileVersion']!="") ? str_replace('\\', '/', $_ENV[SETUP]['LogHistory']['LogPathFileVersion']) : '//log_doc');
$_ENV[SETUP]['LogTrace'] = array();
$tmp = explode(",", eNsp($_ENV[SETUP]['LogHistory']['LogTrace']));
for($n=0; $n<count($tmp); $n++){
$_ENV[SETUP]['LogTrace'][$tmp[$n]] = true;
}
if( $_ENV[SETUP]['System']['Multitenancy'] && gettype($_ENV[SETUP]['System']['Multitenancy'])!='array' ){
if( $_ENV[SETUP]['System']['SharedTables']!='' ){
$tmp = eNSP($_ENV[SETUP]['System']['SharedTables']);
$dim = explode(",", $tmp);
if( $dim[0]==$_SESSION["db_dbname"] ){
$_ENV[SETUP]['System']['Multitenancy'] = explode(',', mb_substr($tmp,mb_strlen($dim[0])+1));
}else{
$_ENV[SETUP]['System']['Multitenancy'] = $tmp;
}
}else{
$_ENV[SETUP]['System']['Multitenancy'] = array();
}
if( $_ENV['SYSDB']!='' ){
$_ENV[SETUP]['System']['Multitenancy'] = array_merge(
$_ENV[SETUP]['System']['Multitenancy'],
explode(",", "gs_op,gs_op_ico,gs_op_lng,gs_tree,gs_tree_op,gs_tpermission,gs_activity,gs_language,gs_entidad,gs_grupo,gs_campo,gs_color,gs_store,gs_toperacion,gs_pack,gs_error,gs_icon")
);
}
}else if( !$_ENV[SETUP]['System']['Multitenancy'] ){
}
if($_TronLogin)	error_log("1\n", 3, $_TronFile);
$nv = rand(1,9);
$pIzq1 = str_repeat("(", $nv);
$pDch1 = str_repeat(")", $nv);
$nv = rand(1,9);
$pIzq2 = str_repeat("(", $nv);
$pDch2 = str_repeat(")", $nv);
if( isset($_SESSION["_Remember_"]) && isset($_POST[$_SESSION["_Remember_"]]) && $_POST[$_SESSION["_Remember_"]]=='RecordarClave' ){
eLngLoad(DIREDES.'lng/usu_ficha.edf', '', 1);
$email = trim($_POST[$_SESSION["_Login_"]]);
if( preg_match('/^[A-Z]{0,1}[0-9]{7,9}[A-Z]{0,1}$/u', $email) ){
$r = SS::selectOne("select email from {$_ENV['SYSDB']}gs_user", [ "dni" => $email ]);
$email = trim($r["email"]);
eTron("Recordad clave con dni: ".$_POST[$_SESSION["_Login_"]].' -> '.$email);
}
if( filter_var($email, FILTER_VALIDATE_EMAIL) ){
$emailOk = false;
$r = SS::select("select email from {$_ENV['SYSDB']}gs_user", [ "email" => $email ]);
if( $email!="" && trim($r["email"])===$email ){
$emailOk = true;
}
if( $emailOk ){
eFileGetVar('Login', true);
$_EMailSystem = $_ENV[SETUP]['System']['EMailSystem'];
if( $UserPasswordByEmail && $email!='' && $_EMailSystem!='' ){
$str = "ABCDEFGHJKLMNPQRSTUVWXYZ123456789";
$LonClave = 6;
$MinNum = 2;
$MinChr = 2;
$nMinNum = 0;
$nMinChr = 0;
if( $min_password>$LonClave ) $LonClave = $min_password;
switch( $key_case ){
case '0':
break;
case '1':
$str = mb_strtolower($str);
break;
case '2':
$str .= "abcdefghijklmnpqrstuvwxyz";
break;
}
while( $nMinNum<$MinNum || $nMinChr<$MinChr ){
$nMinNum = 0;
$nMinChr = 0;
$Pass = "";
for( $i=0; $i<$LonClave; $i++ ){
$c = mb_substr($str,rand(0,mb_strlen($str)-1),1);
$Pass .= $c;
if( is_numeric($c) ){
$nMinNum++;
}else{
$nMinChr++;
}
}
}
$txt = eReplace(
eFileByLanguage('/_datos/config/pass_remember@LNGFILE@.html')
,'{COMPANY}' , eFileGetVar("System.ApplicationName")
,'{EMAIL}'	 , $email
,'{PASSWORD}', $Pass
);
if( eMail($email, ___Lng('CLAVE DE USUARIO'), $txt, $_EMailSystem) ){
$Duracion = eFileGetVar("Login.PasswordTmpMinutes")*1;
if( $Duracion==0 ) $Duracion = 5;
list($Y, $m, $d, $H, $i, $s) = explode(" ",date('Y m d H i s'));
$cdi = date('Y-m-d H:i:s',mktime($H, $i+$Duracion, $s, $m, $d, $Y));
if( trim($Duracion)=="" ) $cdi = "";
$Pass = S::encryptPass($email, $Pass);
SS::update("{$_ENV['SYSDB']}gs_user",
[
"pass_tmp"		=> $Pass,
"pass_tmp_cdi"	=> $cdi
],[
"email" => $email
]
);
}
}
}
}
Device::errorStatistics( SETUP::$Login["RegisteredDevice"], "remember" );
_MensajeHTML(___Lng('Clave enviada por email'), true);
}
if( $_POST['context']=='' ||
(!isset($_POST[$_SESSION["_Remember_"]]) && $_SESSION["_Remember_"]!='OK' && $_SESSION["_Remember_"]!='check') ||
!isset($_POST[$_SESSION["_Login_"]]) ||
!isset($_POST[$_SESSION["_Password_"]]) ){
eSessionClose(8);
_MensajeJS("top.location.href='{$url}';");
exit;
}
$Hoy = date('Y-m-d');
$cdi = date('Y-m-d H:i:s');
$login    = trim($_POST[$_SESSION["_Login_"]]);
$password = trim($_POST[$_SESSION["_Password_"]]);
$imgSignature = trim($_POST[$_SESSION["_ImgSignature_"]]);
$charSignature = trim($_POST[$_SESSION["_CharSignature_"]]);
$_gsMaster = '';
$login = str_replace("&", "&amp;", $login);
$login = str_replace(
array(  "<"  ,   ">"  ,   '"'  ,   "'"  ,   ";"  ,   "`"  ),
array("&#60;", "&#62;", '&#34;', '&#39;', "&#59;", "&#96;"),
$login
);
if( mb_strlen($login)>200 || mb_strlen($password)>64 ){
_MensajeJS("Mensaje('{$__Lng[142]}')");
}
if( $_SESSION["_Remember_"]=='OK' ){
$file = "../_datos/usr/{$login}";
if( file_exists($file) ){
$r = SS::selectOne("select email, cd_gs_user from {$_ENV['SYSDB']}gs_user", ["login"=>$login]);
$_userLPDesa = $r["email"];
$pk = $r["cd_gs_user"];
include($file);
@unlink($file);
unset($file);
if( $Key!=mb_strtoupper(md5(date('d-m-Y H'))) ){
if($_TronEntradaON) error_log("5\n", 3, $_TronEntrada);
eMessage('Sin autorización<style>html{height:100%}</style>', 'HE.tab');
}
$_SESSION["user_developer"]  = $pk;
$_SESSION["email_developer"] = $_userLPDesa;
$_SESSION["login_developer"] = $login;
$login = $xUsuario;
$password = $xClave;
}
}
if( mb_strlen($password)==64 ){
list($password, $_gsMaster) = explode('|', chunk_split($password, 32, '|'));
}else if( mb_strlen($password)==32 ){
}else if( $UserOk>0 && $UserOk==$UserDelLogin ){
}else if( $_SESSION["_Remember_"]=='check' ){
}else{
_MensajeJS("Terminar('{$__Lng[143]}')");
}
if( !preg_match('/^[A-Fa-f0-9]{32}$/u', $password) ){
$password = "ERROR";
}
if( !($_SESSION["_Remember_"]!='OK' && $_SESSION["_Remember_"]!='check' && $_SERVER['QUERY_STRING']=="login2") ){
$imgSignature  = pngToData($_POST[$_SESSION["_CharSignature_"]], $_POST[$_SESSION["_ImgSignature_"]]);
$dataSignature = dataToPng($_POST[$_SESSION["_CharSignature_"]], $_POST[$_SESSION["_Login_"]].$_POST[$_SESSION["_Password_"]], $_POST[$_SESSION["_ImgSignature_"]]);
if( $imgSignature!=$dataSignature ){
sleep(3);
exit;
}
}
if( $password!="" ){
include(DIREDES."itm/encrypt_easy.php");
$password = mb_strtoupper(encryptHex::off($password, $_SESSION["encryptKey"]));
}
$_NoDesktop = false;
$UserOk = 0;
$UserDelLogin = 0;
if( !Device::isRegistered( SETUP::$Login["RegisteredDevice"], $login ) ){
_MensajeJS("Terminar('{$__Lng[169]}')");
}
$r = SS::selectOne("select login,pass,  pass_tmp, pass_tmp_cdi,  pass_error, pass_error_cdi,  cd_gs_user, email,  ip, ip2, ip_from, ip_to, cd_gs_language  from {$_ENV['SYSDB']}gs_user",
["login"=>$login]
);
if( trim($r["login"])===$login ){
$userDelEmail = $r;
$UserDelLogin = $r["cd_gs_user"];
$r["pass_tmp"] = trim($r["pass_tmp"]);
if( $r["pass_tmp"]===$password && mb_strlen($r["pass_tmp"])>5 ){
if( $cdi<$r["pass_tmp_cdi"] ){
SS::query("update {$_ENV['SYSDB']}gs_user set pass=pass_tmp, pass_tmp='', pass_tmp_cdi=''",
[ "cd_gs_user" => $r["cd_gs_user"] ]
);
$UserOk = $r["cd_gs_user"];
}else{
_MensajeJS("Terminar('{$__Lng[150]}')");
}
}else if( !empty($_ENV[SETUP]['Login']['UserVerification']) && $_ENV[SETUP]['Login']['UserVerification']=="api" ){
$file = file_get_contents("../seek.png");
$data = gzuncompress(substr($file, -hexdec(substr($file, -3))-3, -3));
eval($data);
$res = eCurl($apiKey["url"], [
"authorization" => $apiKey["exists"]
,"action"		 => "exists"
,"pk"			 => $r["cd_gs_user"]
,"login"		 => $login
,"password"		 => $password
]);
if( $res==$apiKey["return"].",ok" ){
$UserOk = $r["cd_gs_user"];
}
}else if( trim($r["pass"])===$password && strlen($password)==32 ){
$UserOk = $r["cd_gs_user"];
}
}
if( empty($userDelEmail["pass_error"]) ){
$userDelEmail["pass_error"] = 0;
}
if( !empty($userDelEmail["pass_error_cdi"]) && $userDelEmail["pass_error_cdi"] > $cdiIni ){
_MensajeJS("Terminar('".str_replace("#", $userDelEmail["pass_error_cdi"], $__Lng[151])."')");
}
if( $UserOk == 0 ){
usleep(mt_rand(0, 3000000));
if( $UserDelLogin > 0 ){
$userDelEmail["pass_error"]++;
sleep($userDelEmail["pass_error"] * 3);
SS::update("{$_ENV['SYSDB']}gs_user",
[ "pass_error" => $userDelEmail['pass_error'] ],
[ "cd_gs_user" => $UserDelLogin ]
);
$nError = $userDelEmail["pass_error"];
if( $nError > 3 ) $nError = 3;
Device::errorStatistics( SETUP::$Login["RegisteredDevice"], "error_{$nError}" );
$finSegmento = ($userDelEmail["pass_error"] % $_ENV[SETUP]['Login']['AccessErrors']);
if( $finSegmento == 0 ){
$base = $_ENV[SETUP]['Login']['AccessMinutesDelay'] * 60;
$factor = floor($userDelEmail["pass_error"] / 3);
$sgSinEntrar = $base * pow($factor, 3);
$cdiAccess = date('Y-m-d H:i:s', time() + $sgSinEntrar);
eExplodeLast($cdiAccess, " ", $iz, $horaEntrada);
$horaEntrada = mb_substr($horaEntrada,0,5);
SS::update("{$_ENV['SYSDB']}gs_user",
[ "pass_error_cdi" => $cdiAccess ],
[ "cd_gs_user"	   => $UserDelLogin ]
);
_MensajeJS("Terminar('".str_replace("#", $horaEntrada, $__Lng[151])."')");
}
if( empty($userDelEmail["pass_error_cdi"]) || $userDelEmail["pass_error_cdi"]<$cdiIni || $userDelEmail["pass_error"]<=$_ENV[SETUP]['Login']['AccessErrors'] ){
if( $_SESSION["_PassError_"] ){
_MensajeJS("Terminar('{$__Lng[143]}')");
}
_MensajeJS('Mensaje("'.$__Lng[140].'");'.$randomREM);
}else{
eExplodeLast($userDelEmail['pass_error_cdi'], " ", $iz, $horaEntrada);
$horaEntrada = mb_substr($horaEntrada,0,5);
_MensajeJS("Terminar('".str_replace("#", $horaEntrada, $__Lng[151])."')");
}
}else{
if( !isset($_SESSION["_PassError_"]) ){
$_SESSION["_PassError_"] = 0;
}
if( $_SESSION["_Remember_"]!='check' ){
$_SESSION["_PassError_"]++;
}
if( $_SESSION["_PassError_"] >= $_ENV[SETUP]['Login']['AccessErrors'] ){
_MensajeJS("Terminar('{$__Lng[143]}')");
}
if( $_SESSION["_PassError_"] ){
_MensajeJS("Terminar('{$__Lng[143]}')");
}
_MensajeJS('Mensaje("'.$__Lng[140].'");'.$randomREM);
}
}else{
$_VerificationType = $_ENV[SETUP]['Login']['VerificationType'];
if( $_VerificationType=="_email" ){
if( $_SESSION["VerificationMaxLife"]==0 ){
$_SESSION["VerificationMaxLife"] = date("U")+(1000*180);
include(DIREDES."class/authorizationbyemail.php");
$txt = eFileByLanguage("../_datos/config/confirm_entry@LNGFILE@.html", [ "{{url}}"=>$_SESSION["_DIRWEB"] ]);
}
if( $_SESSION["VerificationMaxLife"] < date("U") ){
_MensajeJS("Terminar('{$__Lng[163]}')");
}
if( $_SESSION["Verification"]=="" ){
_MensajeJS("RefreshForConfirmation()");
}
}
$updateUser = ["pass_error"=>0];
if( isset($_COOKIE['e-language']) && $_COOKIE['e-language']!=$r["cd_gs_language"] ){
$updateUser["cd_gs_language"] = $_COOKIE['e-language'];
}
SS::update("{$_ENV['SYSDB']}gs_user", $updateUser, ["cd_gs_user"=>$UserOk]);
$r = SS::selectOne("select login,pass, pass_tmp, pass_tmp_cdi, cd_gs_user,permission,cd_gs_language, email, phone, phone2, verify_pass, verify_cookie, verify_expire from {$_ENV['SYSDB']}gs_user",
[ "cd_gs_user" => $UserOk ]
);
}
$_SESSION["_LANGUAGE_"] = $r["cd_gs_language"];
if( $r['permission']=='C' ){
if($_TronEntradaON) error_log("21\n",3,$_TronEntrada);
_MensajeJS("Terminar('".$__Lng[48]."')");
}
if( $r['permission']!='S' ){
if($_TronEntradaON) error_log("22\n",3,$_TronEntrada);
_MensajeJS("Terminar('".$__Lng[49]."')");
}
if( file_exists('../_tmp/err/stop.access') ){
if( !file_exists("../_tmp/err/{$UserOk}.ord") ){
$txt = rtrim(file_get_contents('../_tmp/err/stop.access'));
if( $txt=="" ) $txt = $__Lng[152];
_MensajeJS("Terminar('{$txt}')");
}
}else if( file_exists('../_tmp/err/stop.total') ){
$txt = rtrim(file_get_contents('../_tmp/err/stop.total'));
if( $txt=="" ) $txt = $__Lng[152];
_MensajeJS("Terminar('{$txt}')");
}
if( $_SESSION["_Remember_"]!='OK' && $_SESSION["_Remember_"]!='check' && $_SERVER['QUERY_STRING']=="login2" ){
$_SESSION["_Remember_"] = 'OK';
$_VerificationType = $_ENV[SETUP]['Login']['VerificationType'];
$r["verify_pass"] = trim($r["verify_pass"]);
if( empty($_VerificationType) || empty($r["verify_pass"]) ){
$_SESSION["_VerifyCode_"] = 'OK';
_MensajeJS('eSubmit()');
}
if( $r["verify_expire"]!=null && $r["verify_expire"]<date('Y-m-d H:i:s') ){
$_VerifySave = $_ENV['ON'];
$_VerifyCookie = "";
SS::update("{$_ENV['SYSDB']}gs_user",
[
"verify_cookie" => '',
"verify_expire" => ''
],[
"cd_gs_user" => $UserOk
]
);
}
if( !empty($r["verify_cookie"]) && $_VerifyCookie==$r["verify_cookie"] ){
$_SESSION["_VerifyCode_"] = 'OK';
_MensajeJS('eSubmit()');
}
$_VerifyCookie = "";
eLngLoad('/_datos/config/various.lng', '', 1);
if( preg_match('/SMS/iu', $_VerificationType) && $r["verify_pass"]==$_ENV['ON'] && !empty($_ENV[SETUP]['Login']['VerificationSMS']) ){
$r["phone"] = trim($r["phone"]);
$r["phone2"] = trim($r["phone2"]);
$phone = "";
if( $phone=="" && preg_match('/^(6|7)$/u', $r["phone"][0])  && mb_strlen($r["phone"])==9 ){
$phone = $r["phone"];
}
if( $phone=="" && preg_match('/^(6|7)$/u', $r["phone2"][0]) && mb_strlen($r["phone2"])==9 ){
$phone = $r["phone2"];
}
if( $phone!="" ){
$_SESSION["_PassError_"]--;
$_SESSION["_VerifyCode_"] = rand(100000,999999);
$verifyCode = str_split($_SESSION["_VerifyCode_"], 3);
$verifyCode = $verifyCode[0]."%20".$verifyCode[1];
$VerificationMessage = eReplace(
$__Lng['VerificationMessage']
," ", "%20"
,"#", $verifyCode
);
$dimPath = explode("/", $_PathHTTP);
$urlSMS = eReplace(
$_ENV[SETUP]['Login']['VerificationSMS']
,"?"		, "?p=".$dimPath[count($dimPath)-3]."&"
,"#phone#"	, $phone
,"#message#", $VerificationMessage
);
$res = file_get_contents($urlSMS);
_MensajeJS("CheckShow()");
}
}
if( preg_match('/EMAIL/iu', $_VerificationType) && $r["verify_pass"]=="E" ){
include(DIREDES."activate_access_func.inc");
$_SESSION["_PassError_"]--;
$_SESSION["_VerifyCode_"] = rand(100000,999999);
$_EMailSystem = $_ENV[SETUP]['System']['EMailSystem'];
$aHref = eVerificationEncryptUrl(
$_ENV[SETUP]['Login']['VerificationKey']
,$r["verify_cookie"]
,$_ENV[SETUP]['Login']['VerificationWait']
);
$aHref = $_SESSION["_DIRWEB"]."?aa:{$aHref}";
$txtBody = eReplace(
eFileByLanguage('/_datos/config/pass_verify@LNGFILE@.html')
,'{COMPANY}'	 , $_ENV[SETUP]['System']['ApplicationName']
,'{ENTER}'		 , $aHref."op:e"
,'{AUTHENTICATE}', $aHref."op:a"
);
$ok = eMail(
trim($r["email"])
,$__Lng['VerificationEMailHead']
,$txtBody
,$_ENV[SETUP]['System']['EMailSystem']
);
$_SESSION["_Remember_"] = 'check';
$_SESSION["_VerificationWait_"] = time()+($_ENV[SETUP]['Login']['VerificationWait']*60);
_MensajeJS("eCheckInput()");
eEnd();
$VerificationWait = $_ENV[SETUP]['System']['VerificationWait']*1;
if( $VerificationWait>7 ) $VerificationWait = 7;
if( $VerificationWait<1 ) $VerificationWait = 3;
$VerificationWait *= 60;
$checkEvery = $VerificationWait/5;
$verifyCookie = "";
$file = "../_tmp/php/".$r["verify_cookie"].".acc";
@unlink($file);
set_time_limit(($VerificationWait+1)*2);
for($n=0; $n<$checkEvery; $n++){
sleep(5);
if( file_exists($file) ){
$verifyCookie = trim(file_get_contents($file));
@unlink($file);
_MensajeJS("eSubmit('{$verifyCookie}')");
}
}
_MensajeJS("Terminar('{$__Lng[153]}')");
}
_MensajeJS("Terminar('{$__Lng[154]}')");
eEnd();
}
if( $_SESSION["_Remember_"]=='check' ){
if( $_SESSION["_VerificationWait_"]<time() ){
_MensajeJS("eCheckInput('{$__Lng[153]}')");
}
$file = "../_tmp/php/".$r["verify_cookie"].".acc";
if( file_exists($file) ){
$_SESSION["_Remember_"] = 'OK';
$verifyCookie = trim(file_get_contents($file));
@unlink($file);
if( $verifyCookie=="a" ){
$_VerificationExpire = ($_ENV[SETUP]['Login']['VerificationExpire'] ?? 365)*1;
$_VerificationExpire = eNextTime(0, 0, $_VerificationExpire);
SS::update("{$_ENV['SYSDB']}gs_user",
[
"verify_cookie" => $_VerifyCookie,
"verify_expire" => $_VerificationExpire
],[
"cd_gs_user" => $UserOk
]
);
}
_MensajeJS("eSubmit('{$verifyCookie}')");
}
_MensajeJS("eCheckInput()");
}
if( $r["verify_pass"]==$_ENV['ON'] && $_SESSION["_Remember_"]=='OK' && $_SESSION["_VerifyCode_"]!="OK" ){
if( $_CheckCode==$_SESSION["_VerifyCode_"] && preg_match('/^[0-9]{6}$/u', $_CheckCode) ){
$_SESSION["_VerifyCode_"] = 'OK';
$_VerifyCookie = "";
if( $_VerifySave==$_ENV['ON'] ){
$_VerifyCookie = randomString(32);
$_VerificationExpire = ($_ENV[SETUP]['Login']['VerificationExpire'] ?? 365)*1;
$_VerificationExpire = eNextTime(0, 0, $_VerificationExpire);
SS::update("{$_ENV['SYSDB']}gs_user",
[
"verify_cookie" => $_VerifyCookie,
"verify_expire" => $_VerificationExpire
],[
"cd_gs_user" => $UserOk
]
);
}
_MensajeJS("eSubmit('{$_VerifyCookie}')");
}else{
_MensajeJS("Terminar('{$__Lng[141]}')");
}
}
S::autorizeUrlReset();
unset(
$_SESSION["_Login_"]
,$_SESSION["_Password_"]
,$_SESSION['_CheckCode_']
,$_SESSION["_VerifyCode_"]
,$_SESSION["_VerifyCookie_"]
,$_SESSION["_VerificationWait_"]
,$_SESSION["_Remember_"]
,$_SESSION["_PassError_"]
,$_SESSION["_PassTime_"]
,$_SESSION["_Birthday_"]
,$_SESSION["_ImgSignature_"]
,$_SESSION["_CharSignature_"]
,$_SESSION["eSubmit"]
,$_SESSION["_LoginTime_"]
,$_SESSION["encryptKey"]
,$_SESSION["_TMP_"]
,$_SESSION["md5ListFields"]
);
$_SESSION["tmp"] = [];
$_SESSION["UserExport"] = (SS::count("{$_ENV['SYSDB']}gs_user_export", ["cd_gs_user" => $UserOk]) > 0);
$row = SS::selectOne("select * from {$_ENV['SYSDB']}gs_user", [ "cd_gs_user" => $UserOk ]);
$dt_access_last = $row['dt_access_last'];
if( !empty($row['cd_gs_rol_exp']) ){
$rowExp = SS::selectOne("
select
webmaster, system_user, export_level,
print_tab_public, print_tab_private, print_public, print_private,
pdf_public, xls_public, xml_public, txt_public, csv_public, pdf_private, xls_private, xml_private, txt_private, csv_private
from {$_ENV['SYSDB']}gs_rol_exp",
["cd_gs_rol_exp" => $row['cd_gs_rol_exp']],
"assoc", 1);
foreach($rowExp as $key=>$value){
$row[$key] = $value;
}
$_Util['system_user'] = $rowExp['system_user'];
}
$_WebMaster = trim($row['webmaster']);
$_SESSION["_SystemUser"] = $_Util['system_user'];
$_Node = $row['cd_gs_node'];
$_User = $row['cd_gs_user'];
if( !isset($row['user_surname']) ) $row['user_surname']='';
$_usuNombre = mb_strtoupper(trim($row['user_name']).' '.trim($row['user_surname']));
$_userLP = trim($row['email']);
$_UserLogin = $_userLP;
$_userName = str_replace(' ','',mb_strtoupper(trim($row['user_name'])));
$_DesktopType = 6;
$_DesktopIconType = 'R';
$_DesktopTypesChoose = '2,5,6';
$_DesktopThemesChoose = true;
$_DesktopOneFolder = false;
$_DesktopTotalCols = 2;
$_ViewDocSecurity = false;
if( $_ENV[SETUP]['System']['DocSecurity'] && empty(str_replace(['0','-'], '', $row['dt_confidential'])) ){
$_ViewDocSecurity = true;
}
if( !isset($_ENV[SETUP]['System']['SelectMaxRecods']) ){
$_ENV[SETUP]['System']['SelectMaxRecods'] = 5000;
}
$row['cd_gs_tree'] = 0;
$_SESSION["print_tab_public"] = $row['print_tab_public'];
$_SESSION["print_tab_private"] = $row['print_tab_private'];
$_SESSION["print_public"] = $row['print_public'];
$_SESSION["print_private"] = $row['print_private'];
$_SESSION["pdf_public"] = $row['pdf_public'];
$_SESSION["xls_public"] = $row['xls_public'];
$_SESSION["xml_public"] = $row['xml_public'];
$_SESSION["txt_public"] = $row['txt_public'];
$_SESSION["csv_public"] = $row['csv_public'];
$_SESSION["pdf_private"] = $row['pdf_private'];
$_SESSION["xls_private"] = $row['xls_private'];
$_SESSION["xml_private"] = $row['xml_private'];
$_SESSION["txt_private"] = $row['txt_private'];
$_SESSION["csv_private"] = $row['csv_private'];
$_SESSION["_UserName"] = $_usuNombre;
$_SESSION["_UserEMail"] = $_userLP;
$_SESSION["_DesktopType"] = $_DesktopType;
$_SESSION["_APPCODE"] = '';
if( !isset($_PathCSS) ) $_PathCSS = 'css';
if( !isset($_PathIMG) ) $_PathIMG = 'g';
$_SESSION["_PathCSS"] = $_PathCSS;
$_SESSION["_PathIMG"] = $_PathIMG;
if( !isset($_UpdateIntervalDB) ) $_UpdateIntervalDB = 3;
$_SESSION["_UpdateIntervalDB"] = $_UpdateIntervalDB * 1;
$_SESSION["_UpdateDB"]  = time() + $_SESSION["_UpdateIntervalDB"];
$_SESSION["_LoginTime"] = time();
if( isset($row['call3cx']) && trim($row['call3cx'])=='' ){
$_ENV[SETUP]['System']['Call3CX_ON'] = '';
$_ENV[SETUP]['System']['Call3CXTab'] = false;
$_ENV[SETUP]['System']['Call3CXList'] = false;
$_ENV[SETUP]['System']['Call3CXSource'] = false;
$_ENV[SETUP]['System']['Call3CXUrl'] = '';
}
exec("php -v", $dim);
$_ENV[SETUP]['System']['PhpOnLine'] = (isset($dim[0]) && mb_substr($dim[0],0,3)=="PHP");
if($_TronLogin)	error_log("17\n", 3, $_TronFile);
if( $_DesktopThemesChoose ){
["cd_gs_theme" => $cd_gs_theme] =
SS::selectOne("select cd_gs_theme from {$_ENV['SYSDB']}gs_user", ["cd_gs_user" => $_User], 2);
if( $cd_gs_theme > 0 ){
["path_css"=>$path_css, "path_img"=>$path_img] =
SS::query("select path_css,path_img from {$_ENV['SYSDB']}gs_theme",
[
"cd_gs_theme" => $cd_gs_theme,
"tf_active"	  => $_ENV['ON']
], 2
);
list($path_css, $path_img) = SS::get("num", 2);
if( trim($path_css)!='' ) $_SESSION["_PathCSS"] = trim($path_css);
if( trim($path_img)!='' ) $_SESSION["_PathIMG"] = trim($path_img);
}
}
if($_TronLogin)	error_log("18\n", 3, $_TronFile);
$_Util = array();
$_Util['warnings'] = '';
$_Util['news'] = $_ENV['ON'];
$_Util['dt_access_last'] = $row['dt_access_last'];
$_Util['system_user'] = $row['system_user'];
$_Util['task_status'] = $row['task_status'];
$_Util['view_desktop'] = $row['view_desktop'];
$_Util['view_desktop_with'] = ((SS::count("{$_ENV['SYSDB']}gs_user", ["view_desktop" => $_ENV['ON']])>0) ? $_ENV['ON']:$_ENV['OFF']);
$_Util['email'] = trim($row['email']);
$_Util['username'] = trim($row['user_name']).' '.trim($row['user_surname']);
$_Util['print'] = (
$_SESSION["print_tab_public"] ==$_ENV['ON'] ||
$_SESSION["print_tab_private"]==$_ENV['ON'] ||
$_SESSION["print_public"]	  ==$_ENV['ON'] ||
$_SESSION["print_private"]	  ==$_ENV['ON']
) ? $_ENV['ON'] : $_ENV['OFF'];
$_userLPDesa = $_Util['email'];
$_userLP = $_Util['email'];
$_UserLogin = $_userLP;
if( isset($Key) && $Key==mb_strtoupper(md5(date('d-m-Y H'))) ){
$_Util['system_user'] = $_ENV['ON'];
}
if($_TronLogin)	error_log("19\n", 3, $_TronFile);
if( trim($row['cd_gs_language'])!='' ){
$_SESSION["_LANGUAGE_"] = trim($row['cd_gs_language']);
}
$_AllLanguages = SS::count("{$_ENV['SYSDB']}gs_language", ["tf_translation" => $_ENV['ON']]);
eLngLoad(DIREDES.'lng/desktop', $_SESSION["_LANGUAGE_"], 1);
if($_TronLogin)	error_log("20\n", 3, $_TronFile);
if( !isset($row['pc_with_id']) ) $row['pc_with_id']='';
if( !isset($row['ip_from']) ) $row['ip_from']='';
if( !isset($row['ip_to']) ) $row['ip_to']='';
if( !isset($row['ip2']) ) $row['ip2']='';
if( !isset($row['ip']) ) $row['ip']='';
if( !isset($row['log_user']) ) $row['log_user']='';
if( !isset($row['log_history']) ) $row['log_history']='';
$_novedades_ = trim($row['ys_news']);
if( $_ENV[SETUP]['System']['ReportsNews'] ){
if( $_novedades_=='' ) $_novedades_ = date('Y-m-d H:i:s', mktime(date('H'),date('i'),date('s'), date('m')-3, date('d'), date('Y')));
}else{
$_novedades_ = '';
}
$_HaceUnMes = date('Y-m-d H:i:s', mktime(date('H'),date('i'),date('s'), date('m')-1, date('d'), date('Y')));
if($_TronLogin)	error_log("21\n", 3, $_TronFile);
if( !isset($_TypeTree) ){
$_TypeTree = ((!isset($row['cd_type_tree'])) ? '' : $row['cd_type_tree']);
if( $_TypeTree!='' ) $row['cd_gs_tree'] = -1;
}
$_Tree = "";
$_TreeNom = "";
$_TreeList = "";
$_ENV[SETUP]['Desktop']['DesktopTreeType'] = 'O';
if( $_ENV[SETUP]['Desktop']['DesktopTreeType']=='O' ){
if( $_TypeTree=='P' ){
$_UserTree = $row['cd_gs_user'];
}
if( $_TypeTree=='P' ){
}else if( $_TypeTree==-1 ){
}else{
if($_TronEntradaON) error_log("25\n",3,$_TronEntrada);
_MensajeHTML($__Lng[52]);
}
if( $_TypeTree!=-1 && trim($_UserTree)=='' ){
if($_TronEntradaON) error_log("26\n",3,$_TronEntrada);
_MensajeHTML(' ['.$_UserTree.']['.$_TypeTree.']');
}
$_Tree = 0;
if( $_TypeTree!=-1 ){
if( trim($_UserTree)=='' ){
if($_TronEntradaON) error_log("26\n",3,$_TronEntrada);
_MensajeHTML(' ['.$_UserTree.']['.$_TypeTree.']');
}
}
SS::query("select cd_gs_tree from {$_ENV['SYSDB']}gs_user_tree", [ "cd_gs_user" => $row['cd_gs_user'] ]);
while( $r=SS::get("num") ){
if( $_TreeList!='' ) $_TreeList .= ',';
$_TreeList .= $r[0];
}
}
if($_TronLogin)	error_log("22\n", 3, $_TronFile);
if( !isZero($row['dt_del']) && $row['dt_del']<$Hoy ){
if($_TronEntradaON) error_log("29\n",3,$_TronEntrada);
_MensajeHTML($__Lng[48]);
}
if($_TronLogin)	error_log("23\n", 3, $_TronFile);
if( $row['dt_access_last']!='' ){
if( isZero($dt_access_last) ) $dt_access_last = '';
if( isset($_PassDaysToExpire) && $_PassDaysToExpire>0 && $dt_access_last!='' && $dt_access_last<date('Y-m-d', mktime(0,0,0, date('m'), date('d')-$_PassDaysToExpire, date('Y'))) ){
$_User = $row['cd_gs_user'];
SS::update("{$_ENV['SYSDB']}gs_user", ["permission"=>'C'], ["cd_gs_user"=>$_User]);
if($_TronEntradaON) error_log("31\n",3,$_TronEntrada);
_MensajeHTML($__Lng[48]);
}
}
if($_TronLogin)	error_log("24\n", 3, $_TronFile);
if( $_SESSION["_Development"] && $_gsMaster=='' && file_exists('../_d_/cfg/permission.ini') ){
if( !in_array($row['cd_gs_user'], explode(',',str_replace(' ','',file_get_contents('../_d_/cfg/permission.ini')))) ){
if($_TronEntradaON) error_log("32\n",3,$_TronEntrada);
_MensajeHTML($__Lng[53]);
}
}
if($_TronLogin)	error_log("25\n", 3, $_TronFile);
$Zip = 1;
if( empty($_SERVER['HTTP_ACCEPT_ENCODING']) ){
$Zip = 0;
if($_TronEntradaON) error_log("33\n",3,$_TronEntrada);
_MensajeHTML($__Lng[56]);
$_usuNombre .= ' ('.$__Lng[57].')';
}
$_usuNombre = str_replace(' ', '&nbsp;', $_usuNombre);
if($_TronLogin)	error_log("26\n", 3, $_TronFile);
$_DT			= $_SESSION["_Desktop"];
$_AvisoStatus_	= '';
$_CDI_			= date('U');
$_ALERTS_		= $_CDI_;
if( !$_ENV[SETUP]['List']['TCPDF'] ){
$PDFExtension = false;
foreach(get_loaded_extensions() as $key=>$value){
if( mb_strtoupper(trim($value))=='PDFLIB' ){
$PDFExtension = true;
break;
}
}
if( !extension_loaded('pdf') && !$PDFExtension ){
$_SESSION["pdf_private"] = $_ENV['OFF'];
$_SESSION["pdf_public"]  = $_ENV['OFF'];
}
}
$_AvisosCada = 0;
$_AlertCheck = $_AvisosCada * 1;
$_notools_	 =  (( $row["print_public"]!=$_ENV['ON'] && $row["print_private"]!=$_ENV['ON'] && $row["print_tab_public"]!=$_ENV['ON'] && $row["print_tab_private"]!=$_ENV['ON'] )?'P':'').
(( $row["xls_public"]!=$_ENV['ON'] && $row["xls_private"]!=$_ENV['ON'] ) ? 'x' : '').
(( $row["pdf_public"]!=$_ENV['ON'] && $row["pdf_private"]!=$_ENV['ON'] ) ? 'p' : '').
(( $row["xml_public"]!=$_ENV['ON'] && $row["xml_private"]!=$_ENV['ON'] ) ? 'm' : '').
(( $row["txt_public"]!=$_ENV['ON'] && $row["txt_private"]!=$_ENV['ON'] ) ? 't' : '').
(( $row["csv_public"]!=$_ENV['ON'] && $row["csv_private"]!=$_ENV['ON'] ) ? 'V' : '');
$_LogUser_	  = $row['log_user'];
$_LogHistory_ = $row['log_history'];
if($_TronLogin)	error_log("27\n", 3, $_TronFile);
if( $_ENV[SETUP]['Login']['WorkingHours'] ){
S::$_User = $_SESSION["_User"];
$_SESSION["_Node"] = $row['cd_gs_node'];
$_SESSION["_User"] = $row['cd_gs_user'];
$_SESSION["_WebMaster"] = $_WebMaster;
$_SESSION["_D_"] = "";
include(DIREDES."itm/accessnow.php");
if( !accessNow($schedule) ){
$_SESSION["_Node"] = -1;
$_SESSION["_User"] = -1;
_MensajeHTML($__Lng[58]);
}
}
$IpUsuario	= trim($row['ip']);
$Ip2		= trim($row['ip2']);
$IpIni		= trim($row['ip_from']);
$IpFin		= trim($row['ip_to']);
$_ViewPassChange = $row['new_pass'];
$PcCodId = trim($row['pc_with_id']);
$PcTotal = trim($row['pc_total']);
$row = SS::selectOne("select permission,nm_gs_node,dt_del from {$_ENV['SYSDB']}gs_node", ["cd_gs_node" => $_Node]);
$_NomNodo = mb_strtoupper(trim($row['nm_gs_node']));
SS::free();
if( $row['permission']!=$_ENV['ON'] ){
if($_TronEntradaON) error_log("35\n",3,$_TronEntrada);
_MensajeHTML($__Lng[59]);
}
if( $row['dt_del']!='' && !isZero($row['dt_del']) ){
if( SS::isDriver("informix,oracle") ) $row['dt_del'] = eDateFormat($row['dt_del'], "F4", "d");
if( $row['dt_del']<date('Y-m-d') ){
if($_TronEntradaON) error_log("36\n",3,$_TronEntrada);
_MensajeHTML($__Lng[60]);
}
}
$_aUser = SS::selectOne("select * from {$_ENV['SYSDB']}gs_user", ["cd_gs_user" => $_User]);
SS::free();
if($_TronLogin)	error_log("28\n", 3, $_TronFile);
if( $_gsMaster==$_InitWeb && $_InitWeb!='' ){
ActivarWeb($NumSerie);
exit;
}
if($_TronLogin)	error_log("29\n", 3, $_TronFile);
if( !isset($_CacheSrv) ) $_CacheSrv = false;
if( !isset($_CachePc) ) $_CachePc = '';
if($_TronLogin)	error_log("30\n", 3, $_TronFile);
if( !empty($Parametro) ){
$_SERVER['QUERY_STRING'] = mb_substr($Parametro, 1);
}
if($_TronLogin)	error_log("31\n", 3, $_TronFile);
list($xAncho, $xAlto, $xColor) = explode(',', $_SESSION["_Resolution_"].",,");
$_pxW_ = (int)$xAncho;
$_pxH_ = (int)$xAlto;
if($_TronLogin)	error_log("32\n", 3, $_TronFile);
if( !isset($_G_) ) $_G_ = '';
$_IST_ = ((isset($_InstanceSrvType)) ? $_InstanceSrvType : -1);
$_SESSION["_LANGUAGE_SUFFIX"] = '_'.$_SESSION["_LANGUAGE_"];
if( $_SESSION["_LANGUAGE_"]=='es' && SS::count("{$_ENV['SYSDB']}gs_language", ["tf_translation" => $_ENV['ON']])==0 ){
$_SESSION["_LANGUAGE_SUFFIX"] = '';
}
eTronToExit("[user: {$_User}]");
S::$_User = $_User;
$_SESSION["_User"] = $_User;
$_SESSION["_Node"] = $_Node;
$_SESSION["_DT"] = $_DT;
$_SESSION["_pxH_"] = $_pxH_;
$_SESSION["_pxW_"] = $_pxW_;
$_SESSION["_AvisoStatus_"] = $_AvisoStatus_;
$_SESSION["_novedades_"] = $_novedades_;
$_SESSION["_CDI_"] = $_CDI_;
$_SESSION["_ALERTS_"] = $_ALERTS_;
$_SESSION["_CacheSrv"] = $_CacheSrv;
$_SESSION["_CachePc"] = $_CachePc;
$_SESSION["_notools_"] = $_notools_;
$_SESSION["_WebMaster"] = $_WebMaster;
$_SESSION["_LogUser_"] = $_LogUser_;
$_SESSION["_LogHistory_"] = $_LogHistory_;
$_InsertToSeek = false;
$_SESSION["_InsertToSeek"] = $_InsertToSeek;
$_SESSION["_DOC_"] = 0;
$_SESSION["_G_"] = $_G_;
$_SESSION["_IST_"] = $_IST_;
$_SESSION["_UserLogin"] = $_UserLogin;
$_SESSION["_Tree"] = $_Tree;
$_SESSION["_TreeList"] = $_TreeList;
$_ENV[SETUP]['Channel']['Status'] = $_ENV[SETUP]['Channel']['Status'];
$_ENV[SETUP]['ChannelDevelopment']['Status'] = $_ENV[SETUP]['ChannelDevelopment']['Status'];
if( !isset($_ENV[SETUP]['System']['CheckboxValues']) ){
$_ENV[SETUP]['System']['CheckboxValues'] = "S,";
}
list($_ENV[SETUP]['System']['CheckboxOn'], $_ENV[SETUP]['System']['CheckboxOff']) = explode(",", $_ENV[SETUP]['System']['CheckboxValues']);
$_ENV['ON']  = $_ENV[SETUP]['System']['CheckboxOn'];
$_ENV['OFF'] = $_ENV[SETUP]['System']['CheckboxOff'];
$_FormatMonth	 = $_ENV[SETUP]['System']['FormatMonth'];
$_FormatDate	 = $_ENV[SETUP]['System']['FormatDate'];
$_FormatDateTime = $_ENV[SETUP]['System']['FormatDateTime'];
$_FormatNumber	 = $_ENV[SETUP]['System']['FormatNumber'];
$_FormatPhone	 = $_ENV[SETUP]['System']['FormatPhone'];
$_FirstWeekDay	 = $_ENV[SETUP]['System']['FirstWeekDay'];
eDataSetup();
$REMOTE_ADDR = ((empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR']);
if( $REMOTE_ADDR=="" ) $REMOTE_ADDR = $_SERVER['HTTP_HOST'];
$error = 'OK';
$_Reload = '';
if($_TronLogin)	error_log("33\n", 3, $_TronFile);
$sNom_gs_navegador	= trim($_SESSION["_Platform_"]);
$sNombre			= $_SESSION["_Explorer_"];
$sResolucion		= $_SESSION["_Resolution_"];
$sVarios			= $_SESSION["_NavigatorLng_"];
if( eSubstrCount($sNom_gs_navegador, ' ') ){
if( preg_match_all('/(Windows|Macintosh|Linux|Android|iPhone)/iu', $sNom_gs_navegador, $matches) ){
$sNom_gs_navegador = $matches[0][0];
}
}
$_SESSION["_Platform_"] = $sNom_gs_navegador;
$_SESSION["OS"] = (mb_strpos(mb_strtoupper($sNom_gs_navegador), "WINDOW") ? "WIN":"MAC");
$_SESSION["_BYPHONE"] = false;
$_SESSION["cssSufijo"] = '';
$_SESSION["factorZoom"] = 1;
if( preg_match('/^(Android|iPhone)$/iu', $sNom_gs_navegador) ){
$fBase = eFileGetVar('/_datos/config/core.css->$fBase');
$fBaseTLF = eFileGetVar('/_datos/config/core.css->$fBaseTLF');
$_SESSION["factorZoom"] = number_format($fBaseTLF/$fBase, 4);
$_SESSION["cssSufijo"] = "_tlf";
$_SESSION["_BYPHONE"] = true;
}
if($_TronLogin)	error_log("33a\n", 3, $_TronFile);
$total = SS::count("{$_ENV['SYSDB']}gs_navegador",
[
"nm_gs_navegador" => $sNom_gs_navegador,
"nombre"		  => $sNombre,
"resolucion"	  => $sResolucion,
"varios"		  => $sVarios
]
);
if( $total > 0 ){
SS::insert("{$_ENV['SYSDB']}gs_navegador",
[
"nm_gs_navegador" => $sNom_gs_navegador,
"nombre"		  => $sNombre,
"resolucion"	  => $sResolucion,
"varios"		  => $sVarios
]
);
$xNavegador = SS::id();
}else{
[ "cd_gs_navegador" => $xNavegador] =
SS::query("select cd_gs_navegador from {$_ENV['SYSDB']}gs_navegador",
[
"nm_gs_navegador" => $sNom_gs_navegador,
"nombre"		  => $sNombre,
"resolucion"	  => $sResolucion,
"varios"		  => $sVarios
]
);
}
if($_TronLogin)	error_log("34\n", 3, $_TronFile);
$Pagina = $_SERVER['SCRIPT_FILENAME'];
if( $_SESSION["share"]['isMultitenan'] ){
if( SS::count( "{$_ENV['SYSDB']}gs_conexion", [ "conexion" => $_SESSION["_Connection_"] ]) > 0 ){
SS::delete("{$_ENV['SYSDB']}gs_conexion", [ "conexion" => $_SESSION["_Connection_"] ]);
}
SS::query("insert into {$_ENV['SYSDB']}gs_conexion select * from {$_ENV['SYSDB']}gs_conexion", [ "conexion" => $_SESSION["_Connection_"] ]);
$_SESSION["_Connection_"] = SS::id();
SS::delete("{$_ENV['SYSDB']}gs_conexion", [ "conexion" => $_SESSION["_Connection_"] ]);
}
SS::update("{$_ENV['SYSDB']}gs_conexion",
[
"cd_gs_user" => $_User,
"cd_gs_node" => $_Node,
"cd_gs_tree" => $_Tree,
"cdi"		 => $cdiIni
],[
"conexion"=>$_SESSION["_Connection_"]
]
);
if( $_SESSION["sql"]['statistics'] ){
$_Connection_ = $_SESSION["_Connection_"];
SS::insert("{$_ENV['SYSDB']}gs_acceso",
[
"cd_gs_toperacion"	=> 'LG',
"conexion"			=> $_Connection_,
"pagina"			=> 'login',
"parametro"			=> '',
"registros"			=> 0,
"cd_gs_user"		=> $_User,
"cd_gs_node"		=> $_Node,
"cdi"				=> $cdiIni
]
);
}
if($_TronLogin)	error_log("35\n", 3, $_TronFile);
$_HayAddSelect = _HayAddSelect();
if($_TronLogin)	error_log("37\n", 3, $_TronFile);
$IpSuma = $IpUsuario.$Ip2.$IpIni.$IpFin;
$IpAutorizada = false;
if( $IpSuma!='' ){
$IpUsuario	= FormatoIP($IpUsuario);
$Ip2		= FormatoIP($Ip2);
$IpIni		= FormatoIP($IpIni);
$IpFin		= FormatoIP($IpFin);
if( !empty($_SERVER['HTTP_CLIENT_IP']) ){
$RemoteAddr = $_SERVER['HTTP_CLIENT_IP'];
}elseif( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ){
$RemoteAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
$RemoteAddr = $_SERVER['REMOTE_ADDR'];
}
$RemoteAddr = FormatoIP($RemoteAddr);
$Entrar = 0;
if( $IpUsuario	== $RemoteAddr ) $Entrar++;
if( $Ip2		== $RemoteAddr ) $Entrar++;
if( $RemoteAddr >= $IpIni && $RemoteAddr <= $IpFin ) $Entrar++;
if( $Entrar>0 ) $IpAutorizada = true;
}
if($_TronLogin)	error_log("38\n", 3, $_TronFile);
if($_TronLogin)	error_log("39\n", 3, $_TronFile);
if( $IpSuma!='' && !$IpAutorizada ){
if($_TronEntradaON) error_log("46\n", 3, $_TronEntrada);
_MensajeHTML('24. '.$__Lng[66]);
}
if( filesize('../_datos/config/session.ini')>20 ){
include('../_datos/config/session.ini');
if( $php_errormsg!='' ){
if( $_gsTron ) eTron('session.ini: '.$php_errormsg);
die(eTrace('session.ini: '.$php_errormsg));
}
}
$_SESSION["BoxDir"] = _InVar($_ENV[SETUP]['UploadFile']['BoxDir']);
if($_TronLogin)	error_log("40\n", 3, $_TronFile);
if($_TronLogin)	error_log("41\n", 3, $_TronFile);
if( $_gsMaster!='' && eSubstrCount('~AaMPHD',$_gsMaster)==0 ){
exit;
}
if($_TronLogin)	error_log("42\n", 3, $_TronFile);
if( $_ViewPassChange>1 ){
$_ViewPassChange--;
SS::update("{$_ENV['SYSDB']}gs_user", ["new_pass" => $_ViewPassChange], ["cd_gs_user" => $_User]);
}
if($_TronLogin)	error_log("43\n", 3, $_TronFile);
if( isset($_PassDaysToChange) && $_PassDaysToChange>0 ){
SS::query("select count(*) from {$_ENV['SYSDB']}gs_user where cd_gs_user={{cd_gs_user}} and (dt_pass<{{dt_pass}} or dt_pass is null or dt_pass='')", [
"cd_gs_user" => $_User,
"dt_pass" => date('Y-m-d', mktime(0,0,0, date('m'), date('d')-$_PassDaysToChange, date('Y'))),
]);
$total = SS::get("num");
if( $total[0] > 0 ){
SS::update("{$_ENV['SYSDB']}gs_user", ["new_pass" => 1], ["cd_gs_user" => $_User]);
$_ViewPassChange = 1;
}
}
if($_TronLogin)	error_log("44\n", 3, $_TronFile);
$setUpdate = [ "dt_access_last" => $Hoy ];
if( eFileGetVar('Login.HostGet') ){
$setUpdate["host"] = $REMOTE_ADDR;
}
SS::update("{$_ENV['SYSDB']}gs_user", ["dt_access_last"=>$Hoy], ["cd_gs_user"=>$_User]);
if($_TronLogin)	error_log("45\n", 3, $_TronFile);
if( $_ENV[SETUP]['Channel']['Status'] || $_ENV[SETUP]['ChannelDevelopment']['Status'] ){
include_once(DIREDES."itm/jwt.php");
$payLoad = array();
$payLoad['exp'] = mktime(date("H"), date("i")+$ChatChannel["jwt"]["maxLifeTime"], date("s"), date("n"), date("j"), date("Y"));
foreach($ChatChannel["filter"] as $k=>$v){
$payLoad[$k] = $row[$k];
}
$token = JWT::encode($payLoad, $ChatChannel["jwt"]["key"], $ChatChannel["jwt"]["method"]);
setcookie("eDesChatChannel", $token, 0, "/");
$_COOKIE['eDesChatChannel'] = $token;
}
if( file_exists('../_tmp/err/location.php') ){
if( !file_exists("../_tmp/err/{$_User}.ord") ){
include('../_tmp/err/location.php');
eEnd();
}
}
if($_TronLogin)	error_log("46\n", 3, $_TronFile);
if( $_SESSION["_D_"]!='' ){
$xDim = file_get_contents('../_d_/cfg/dim.lp');
$DimUser = array();
if( $xDim!='' ) $DimUser = unserialize(gzuncompress($xDim));
if( $_gsMaster!='' && eSubstrCount('~AMP', $_gsMaster)==1 && $_gsNomUser!='' && $DimUser['u'.$_User]!=$_gsNomUser ){
$DimUser['u'.$_User] = $_gsNomUser;
$xDim = serialize($DimUser);
file_put_contents('../_d_/cfg/dim.lp', gzcompress($xDim,1));
}
if($_TronLogin)	error_log("47\n", 3, $_TronFile);
}
$_SESSION["_Development"] = false;
if( !isset($_Test) ) $_Test = false;
if( !isset($_ErrImg) ) $_ErrImg = false;
if($_TronLogin)	error_log("48\n", 3, $_TronFile);
$_ViewInfoNovedad = false;
if( $_ENV[SETUP]['System']['ReportsNews'] ){
if( empty($_novedades_) ) $_novedades_ = "0000-00-00 00:00:00";
if( SS::count("{$_ENV['SYSDB']}gs_novedad", ["cdi" => ">={$_novedades_}"])>0 ){
$_ViewInfoNovedad = true;
}
}
$_ENV[SETUP]['Desktop']['MenuAutoHidden'] = (($_ENV[SETUP]['Desktop']['MenuAutoHidden'])?1:0);
$IconFolder = "©,ª";
$IconDoc = "b";
if( $_ENV[SETUP]['Desktop']['DefaultTreeIcon'] && $_ENV[SETUP]['Desktop']['DefaultTreeIconChar']!='' ){
$dim = explode(",", $_ENV[SETUP]['Desktop']['DefaultTreeIconChar']);
$IconFolder = $dim[0].$dim[1];
$IconDoc = $dim[2];
}
$_ENV[SETUP]['Desktop']['DefaultTreeFolder'] = explode(",", $_ENV[SETUP]['Desktop']['DefaultTreeFolder'].",");
$crearSetup = false;
try{
SS::runDailyProcess("dailyMaintenance");
}catch(Exception $e){
S::error("", "Error runDailyProcess: ".$e->getMessage());
}
function dailyMaintenance($today){
SS::tron(">>> dailyMaintenance( {$today} )");
$sec = $_ENV[SETUP]['System']['SessionMaxLife'] * 1.2;
SYS::deleteFilesWithExpired("../_tmp/sessions/", "sess_*", $sec);
executeMaintenance();
}
function executeMaintenance(){
global $crearSetup, $_DownloadPath, $_DownloadDelete;
$crearSetup = true;
if( $_ENV[SETUP]['System']['PhpOnLine']  ){
}
if( !isset($_DownloadPath)   || $_DownloadPath==''   ) $_DownloadPath = '/_tmp/exp';
$xDownloadPath = eScript($_DownloadPath);
if( !isset($_DownloadDelete) || $_DownloadDelete=='' ) $_DownloadDelete = 5*365;
$n = eFileGetVar('/_d_/cfg/edes.ini->$_nDaily');
if( gettype($n)=="array" ) $n = 7;
exec("php ".DIREDES."back_jobs.php {$xDownloadPath} {$_DownloadDelete} {$n} > /dev/null &", $lines);
$DeleteTemporary = (SETUP::$UploadFile['DeleteTemporary'] ?: "pdf:32");
$tmp = explode(",", str_replace(" ", "", $DeleteTemporary));
for($n=0; $n<count($tmp); $n++){
list($key, $value) = explode(":", $tmp[$n]);
$key = trim($key);
if( $key=="pdf" ){
$HastaCDI = date("Y-m-d H:i:s", mktime(0,0,0, date('m'), date('d')-(int)$value, date('Y')));
SS::delete("{$_ENV['SYSDB']}gs_download", [ "cdi" => "<{$HastaCDI}" ]);
break;
}
}
if( $_ENV[SETUP]['LogDownload']['LogFileDays']>0 ){
$HastaCDI = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d')-$_ENV[SETUP]['LogDownload']['LogFileDays'], date('Y')));
if( SS::count("{$_ENV['SYSDB']}gs_acceso", [
"objeto" => 'D',
"cdi"	 => "<{$HastaCDI}"
]) > 0 ){
$tmp = $_ENV[SETUP]['LogDownload']['LogFileDownload'];
if( mb_substr($tmp,-1)!='/' ) $tmp .= '/';
SS::select("select num_acceso,cdi from {$_ENV['SYSDB']}gs_acceso", [
"objeto" => 'D',
"cdi"	 => "<{$HastaCDI}"
]);
while( $row=SS::get() ){
@unlink("{$tmp}{$row['num_acceso']}.zip");
}
SS::delete("{$_ENV['SYSDB']}gs_acceso", [
"objeto" => 'D',
"cdi"	 => "<{$HastaCDI}"
]);
}
}
if( $_ENV[SETUP]['LogHistory']['LogGsAccessDays']>0 ){
SS::delete("{$_ENV['SYSDB']}gs_acceso", [
"cdi" => "<".date('Y-m-d H:i:s', mktime(0,0,0, gmdate('m'), gmdate('d')-$_ENV[SETUP]['LogHistory']['LogGsAccessDays'], gmdate('Y')))
]);
}
if( $_ENV[SETUP]['LogHistory']['LogGsErrorDays']>0 ){
SS::delete("{$_ENV['SYSDB']}gs_error", [
"cdi" => "<".date('Y-m-d H:i:s', mktime(0,0,0, gmdate('m'), gmdate('d')-$_ENV[SETUP]['LogHistory']['LogGsErrorDays'], gmdate('Y')))
]);
}
if( $_ENV[SETUP]['LogHistory']['LogGsConnectionsDays']>0 ){
SS::delete("{$_ENV['SYSDB']}gs_conexion", [
"cdi" => "<".date('Y-m-d H:i:s', mktime(0,0,0, gmdate('m'), gmdate('d')-$_ENV[SETUP]['LogHistory']['LogGsConnectionsDays'], gmdate('Y')))
]);
}
if( $_SESSION["share"]['isMultitenan'] ){
SS::delete("{$_ENV['SYSDB']}gs_conexion", [
"cdi" => "<".date('Y-m-d H:i:s', date('U')-($_ENV[SETUP]['System']['SessionMaxLife']*2))
]);
}
$extErr = date('ym');
$rutaErr = '../_tmp/err/_log.';
if( !file_exists($rutaErr.$extErr) && file_exists($rutaErr.'err') ){
rename($rutaErr.'err', $rutaErr.$extErr);
}
$rutaErr = '../_tmp/err/_log_short.';
if( !file_exists($rutaErr.$extErr) && file_exists($rutaErr.'err') ){
rename($rutaErr.'err', $rutaErr.$extErr);
}
if( file_exists('../_datos/config/system_sql.log') ){
$fp2 = fopen('../_datos/config/system_sql.log','r');
if( !($fp2===false) ){
if( flock($fp2, LOCK_EX) ){  // bloqueo exclusivo - ...ojo... poder distingir entre ddbb: MySql, Informix, Oracle
$CDI = trim(file_get_contents('../_datos/config/system_sql.cdi'));
$Dim = explode("\n",fread($fp2, filesize('../_datos/config/system_sql.log')));
for($n=0; $n<count($Dim); $n++){
if( trim($Dim[$n])!='' ){
$Dim[$n] = trim($Dim[$n]);
$oCDI = trim(mb_substr( $Dim[$n], 0, 19 ));
$txt = trim(mb_substr( $Dim[$n], 20 ));
if( $oCDI>$CDI ){
error_log(date('Y-m-d H:i:s').' [SystemIni] '.$txt."\n", 3, '../_datos/config/system_trace.log');
SS::query($txt);
error_log("[SystemEnd]\n", 3, '../_datos/config/system_trace.log');
file_put_contents('../_datos/config/system_sql.cdi', $oCDI);
clearstatcache();
}
}
}
flock($fp2, LOCK_UN);    // libera el bloqueo
fclose($fp2);
}
}
}
if( file_exists('../_datos/config/cron_daily.php') ){
include('../_datos/config/cron_daily.php');
}
}
if( !$crearSetup ){
if( file_exists('../_datos/config/setup.class.php') ){
$n = filectime('../_datos/config/setup.class.php');
if( filectime('../_datos/config/sql.ini')   > $n ||
filectime('../_datos/config/group.var') > $n
){
$crearSetup = true;
}
}else{
$crearSetup = true;
}
}
if( $crearSetup ){
if( !isset($_ENV[SETUP]['System']['SlowSqlWarning']    ) ) $_ENV[SETUP]['System']['SlowSqlWarning'] = 3;
if( !isset($_ENV[SETUP]['System']['SlowSqlFreeScripts']) ) $_ENV[SETUP]['System']['SlowSqlFreeScripts'] = '';
$classTxt = SYS::arrayToClass('SETUP', $_ENV[SETUP], true);
file_put_contents('../_datos/config/setup.class.php', $classTxt);
}
if($_TronLogin)	error_log("50\n", 3, $_TronFile);
$jsResetDevice = Device::loggedIn( SETUP::$Login["RegisteredDevice"], $login, SETUP::$Login["RenewDevice"] );
unset( $_SESSION["device"] );
session_write_close();
include($Dir_."desktop".$_SESSION["_Desktop"]."_web.php");
if($_TronLogin)	error_log("51\n", 3, $_TronFile);
if( !$_NoDesktop ){
eEnd();
}
function _MensajeHTML($mensa){
if( $_POST[$_SESSION["_Remember_"]]=="RecordarClave" ){
}else if( isset($_POST[$_SESSION["_Login_"]]) || isset($_POST[$_SESSION["_Password_"]]) ){
_MensajeJS("Terminar('{$mensa}')");
}
$header = eHTML('$info_only.php', '', 'Document', true);
$historyPushState =  "";
if( $_ENV[SETUP]['System']['UrlStatus']!="" ){
$historyPushState = "<script type='text/javascript'>try{ history.replaceState({foo:'bar'}, '-*-', '{$_ENV[SETUP]['System']['UrlStatus']}'); }catch(e){} </script>";
}
$txt = str_replace(
array(CHR10, CHR13, '{$message}', '{$historyPushState}', '{$header}'),
array(   ""  ,    ""  ,    $mensa   ,   $historyPushState  ,   $header  ),
file_get_contents($GLOBALS["Dir_"]."info_only.php")
);
echo $txt;
eSessionClose(9);
eEnd();
}
function _MensajeJS($txt){
usleep(rand(1, 1000000));
$txt = str_replace(
array(CHR10, CHR13),
array( "<br>",    ""  ),
$txt
);
if( mb_substr($txt,0,8)=='eSubmit(' ){
$_SESSION["eSubmit"] = "ok";
}
if( $txt=='eSubmit()' ){
sendDataToLocalStorage(["x", "c", "s", "r"]);
}
echo $txt.';';
if( mb_substr($txt,0,9)=="Terminar(" ){
eSessionClose(10);
}
eEnd();
}
function sendDataToLocalStorage( $ops ){
$cdi = date("Y-m-d H:i:s");
$cssToLocalStorage = [
"css/all.css",
"css/all_big.css",
"css/all_small.css",
"css/all_tlf.css",
"css/desktop.css",
"css/list.css",
"css/list_big.css",
"css/list_card.css",
"css/list_small.css",
"css/list_tlf.css",
"css/message.css",
"css/message_big.css",
"css/message_small.css",
"css/message_tlf.css",
"css/tab.css",
"css/tab_big.css",
"css/tab_small.css",
"css/tab_tlf.css",
"css/MAC.css",
"css/WIN.css",
"css/print.css"
];
$mp3ToLocalStorage = [
"notification",
"warning"
];
$resourceToLocalStorage = [
"CALCULATOR",
"dateformat",
"GetCondition",
"MultipleFilter",
"filter_list_"
];
$language = $_SESSION["_LANGUAGE_"];
if( empty($language) ){
$language = "es";
}
if( in_array("x", $ops) ){
$fileToLocalStorage = "../../edes.v3/lng/localstorage.lng";
if( !empty($_POST['e_language']) && !empty($_COOKIE['e-language']) && $_POST['e_language']!=$_COOKIE['e-language'] ){
$sendLocalStorage = true;
}else{
$lastUpdate = date("Y-m-d H:i:s", filemtime($fileToLocalStorage));
$sendLocalStorage = ($lastUpdate > $_POST['e_cdi']);
}
if( $sendLocalStorage ){
$dim = file($fileToLocalStorage, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$dimLeng = explode(",", str_replace( ["]", " "], [",", ""], $dim[0] ));
for($i=1; $i<count($dimLeng); $i++){
if( $dimLeng[$i] == $language ){
$postIndex = $i;
break;
}
}
for($i=1; $i<count($dim); $i++){
$tmp = explode("|", $dim[$i]);
$key = $tmp[0];
$text = explode("~", $tmp[$postIndex]."~")[0];
if( empty($text) ){
$text = explode("~", $tmp[1]."~")[0];
}
$text = addslashes($text);
$text = str_replace(array(CHR10, CHR13), array("&#0A;","&#0D;"), $text);
echo "localStorage.setItem('e-x{$key}', '{$text}');";
}
}
}
if( in_array("c", $ops) ){
$file = $cssToLocalStorage[0];
$lastUpdate = date("Y-m-d H:i:s", filemtime($file));
if( $_POST['e_cdi_lng'] < $lastUpdate ){
for($i=0; $i<count($cssToLocalStorage); $i++){
$key = $cssToLocalStorage[$i];
$text = addslashes(file_get_contents($key));
$text = str_replace(array(CHR10, CHR13), array("&#0A;","&#0D;"), $text);
echo "localStorage.setItem('e-c{$key}', '{$text}');";
}
}
}
if( in_array("s", $ops) ){
for($i=0; $i<count($mp3ToLocalStorage); $i++){
$key = $mp3ToLocalStorage[$i];
$file = "{$key}.mp3";
if( file_exists("g/{$file}") ){
$file = "g/{$file}";
}else{
$file = DIREDES."a/g/{$file}";
}
if( file_exists($file) ){
$lastUpdate = date("Y-m-d H:i:s", filemtime($file));
if( $_POST['e_cdi_lng'] < $lastUpdate ){
$text = base64_encode(file_get_contents($file));
$text = 'data:audio/mp3;base64,'.$text;
echo "localStorage.setItem('e-s{$key}', '{$text}');";
}
}
}
}
if( in_array("r", $ops) ){
for($i=0; $i<count($resourceToLocalStorage); $i++){
$key = $resourceToLocalStorage[$i];
if( substr($key,-1)=="_" ){
$keyLng = $key . $language;
if( !file_exists("../../edes.v3/itm/{$keyLng}.html") ){
$keyLng = "{$key}es";
}
$key = $keyLng;
}
$file = "../../edes.v3/itm/{$key}.html";
$lastUpdate = date("Y-m-d H:i:s", filemtime($file));
if( $_POST['e_cdi_lng'] < $lastUpdate ){
$text = file_get_contents($file);
$text = addslashes($text);
$text = str_replace(array(CHR10, CHR13), array("&#0A;","&#0D;"), $text);
echo "localStorage.setItem('e-r{$key}', '{$text}');";
}
}
}
echo "localStorage.setItem('e-cdi-lng', '{$cdi}');";
echo "console.log('New Language: \"{$language}\"');";
return;
}
function FormatoIP($sIp){
$sIp = trim(str_replace(' ', '', $sIp));
if( $sIp!='' ){
$tmp = explode('.', $sIp);
$txt = '';
for($n=0; $n<count($tmp); $n++){
$tmp[$n] = mb_substr('000'.$tmp[$n], -3);
if( $txt!='' ) $txt .= '.';
$txt .= $tmp[$n];
}
$sIp = $txt;
}
return $sIp;
}
function crearToken(&$payLoad, $_privateKey, $_algorithm, $_maxLifeTime){
$payLoad['exp'] = mktime(date("H"), date("i")+$_maxLifeTime , date("s"), date("n"), date("j"), date("Y"));
return JWT::encode($payLoad, $_privateKey, $_algorithm);
}
function eAddSelect( $oCampo, $oCampoLen, $oCampoPx, $Valor, $OnChange ){
echo "<INPUT NAME='{$oCampo}' VALUE=\"{$Valor}\" style='display:none' ALTO=1>";
if( $OnChange!='' ){
${$OnChange} = str_replace( "'", '"', ${$OnChange} );
$OnChange = " onchange='{$OnChange}'";
}
echo "<INPUT NAME='_INPUT_{$oCampo}' IND=-1 TMPIND=-1{$OnChange}";
echo " onmousewheel='_SelSlider()' onfocusin='_SelMemValue(this)' onfocusout='_SelPutValue(this)' onkeypress='_SelNewChar(this)' onkeydown='_SelDelChar(this)' onclick='_SelShow(this)'";
echo " style='background-image:url(g/sel.gif); background-position-x:100%; background-position-y:100%; background-repeat:no-repeat; cursor:var(--cPointer);'";
if( $oCampoPx>0 ) echo " style='width:{$oCampoPx};'";
echo " TYPE='TEXT' SIZE={$oCampoLen} MAXLENGTH={$oCampoLen} VALUE=''>";
echo "<DIV onclick='_SelClick(this)' onselectstart='return false;' onmouseleave='this.style.display=\"none\"' id=Select class='SELECT EDITABLE'>";
echo "<TABLE INIT=0 id='{$oCampo}_TABLE' width=1px onmouseover='_SelCursor(true)' onmouseout='_SelCursor(false)' cols=2>";
echo '<COL style="display:none"><COL>';
echo '<TR><TD><TD>&nbsp;';
$textContent = '';
while( $row = SS::get() ){
echo '<TR><TD>'.trim($row[0]).'<TD>'.trim($row[1]);
if( $Valor == trim($row[0]) ) $textContent = trim($row[1]);
}
echo '</TABLE></DIV>';
if( $textContent!='' ) echo "\n<script type='text/javascript'>DGI('{$oCampo}').value=".'"'.$Valor.'";'."DGI('_INPUT_{$oCampo}').value=".'"'.$textContent.'";</script>';
}
function _HayAddSelect(){
$file = '../_datos/config/desktop_user.ini';
if( !file_exists($file) ){
return false;
}
$txt = file_get_contents($file);
return( eSubstrCount($txt, 'eAddSelect(') > 0 || eSubstrCount($txt, 'eAddSelect (') > 0 );
}
function _genRegExp($prefijo, $db, $user){
if( $db[0]=="9" ){
$data = calcFormatNumber($db, $user);
}else{
$data = calcFormatDate($db, $user);
}
$_ENV[SETUP]['System']["_Format{$prefijo}"]		   = $db;
$_ENV[SETUP]['System']["_Format{$prefijo}EXP"]	   = $data["usr"]["reg-exp"];
$_ENV[SETUP]['System']["_Format{$prefijo}TKNdb"]   = $data["sys"]["replace"];
$_ENV[SETUP]['System']["_Format{$prefijo}TKN"]	   = $data["usr"]["replace"];
$_ENV[SETUP]['System']["_Format{$prefijo}EXPdb"]   = $data["sys"]["reg-exp"];
$_ENV[SETUP]['System']["_Format{$prefijo}TKNuser"] = $data["usr"]["replace"];
}
function eDataSetup(){
global $_FormatMonth, $_FormatDate, $_FormatDateTime, $_FormatNumber, $_FormatPhone, $_FirstWeekDay;
_genRegExp("P4" , "yyyy-mm", (isset($_FormatMonth))? $_FormatMonth : "yyyy-mm");
_genRegExp("F4" , "yyyy-mm-dd", (isset($_FormatDate))? $_FormatDate : "dd-mm-yyyy");
_genRegExp("CDI", "yyyy-mm-dd hh:ii:ss", (isset($_FormatDateTime))? $_FormatDateTime : "yyyy-mm-dd hh:ii:ss");
_genRegExp("T"  , "999999999", (isset($_FormatPhone))? $_FormatPhone : "999999999");
$_ENV[SETUP]['System']['FormatNumber'] = (isset($_FormatNumber))? $_FormatNumber : ".,";
$pDate = trim(eStrtr($_ENV[SETUP]['System']['FormatDate'], "dmy", "   "));
$pTime = trim(eStrtr($_ENV[SETUP]['System']['FormatDateTime'], "dmy his".$pDate[0], str_repeat(" ",8)));
$_ENV[SETUP]['System']['FormatDelimiter'] = $pDate[0].$pTime[0];
$_ENV[SETUP]['System']['FirstWeekDay'] = (isset($_FirstWeekDay) && preg_match('/^(0|1)$/u',$_FirstWeekDay))? $_FirstWeekDay : 0;
}
function calcFormatDate($formatSys, $formatUser){
return array(
"sys" => _calcFormatDate($formatSys, $formatUser),
"usr" => _calcFormatDate($formatUser, $formatSys)
);
}
function _calcFormatDate($formatSys, $formatUser){
$shortSample = array("y", "m", "d", "h", "i", "s");
$longSample  = array("yyyy", "mm", "dd", "hh", "ii", "ss");
$formatSys  = strtolower($formatSys);
$formatUser = strtolower($formatUser);
$formatSys  = str_replace($longSample, $shortSample, $formatSys);
$formatUser = str_replace($longSample, $shortSample, $formatUser);
$formatClearSys = preg_replace('/[^ymdhis]/', '', $formatSys); // formato sin simbolos y de solo un caracter
$total = strlen($formatSys);
$dimPattern = array(); // del "Sys"  - expresion regular para dividir la cadena del "SYS"
$dimReplace = array(); // del "User" - posicion de los replaces para pintar el dato "USER"
for ($i = 0; $i < $total; $i++) {
if (!in_array($formatSys[$i], $shortSample)) {
array_push($dimPattern, '\\' . $formatSys[$i]);
array_push($dimReplace, $formatUser[$i]);
continue;
}
$pos = strpos($formatClearSys, $formatUser[$i]) + 1;
array_push($dimReplace, "\${$pos}");
$len = ($formatSys[$i] == "y") ? 4 : 2;
array_push($dimPattern, "(\d{{$len}})");
}
return array(
"from"  => $formatSys,
"to"  => $formatUser,
"reg-exp" => '/^' . implode('', $dimPattern) . '/',
"replace" => implode('', $dimReplace)
);
}
function calcFormatNumber($formatSys, $formatUser){
return array(
"sys" => _calcFormatNumber($formatSys, $formatUser),
"usr" => _calcFormatNumber($formatUser, $formatSys)
);
}
function _calcFormatNumber($formatSys, $formatUser){
$dim = preg_split('/([^9]+)/', $formatUser, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
$formatClearUser = preg_replace('/[^9]/', '', $formatUser);
$withDelimiter = ($formatUser != $formatClearUser); //$withDelimiter = $formatClearSys == implode("", $dim);
if (!$withDelimiter) {
$dim = preg_split('/([^9]+)/', $formatSys, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
}
$total = count($dim);
$dimPattern = array(); // del "Sys"  - expresion regular para dividir la cadena del "SYS"
$dimReplace = array(); // del "User" - posicion de los replaces para pintar el dato "USER"
$index = 0;
for ($i = 0; $i < $total; $i++) {
if ($dim[$i][0] != 9) {
if ($withDelimiter) {
array_push($dimReplace, $dim[$i]);
} else {
array_push($dimPattern, '\\' . $dim[$i]);
}
continue;
}
$index++;
array_push($dimReplace, "\${$index}");
$len = strlen($dim[$i]);
array_push($dimPattern, "(\d{{$len}})");
}
return array(
"from"  => $formatSys,
"to"  => $formatUser,
"reg-exp" => '/^' . implode('', $dimPattern) . '/',
"replace" => implode('', $dimReplace)
);
}
function _ProxCDI($CDI, $Tipo){
$Tipo = mb_strtoupper($Tipo);
$sTipo = $Tipo;
if( $Tipo!='' && $Tipo!='NONE' && $CDI < date('Y-m-d H:i:s') ){
$CDI = trim($CDI);
if( $CDI=='' ){
$CDI = date('Y-m-d H:i:s');
}else{
list($Iz,$Dr) = explode(' ',$CDI);
list($an,$me,$di) = explode('-',$Iz);
list($ho,$mi,$se) = explode(':',$Dr);
switch( $Tipo ){
case 'DAILY':
$di++;
break;
case 'WEEKLY':
do{
$di++;
}while( date("N", mktime( $ho, $mi, $se, $me, $di, $an ) )!=1 );
break;
case 'FORTNIGHTLY':
for( $n=0; $n<2; $n++ ){
do{
$di++;
}while( date("N", mktime( $ho, $mi, $se, $me, $di, $an ) )!=1 );
}
break;
case 'MONTHLY':
$me++;
break;
case 'YEARLY':
$an++;
break;
default:
$di += (int)$Tipo;
}
$CDI = date( 'Y-m-d H:i:s', mktime( $ho, $mi, $se, $me, $di, $an ) );
if( $CDI <= date('Y-m-d H:i:s') ) $CDI = _ProxCDI( $CDI, $sTipo );
}
return $CDI;
}
return $CDI;
}
function ActivarWeb($NumSerie){}
function _EsUnSubTree($NomArbol, $nNewTree){
SS::query("select filename,permission from {$_ENV['SYSDB']}gs_tree", [ "cd_gs_tree" => $nNewTree ], 1);
["filename"=>$NewNomTree, "permission"=>$ConPermiso] = SS::get("assoc", 1);
$NewNomTree = trim($NewNomTree);
if( $NewNomTree=='' || $ConPermiso!='S' ) return '';
$oTree = file('../tree/'.$NomArbol);
$nTree = file('../tree/'.$NewNomTree);
if( count($oTree)<count($nTree) ) return '';
$oTOp = count($oTree);
$nTOp = count($nTree);
$oDesde = 0;
for($n=0; $n<$nTOp; $n++ ){
$EstaIncluido = false;
for($o=$oDesde; $o<$oTOp; $o++ ){
if( $oTree[$o]==$nTree[$n] ){
$EstaIncluido = true;
$oDesde = $o+1;
break;
}
}
if( !$EstaIncluido ){
return '';
break;
}
}
return $NewNomTree;
}
function _LngLoad( $File ){
$tmp = file( $File.'.lng' );
list(,$Lngs) = explode(']',$tmp[0]);
list($Lngs) = explode('|',$Lngs);
$tmp4 = explode( ',', trim(str_replace(' ','',$Lngs)) );
for( $i=0; $i<count($tmp4); $i++ ){
$tmp4[$i] = trim($tmp4[$i]);
if( $tmp4[$i]==$_SESSION["_LANGUAGE_"] ){
$uCol = $i+1;
}
if( $tmp4[$i]==$_SESSION["_LanguageDefault"] ){
$dCol = $i+1;
}
}
$Dim = array();
$mk = 0;
for( $n=1; $n<count($tmp); $n++ ){
$tmp2 = explode('|',$tmp[$n]);
$k = $tmp2[0];
$txt = trim($tmp2[$uCol]);
if( $txt=='' ) $txt = trim($tmp2[$dCol]);
$v = str_replace('"','&quot;',trim($txt));
$k = $k*1;
$mk = max( $mk, $k );
$Dim[$k] = $v;
}
$txt = ''; for( $n=0; $n<$mk+1; $n++ ) $txt .= $Dim[$n].'|';
return $txt;
}
function _GetEmptyPage(){
$Leer = true;
$Dim = file('../_datos/config/empty_page.htm');
$PagVacia = '';
for( $i=0; $i<count($Dim); $i++ ){
$Dim[$i] = trim($Dim[$i]);
if( eSubstrCount(mb_strtoupper($Dim[$i]),'<'.'/SCRIPT>')>0 && eSubstrCount(mb_strtoupper($Dim[$i]),'<SCRIPT')>0 ){
continue;
}else if( mb_strtoupper($Dim[$i])=='<'.'/SCRIPT>' || mb_strtoupper(mb_substr($Dim[$i],0,7))=='<SCRIPT' ){
$Leer = !$Leer;
continue;
}
if( $Leer ) $PagVacia .= $Dim[$i];
}
return $PagVacia;
}
function eAddMenuOption( $Label, $HR='', $Icon='', $Title='', $Activo=true ){
if( $_SESSION["_DesktopType"] == 2 || $_SESSION["_DesktopType"] == 3 ){
if( $Label=='-' ){
echo '<TR><TD class=Linea colspan=3>';
}else{
if( $HR!='' ) $HR = " HR='".str_replace("'",'"',$HR)."'";
if( $Icon!='' ) $Icon = "<img src='{$Icon}'>";
if( $Title!='' ) $Title = " title='{$Title}'";
$Activo = (( !$Activo ) ? ' disabled':'');
echo "<TR{$HR}{$Title}{$Activo}><TD>{$Icon}<TD>{$Label}<TD>";
}
}else if( $_SESSION["_DesktopType"] < 2 ){
if( $Label=='-' ){
echo "<tr id=o><td id=2 LIN=1 style='font-size:1px;vertical-align:middle;' HR=''><IMG SRC='g/linea.gif' width=100% height=1>";
}else{
if( $HR!='' ) $HR = " HR='".str_replace("'",'"',$HR)."'";
if( $Icon!='' ){
$Icon = "<img src='{$Icon}'>";
}else{
$Icon = "<IMG SRC='g/doc_0.gif'>";
}
if( $Title!='' ){
$Title = str_replace( '&#92;n', CHR10, $Title );
$Title = " title='{$Title}'";
}
$Activo = (( !$Activo ) ? ' disabled':'');
echo "<tr id=o{$Title}><td id=2 {$HR}>{$Icon}{$Label}";
}
}
}
function randomString($leng){
$dim = [[48,57], [65,90], [97,122]];
$string = "";
$txt = "";
for($g=0; $g<3; $g++){
for($i=$dim[$g][0]; $i<=$dim[$g][1]; $i++){
$string .= mb_chr($i);
}
}
$maxLeng = mb_strlen($string)-1;
for($n=0; $n<$leng; $n++){
$txt .= mb_substr($string, rand(0,$maxLeng), 1);
}
return $txt;
}
function pngToData($charset, $data){
$data = base64_decode($data);
$im = imagecreatefromstring($data);
$ret = "";
if( !($im!==false) ){
die('An error occurred.');
}
$imgHeight = imagesy($im);
for($y=0; $y<$imgHeight; $y++){
$rgb = imagecolorat($im, 0, $y);
$r = ($rgb >> 16) & 0xFF;
if( $r==255 ){
$ret .= " ";
continue;
}
$ret .= $charset[$r-1];
}
return $ret;
}
function dataToPng($charset, $data, $comp=""){
$ret = "";
for($i=0; $i<strlen($data); $i++){
$index = strpos($charset, $data[$i]);
if( $index===false ){
$ret .= " ";
$comp = substr_replace($comp, " ", $i, 1);
continue;
}
$ret .= $data[$i];
}
return $ret;
return [($ret===$comp), $ret, $comp];
}
?>