<?php
function eContextPKMain_NO_(){
if( empty($_ENV[SYS]['contextMain']) ){
if( empty($_ENV[SYS]['context']) ) $_ENV[SYS]['context'] = 1;
$_ENV[SYS]['contextMain'] = $_ENV[SYS]['context'];
}
return $_ENV[SYS]['contextMain'];
}
function _checkRequestExists(){
return;
list($script) = preg_split('/[\?\&]/u', $_ENV[SYS]['queryString']);
if( $r["type"]!="url" ){
die("ERROR: record not found");
}
$r["data"] = trim($r["data"]);
if( mb_substr($r["data"], -1)!="}" ){
return get2array($r["data"]);
}
$r["data"] = json_decode($r["data"]);
if( json_last_error()==0 ){
return $r["data"];
}
switch(json_last_error()) {
case JSON_ERROR_NONE:
$error = 'Sin errores';
break;
case JSON_ERROR_DEPTH:
$error = 'Excedido tamaño máximo de la pila';
break;
case JSON_ERROR_STATE_MISMATCH:
$error = 'Desbordamiento de buffer o los modos no coinciden';
break;
case JSON_ERROR_CTRL_CHAR:
$error = 'Encontrado carácter de control no esperado';
break;
case JSON_ERROR_SYNTAX:
$error = 'Error de sintaxis, JSON mal formado';
break;
case JSON_ERROR_UTF8:
$error = 'Caracteres UTF-8 malformados, posiblemente están mal codificados';
break;
default:
$error = 'Error desconocido';
break;
}
die("ERROR: ".$error);
}
function eContextInit_NO_(){
return;
$_SESSION["context"] += rand(1, 99);
$_ENV[SYS]['context'] = $_SESSION["context"];
$_SESSION["context"] = 0;
}
function eSerialAdd(){
["pk" => $_ENV[SYS]['context']] = SS::selectOne("select pk from {$_ENV['SYSDB']}gs_serial", ["cd_gs_conexion" => $_SESSION["_Connection_"]]);
$_ENV[SYS]['context']++;
SS::update("{$_ENV['SYSDB']}gs_serial",
[ "pk" => $_ENV[SYS]['context'] ],
[ "cd_gs_conexion" => $_SESSION["_Connection_"] ]
);
return $_ENV[SYS]['context'];
}
function eContextPK($main=false){
if( isset($_ENV[SYS]['context']) ){
return $_ENV[SYS]['context'];
}
return $_SESSION["context"];
}
function eContextAddUrl(){
return '&_CONTEXT='.eContextPK();
}
function _contextAdd($script, $condition=array()){
return;
list(,$script) = preg_split('/[\?\&]/u', $script);
foreach($condition as $k=>$v){                      // eTrace(gettype($v)); if( gettype($v)=="array" ){
foreach($v as $k2=>$v2){                        // $v2 = utf8_encode($v2);
$condition[$k][$k2] = $v2;
}
}
$condition = str_replace("'", "\\'", $condition);
if( json_last_error()>0 ){
die("_contextAdd: con errores");
}
$type = "url";
SS::insert("{$_ENV['SYSDB']}gs_context", [
"cd_gs_conexion"=> $_SESSION["_Connection_"],
"context"		=> $_ENV[SYS]['context'],
"type"			=> $type,
"script"		=> $script,
"data"			=> $condition
]);
}
function eSessionAddUrl(){
return "&_SESS_={$_SESSION['_SESS_']}";
}
function _urlGet($url){
return $url.eSessionAddUrl();
}
function get2array($txt){
if( gettype($txt)=="array" ){
return $txt;
}
$dim = array();
$tmp = explode("&", $txt);
for($i=0; $i<count($tmp); $i++){
if( trim($tmp[$i])=="" ) continue;
eExplodeOne($tmp[$i], "=", $k, $v);
if( preg_match('/^(_CONTEXT|_SESS)$/u', $k) ) continue;
$dim[$k] = $v;
}
return $dim;
}
function eCacheSqlPut($type, $sql, $script=""){
if( $type=="md5" ){
$pk = $_ENV[SYS]['contextMain'];
}else{
eSerialAdd();
$pk = $_ENV[SYS]['context'];
}
if( empty($pk) ) $pk = 1;
SS::insert("{$_ENV['SYSDB']}gs_context", [
"cd_gs_conexion"=> $_SESSION["_Connection_"],
"context"		=> $pk,
"type"			=> $type,
"script"		=> $script,
"data"			=> addslashes($sql)
]);
return $_ENV[SYS]['context'];
}
function eCacheSqlGet($type, $context){
if( !preg_match('/^[0-9]*$/u', $context) ){
_hackerLog("_CONTEXT no valido");
}
$r = SS::selectOne("select * from {$_ENV['SYSDB']}gs_context", [
"cd_gs_conexion"=> $_SESSION["_Connection_"],
"context"		=> $context,
"type"			=> $type
]);
return $r;
}
function eAddFilterGet($type, $addFilter){
list($context, $addFilter) = explode("|", $addFilter);
if( !preg_match('/^[0-9]*$/u', $context) ){
_hackerLog("_CONTEXT no valido");
}
$r = SS::selectOne("select * from {$_ENV['SYSDB']}gs_context", [
"cd_gs_conexion"=> $_SESSION["_Connection_"],
"context"		=> $context,
"type"			=> $type
]);
return $r["data"];
}
function _GeneraInputMD5($_DBRLOCK, $_Mode, $generar=true){
if( isset($_DBRLOCK) && $_DBRLOCK && ($_Mode=="mR" || $_Mode=="bR") ){
$md5 = $_DBRLOCK;
}else{
$md5 = $_ENV['_CONTEXT'].".".md5(SETUP::$System['EncryptionKey'].time());
eCacheSqlPut("md5", $md5, $_ENV[SYS]["Object"].":".$_ENV[SYS]["DF"]);
}
if( $generar ){
echo "<INPUT TYPE='hidden' NAME='_MD5' VALUE='{$md5}'>";
}
return $md5;
}
function eContext2FilePut($file, $xGet="", $title=""){
return;
$get = array("get"=>get2array($xGet), "title"=>$title);
$get = serialize($get);
$get = str_replace("'", "\\'", $get);
SS::insert("{$_ENV['SYSDB']}gs_context", [
"cd_gs_conexion"=> $_SESSION["_Connection_"],
"context"		=> $_ENV[SYS]['context'],
"type"			=> 'file',
"script"		=> $file,
"data"			=> $get
]);
return $_ENV[SYS]['context'].eContextAddUrl().eSessionAddUrl();
}
function eGetUrl($url){
return;
$pos = mb_strpos($url, ":");
if( $pos<4 ){
$url = "edes.php?".$url;
}
$obj = eMid($url, "?", ":");
if( $obj=="D" ){
eExplodeOne($url, ":", $no, $para);
$dim = get2array("_NO_=".$para);
$dim["_DOWN"] = "1";
if( empty($dim["_FILENAME"]) && !empty($dim["FILE"]) ) $dim["_FILENAME"] = $dim["FILE"];
unset($dim[""]);
$para = explode("&", $para);
$file = $para[0];
return "edes.php?D:".eContext2FilePut($file, $dim);
}
$url = eContextUrl($url);
return $url;
}
function eContext2FileGet($pk){
return;
if( !preg_match('/^[0-9]*$/u', $pk) ){
_hackerLog("_CONTEXT no valido");
}
$r = SS::selectOne("select * from {$_ENV['SYSDB']}gs_context", [
"cd_gs_conexion"=> $_SESSION["_Connection_"],
"context"		=> $pk,
"type"			=> 'file'
]);
if( $r["cd_gs_conexion"]!=$_SESSION["_Connection_"] ){
_hackerLog("Registro no encontrado");
}
$data = unserialize($r["data"]);
foreach($data["get"] as $k=>$v){
$_GET[$k] = $v;
}
return $r["script"];
}
function eContextUrl($url){
return;
if( eSubstrCount($url, "_CONTEXT=")==0 ){
$url .= ((eSubstrCount($url, "?"))? "&": "?").'_CONTEXT='.eContextPK();
}
return $url;
}
?>