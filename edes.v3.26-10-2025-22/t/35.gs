<?PHP
if( S::$_User==-1 || $_gsID!=getmypid() ) exit;
_HeaderAdd();
$_DirG = '';
include_once('../_d_/cfg/edes.ini');
include(DIREDES."gettable.php");
if( isset($_POST["DDBB"]) && isset($_POST["TABLE"]) ){
if( $_POST["DDBB"]!="" ){
$tmp2 = $_POST["DDBB"];
if( eSubstrCount(str_replace('\\','/',$tmp2),'/')==0 ) $tmp2 = '/_datos/config/'.$tmp2;
if( eSubstrCount($tmp2,'.')==0 ) $tmp2 .= '.ini';
}else{
$tmp2 = '/_datos/config/sql.ini/';
}
$_OtroDiccionario = true;
include( eScript($tmp2) );
InfoStructure($_POST["TABLE"]);
}
if( $_POST["script"]=="PHP" ){
if( trim($_POST["fuente"])<>"" && trim(file_get_contents("../_d_/usr/php.".S::$_User.".1"))<>trim($_POST["fuente"]) ){
$dir = "../_d_/usr";
if( is_dir($dir) ){
if( !is_readable($dir) ) die('ERROR: No es de lectura: '.$dir);
if( !is_writeable($dir) ) die('ERROR: No es de escritura: '.$dir);
}else{
if( !mkdir($dir, 0777) ) die('No se ha podido crear el directorio: '.$dir);
if( !is_dir($dir) )	die('No está el directorio: '.$dir);
if( !is_readable($dir) ) die('ERROR: No es de lectura: '.$dir);
if( !is_writeable($dir) ) die('ERROR: No es de escritura: '.$dir);
}
for($n=$_SESSION["_RecentFiles"]; $n>0; $n--){
$file = "../_d_/usr/php.".S::$_User.".{$n}";
if( file_exists($file) ){
if( $n==$_SESSION["_RecentFiles"] ) @unlink($file);
else @rename($file, "../_d_/usr/php.".S::$_User.".".($n+1));
}
}
file_put_contents("../_d_/usr/php.".S::$_User.".1", $_POST["fuente"]);
}
$_DirEDes = DIREDES;
$Fichero = '../_tmp/edes_php.'.$_User;
if( file_exists($Fichero) && !is_writable($Fichero) ){
echo "<script type='text/javascript'>top._PARENT.S.error(top,'El fichero &#34;".$Fichero."&#34; no tiene permiso de escritura');</script>";
eEnd();
}
$txt = $_POST["fuente"];
if( ltrim($txt)[0]=="." ){
$txt = "";
$dim = explode("\n", $_POST["fuente"]);
for($n=0; $n<count($dim); $n++){
if( ltrim($dim[$n])[0]!="." ){
$txt .= $dim[$n];
}
}
$_POST["fuente"] = $txt;
}
if( mb_strtoupper(mb_substr($txt,0,3))=='DB:' ){
$n = mb_strpos($txt, ';');
$i = mb_strpos($txt, CHR10);
if( $n>0 && $i>0 ) $n = min($n,$i);
$Comando = mb_substr($txt,0,$n);
$txt = mb_substr($txt,$n+1);
list(,$tmp2) = explode(':',$Comando);
$tmp2 = trim($tmp2);
if( eSubstrCount(str_replace('\\','/',$tmp2),'/')==0 ) $tmp2 = '/_datos/config/'.$tmp2;
if( eSubstrCount($tmp2,'.')==0 ) $tmp2 .= '.ini';
include( eScript($tmp2) );
if( eSubstrCount(',mysql,mysqli,informix,oracle,pdo,',"{$_Sql}," )==0 ) die("ERROR: Controlador '{$_Sql}' no implantado");
list( $_SqlPDOType ) = explode(':', $_SqlPDOConnect);
}else{
eval(qSetup());
}
if( ltrim($txt)[0]=="." ){
$txt = "";
$dim = explode("\n", $_POST["fuente"]);
for($n=0; $n<count($dim); $n++){
if( ltrim($dim[$n])[0]!="." ){
$txt .= $dim[$n];
}
}
$_POST["fuente"] = $txt;
}
$grabar = true;
$comando = "";
if( preg_match("/^SAVE:/iu", $txt) ){
$comando = "save";
$Dim = explode("\n",$txt);
if( mb_substr(trim($Dim[0]),-1)==';' ) $Dim[0] = mb_substr(trim($Dim[0]),0,-1);
$txt = mb_substr($txt,mb_strlen($Dim[0]));
$NomSave = $Dim[0];
while(eSubstrCount($NomSave, "  ")>0) $NomSave = str_replace("  "," ",$NomSave);
$NomSave = str_replace(" ","_",trim(mb_substr($NomSave,5)));
if( trim($txt)=="" ){
@unlink('../_d_/usr/'.$NomSave.'_php.'.$_User);
echo 'Fichero "'.$NomSave.'" borrado';
}else{
file_put_contents('../_d_/usr/'.$NomSave.'_php.'.$_User, $txt);
echo 'Fichero "'.$NomSave.'" grabado';
}
eEnd();
}elseif( preg_match("/^LOAD:/iu", $txt) ){
$comando = "load";
$Dim = explode("\n",$txt);
if( mb_substr(trim($Dim[0]),-1)==';' ) $Dim[0] = mb_substr(trim($Dim[0]),0,-1);
$txt = mb_substr($txt,mb_strlen($Dim[0]));
$NomSave = $Dim[0];
while(eSubstrCount($NomSave, "  ")>0) $NomSave = str_replace("  "," ",$NomSave);
$NomSave = str_replace(" ","_",trim(mb_substr($NomSave,5)));
$Fichero = '../_d_/usr/'.$NomSave.'_php.'.$_User;
$grabar = false;
$contenido = str_replace(
array(   '"'  ,   "'"   ,   '<'   ,   '>'   ,  '\\'   ,    CHR10 ,    CHR13 ),
array('#&#34;', '#&#39;', '#&#60;', '#&#62;', '#&#92;', "#&#chr10;", "#&#chr13;"),
file_get_contents($Fichero)
);
}elseif( preg_match("/^RM:/iu", $txt) ){
$comando = "rm";
$Dim = explode("\n",$txt);
if( mb_substr(trim($Dim[0]),-1)==';' ) $Dim[0] = mb_substr(trim($Dim[0]),0,-1);
$txt = mb_substr($txt,mb_strlen($Dim[0]));
$NomSave = $Dim[0];
while(eSubstrCount($NomSave, "  ")>0) $NomSave = str_replace("  "," ",$NomSave);
$NomSave = str_replace(" ","_",trim(mb_substr($NomSave,3)));
@unlink('../_d_/usr/'.$NomSave.'_php.'.$_User);
echo 'Fichero "'.$NomSave.'" borrado';
eEnd();
}elseif( preg_match("/^LS:/iu", $txt) ){
$comando = "ls";
$MisFiles = '_php.'.$_User;
$Long = mb_strlen($MisFiles)*-1;
$df = opendir('../_d_/usr/');
echo '<TABLE border=0 cellspacing=3 cellpadding=1>';
echo '<col><col style="text-align:right">';
echo '<TR><TH style="border-bottom:solid 1px #000000">FICHERO</TH><th style="width:10px"> </th><TH style="border-bottom:solid 1px #000000">FECHA</TH><th style="width:10px"> </th><TH style="border-bottom:solid 1px #000000">BYTS</TH></TR>';
while( $dir=readdir($df) ){
if( $dir!='.' && $dir!='..' && !is_dir($dir) && mb_substr($dir,$Long)==$MisFiles ){
$oDir = $dir;
$byts = filesize('../_d_/usr/'.$dir);
$dir = str_replace("_"," ",$dir);
echo '<TR><TD>'.mb_substr($dir,0,$Long).'</TD><td></td><TD>'.date('Y-m-d H:i:s', filemtime('../_d_/usr/'.$oDir)).'</TD><td></td><TD style="text-align:right">'.eNumberFormat($byts).'</TD></TR>';
}
}
closedir($df);
echo '</TABLE>';
closedir($df);
eEnd();
}
if( $grabar ){
file_put_contents($Fichero, $txt);
}
eHTML('', '', 'Interprete-PHP');
?>
<style>
BODY::-webkit-scrollbar {
-webkit-appearance: none;
width: 12px;
height:12px;
}
BODY::-webkit-scrollbar-thumb {
border-radius: 4px;
background-color: #7f7f7f;
}
</style>
<?PHP
echo '<SCRIPT type="text/javascript">';
echo 'document.onkeydown = function anonymous(){ if(event.keyCode==121)top.Ver("iPHP"); }'.CHR10;
if( $contenido<>"" ) echo 'top.top.loadPHP("'.$contenido.'");';
echo '</SCRIPT></HEAD><BODY style="margin: 0px 0px 0px 5px" onload="document.body.scrollTop=\''.($TOP*1).'px\';';
echo '">';
echo '<script>var _dimPHP = [["-Últimos PHP"]';
for($n=1; $n<=$_SESSION["_RecentFiles"]; $n++){
$fileSQL = "../_d_/usr/php.".S::$_User.".{$n}";
if( file_exists($fileSQL) ){
$i = date('Y-m-d H:i:s', filemtime($fileSQL));
$txt = str_replace(
array(  '"'  ,   "'"  ,  '\\'  ,   '/'  , CHR10 , CHR13),
array('&#34;', '&#39;', '&#92;', '&#47;', '&#10#;', '&#13#;'),
file_get_contents($fileSQL)
);
echo ",['{$i}','','','{$txt}']";
}
}
echo "];\n";
?>
for(var n=1; n<_dimPHP.length; n++) _dimPHP[n][3] = _dimPHP[n][3].replace(/&#10#;/g, String.fromCharCode(10)).replace(/&#13#;/g, String.fromCharCode(13)).replace(/&#47#;/g,'/');
top._dimPHP = _dimPHP;
</script>
<?PHP
if( $comando=="load" ){
readfile($Fichero);
}else{
include($Fichero);
}
echo '</BODY></HTML>';
eEnd();
}
if( isset($VerSEL) ){
VerSelect($VerSEL);
}
if( trim($Todo)=='select * from' ) exit;
$Fichero = '../_tmp/edes_sql.'.$_User;
if( $NOMSQLHTM!='' ){
$Fichero = '../_tmp/'.$NOMSQLHTM.'.'.$_User;
if( file_exists($Fichero) ) copy( $Fichero, '../_bak_/'.$NOMSQLHTM.'.'.$_User.'.'.date('w') );
}else{
if( file_exists($Fichero) ) copy( $Fichero, '../_bak_/edes_sql.'.$_User.'.'.date('w') );
}
$pnt = fopen($Fichero, 'w');
if( !$pnt ) die('No se ha podido ejecutar');
fputs($pnt, $Todo);
fclose($pnt);
if( $_SESSION["_gsACCESO"]['LOGEAR']>0 ) gsLogear('SQL','I', $Fichero.' '.filesize($Fichero));
$FUENTE = str_replace(CHR13, '' , $Todo);
$Dim = explode("\n", $FUENTE);
if( isset($_GET["_nLinea"]) ){
$Dim = array($Dim[$_GET["_nLinea"]]);
}
if( trim($FUENTE)!='.' ){
for($n=0; $n<count($Dim); $n++){
$Dim[$n] = trim($Dim[$n]);
if( $Dim[$n][0]=='.' ){
$Dim[$n] = '';
}else if( mb_substr($Dim[$n],0,2)==REM ){
$Dim[$n] = '';
}else if( mb_substr($Dim[$n],0,2)=='/'.'*' ){
for($i=$n; $i<count($Dim); $i++){
if( mb_substr($Dim[$i],0,2)=='*'.'/' ){
$Dim[$i] = '';
break;
}else{
$Dim[$i] = '';
}
}
}else if( mb_strtoupper(mb_substr($Dim[$n],0,6))=='[NOTE]' ){
for($i=$n; $i<count($Dim); $i++) $Dim[$i] = '';
}
}
}
$FUENTE = '';
for($n=0; $n<count($Dim); $n++) if( $Dim[$n]!='' ) $FUENTE .= $Dim[$n].' ';
$_ExeSQL = false;
if( mb_strtoupper(trim($FUENTE))=='EXESQL' ){
$DimCDI = array();
$_ExeSQL = true;
$FUENTE = '';
$CDI = file('../_datos/config/sql.cdi');
$CDI = trim($CDI[0]);
$Dim = file('../_datos/config/sql.log');
for( $n=0; $n<count($Dim); $n++ ) if( $Dim[$n]!='' ){
$oCDI = trim(mb_substr( $Dim[$n], 0, 19 ));
$txt = trim(mb_substr( $Dim[$n], 20 ));
if( $oCDI > $CDI ){
if( $FUENTE!='' ) $FUENTE .= ';';
$FUENTE .= $txt.' ';
$DimCDI[] = $oCDI;
}
}
}
if( $FUENTE!='' ){
$comando = $Dim[0];
if(		 preg_match("/^SAVE:/iu", $comando) ){
if( mb_substr(trim($comando),-1)==';' ) $comando = mb_substr(trim($comando),0,-1);
$txt = mb_substr($FUENTE, mb_strlen($Dim[0])+1);
$NomSave = $comando;
while(eSubstrCount($NomSave, "  ")>0) $NomSave = str_replace("  "," ",$NomSave);
$NomSave = str_replace(" ","_",trim(mb_substr($NomSave,5)));
if( trim($txt)=="" ){
@unlink('../_d_/usr/'.$NomSave.'_sql.'.$_User);
echo 'Fichero "'.$NomSave.'" borrado';
}else{
file_put_contents('../_d_/usr/'.$NomSave.'_sql.'.$_User, $txt);
echo 'Fichero "'.$NomSave.'" grabado';
}
eEnd();
}elseif( preg_match("/^LOAD:/iu", $comando) ){
if( mb_substr(trim($comando),-1)==';' ) $comando = mb_substr(trim($comando),0,-1);
$txt = mb_substr($FUENTE, mb_strlen($Dim[0])+1);
$NomSave = $comando;
while(eSubstrCount($NomSave, "  ")>0) $NomSave = str_replace("  "," ",$NomSave);
$NomSave = str_replace(" ","_",trim(mb_substr($NomSave,5)));
$Fichero = '../_d_/usr/'.$NomSave.'_sql.'.$_User;
eJS("window.frameElement.WOPENER.loadExt('/_d_/usr/{$Fichero}');");
eEnd();
}elseif( preg_match("/^RM:/iu"  , $comando) ){
if( mb_substr(trim($Dim[0]),-1)==';' ) $Dim[0] = mb_substr(trim($Dim[0]),0,-1);
$txt = mb_substr($FUENTE, mb_strlen($Dim[0]));
$NomSave = $comando;
while(eSubstrCount($NomSave, "  ")>0) $NomSave = str_replace("  "," ",$NomSave);
$NomSave = str_replace(" ","_",trim(mb_substr($NomSave,3)));
@unlink('../_d_/usr/'.$NomSave.'_sql.'.$_User);
echo 'Fichero "'.$NomSave.'" borrado';
eEnd();
}elseif( preg_match("/^LS:/iu"  , $comando) ){
$MisFiles = '_sql.'.$_User;
$Long = mb_strlen($MisFiles)*-1;
$df = opendir('../_d_/usr/');
echo '<TABLE border=0 cellspacing=3 cellpadding=1>';
echo '<col><col style="text-align:right">';
echo '<TR><TH style="border-bottom:solid 1px #000000">FICHERO</TH><th style="width:10px"> </th><TH style="border-bottom:solid 1px #000000">FECHA</TH><th style="width:10px"> </th><TH style="border-bottom:solid 1px #000000">BYTS</TH></TR>';
while( $dir=readdir($df) ){
if( $dir!='.' && $dir!='..' && !is_dir($dir) && mb_substr($dir,$Long)==$MisFiles ){
$oDir = $dir;
$byts = filesize('../_d_/usr/'.$dir);
$dir = str_replace("_"," ",$dir);
echo '<TR><TD>'.mb_substr($dir,0,$Long).'</TD><td></td><TD>'.date('Y-m-d H:i:s', filemtime('../_d_/usr/'.$oDir)).'</TD><td></td><TD style="text-align:right">'.eNumberFormat($byts).'</TD></TR>';
}
}
closedir($df);
echo '</TABLE>';
closedir($df);
eEnd();
}
if( trim($Todo)!='' && trim(file_get_contents("../_d_/usr/sql.".S::$_User.".1"))<>trim($Todo) ){
$dir = "../_d_/usr";
if( is_dir($dir) ){
if( !is_readable($dir) ) die('ERROR: No es de lectura: '.$dir);
if( !is_writeable($dir) ) die('ERROR: No es de escritura: '.$dir);
}else{
if( !mkdir($dir, 0777) ) die('No se ha podido crear el directorio: '.$dir);
if( !is_dir($dir) )	die('No está el directorio: '.$dir);
if( !is_readable($dir) ) die('ERROR: No es de lectura: '.$dir);
if( !is_writeable($dir) ) die('ERROR: No es de escritura: '.$dir);
}
for($n=$_SESSION["_RecentFiles"]; $n>0; $n--){
$file = "../_d_/usr/sql.".S::$_User.".{$n}";
if( file_exists($file) ){
if( $n==$_SESSION["_RecentFiles"] ) @unlink($file);
else @rename($file, "../_d_/usr/sql.".S::$_User.".".($n+1));
}
}
file_put_contents("../_d_/usr/sql.".S::$_User.".1", $Todo);
}
$exe_sql = str_replace("\t", ' ', rtrim($FUENTE));
$exe_sql = stripslashes($exe_sql);
$tmp = split_sql($exe_sql);
$ExeSQL = stripslashes($tmp[0]);
}
header('Content-Type: text/html; charset=utf-8');
?>
<HTML><HEAD>
<META http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<title>gsTable</title>
<style type="text/css">
BODY {
color: #000099;
background: #FF0000;
margin: 5px 5px 5px 5px;
cursor: default;
}
TABLE, TD {
background-color: #000099;
}
TH {
color: #000099;
background-color: #D3DCE3;
}
TD {
background-color: #F6F8F9;
WHITE-SPACE: nowrap;
}
<?PHP
$txt = file_get_contents("{$_PathCSS}/all.css");
$i = mb_strpos($txt, "/"."* CSSADD:INI *"."/");
$f = mb_strpos($txt, "/"."* CSSADD:END *"."/");
if( $i>0 && $f>$i ){
$txt = mb_substr($txt,0,$i).mb_substr($txt,$f+16);
}
echo str_replace("../fonts/","fonts/",$txt);
unset($txt);
$txt = file_get_contents("{$_PathCSS}/list.css");
echo $txt;
?>
TABLE, TH, TD {
font-size: 13px;
}
BODY::-webkit-scrollbar {
-webkit-appearance: none;
width: 12px;
height:12px;
}
BODY::-webkit-scrollbar-thumb {
border-radius: 4px;
background-color: #7f7f7f;
}
.FONTMONOSPACE {
font-family:monospace;
}
.FONTMONOSPACE B {
font-family:monospace;
}
</style>
<style type="text/css" n_ame="35">
.alignLeft {
text-align:left;
}
.alignRight {
text-align:right;
}
.alignCenter {
text-align:center;
}
</style>
<SCRIPT type="text/javascript">
<?PHP
echo 'var _dimSQL = [["-Últimos SQL"]';
for($n=1; $n<=$_SESSION["_RecentFiles"]; $n++){
$fileSQL = "../_d_/usr/sql.".S::$_User.".{$n}";
if( file_exists($fileSQL) ){
$i = date('Y-m-d H:i:s', filemtime($fileSQL));
$txt = str_replace(
array(  '"'  ,   "'"  ,  '\\'  ,   '/'  , CHR10 , CHR13),
array('&#34;', '&#39;', '&#92;', '&#47;', '&#10#;', '&#13#;'),
file_get_contents($fileSQL)
);
echo ",['{$i}','','','{$txt}']";
}
}
echo "];\n";
?>
for(var n=1; n<_dimSQL.length; n++) _dimSQL[n][3] = _dimSQL[n][3].replace(/&#10#;/g, String.fromCharCode(10)).replace(/&#13#;/g, String.fromCharCode(13)).replace(/&#47#;/g,'/');
top._dimSQL = _dimSQL;
<?PHP
if( !$_SESSION["_BYPHONE"] ){
echo 'top.S.init(window,"all,list");';
}else{
?>
top.window.opener.S.init(window);
var oStyle = top.window.opener.document.styleSheets, r,i,t,reglas, sheet=S.sheet(window);
for(r=0; r<oStyle.length; r++){
if( oStyle[r].title=="all" ){
reglas = oStyle[r].rules
t = reglas.length;
for(i=0; i<t; i++){
if( /^@font-face /.test(reglas[i].cssText) ){
sheet.insertRule(reglas[i].cssText, sheet.cssRules.length);
}
}
}
}
<?PHP
}
?>
function ScrollTH(){
return;
if( null==TBROWSE ) return;
TBROWSE.rows[0].style.top = (document.body.scrollTop==0) ? 0 : document.body.scrollTop-1;
}
var _NOSORT = false;
function Sort(Obj){
top.opener.SortTabla(window, event.target || event.srcElement);
}
function ListadoEnFicha(){
var oTH = top.S.event(window),
Obj = top.S.toTag(oTH, "TABLE");
if( Obj==null ) return;
if( Obj.rows[0].cells.length==2 && Obj.rows[0].cells[1].tagName=='TD' ){
var t = Obj.rows.length,
tr1 = Obj.insertRow(0),
tr0 = Obj.insertRow(0), i;
for(i=0; i<t; i++){
xOldAlign = Obj.rows[i+2].cells[1].OldAlign;
tr1.appendChild(Obj.rows[i+2].cells[1]);
tr0.appendChild(Obj.rows[i+2].cells[0]);
tr0.cells[i].style.textAlign = 'center';
tr1.cells[i].style.textAlign = xOldAlign;
}
for(i=0; i<t; i++) Obj.deleteRow(2);
return;
}
if( Obj.rows.length!=2 ){
var oTR = top.S.event(window);
if( oTR.tagName=="TD" ) oTR = oTR.parentNode;
oTR.style.fontStyle = "italic";
oTR.style.color = "#a9a9a9";
return;
}
var tr, t = Obj.rows[0].cells.length, i;
for(i=t-1; i>0; i--){
tr = Obj.insertRow(2);
tr.appendChild(Obj.rows[0].cells[i]);
tr.appendChild(Obj.rows[1].cells[i]);
tr.cells[0].style.cursor = 'pointer';
tr.cells[0].style.textAlign = tr.cells[1].style.textAlign = 'left';
}
Obj.rows[0].cells[0].style.cursor = 'pointer';
Obj.rows[0].cells[0].style.textAlign = 'left';
Obj.rows[0].appendChild(Obj.rows[1].cells[i]);
Obj.rows[0].cells[1].style.textAlign = 'left';
Obj.deleteRow(1);
}
function PutHand(){
document.body.style.backgroundColor = '#F5F5F5';
S(window.frameElement).attr("eWINDOW", window);
}
function VerFuente(){
if( top.name=='gsEdit' && event.keyCode==121 ) top.Ver('iSQL', 0);
}
document.onkeydown = VerFuente;
function copyBody(){
var txt = S.replace(S("#BROWSE").obj.parentNode.innerHTML, [
["<tr><td", "\n<tr><td"],
["<table ", "\n<table "],
["</table>", "\n</table>\n"],
["<br>Ok<br>", "\n<br>Ok<br>\n"]
]);
if( event.altKey ) txt = S.replace(S.clearTags(S.replace(txt, "</td>", ",</td>", "</th>", ",</th>")), ",\n", "\n");
S.clipboardPut(txt);
S.info("Contenido copiado", 3);
}
function init(){
S("TABLE[id='BROWSE'").each(function(k,o){
o.onclick = ListadoEnFicha;
});
<?PHP if( SETUP::$System['Call3CX'] ){ ?>
setTimeout(function(){
S("TCXSPAN").each(function(k,o){
o.outerHTML = o.innerText;
});
}, 100);
<?PHP } ?>
}
</SCRIPT>
</HEAD>
<BODY scroll=auto onscroll=ScrollTH() onload="PutHand();top.S.infoHide(top);init();" oncontextmenu='return false' ondblclick='copyBody()'>
<?PHP
$_ToExcel = false;
$_ToPDF = false;
$_ToXML = false;
$_ToTXT = false;
$_ToCSV = false;
$_TH = array();
$_EditStructure = '';
$_MemCursor = false;
$_DirEDes = $Dir_;
$_Condition = array();
$_Margin = array();
$Limit = '';
if( $FUENTE!='' ){
$IniSQL = true;
for($w=0; $w<count($tmp); $w++){
$ExeSQL = trim(stripslashes($tmp[$w]));
if( preg_match("/^DB:/iu", $ExeSQL) ){
$IniSQL = false;
break;
}
}
if( $IniSQL ){
}
$_Variable = array();
$_Trace = true;
$totalSentencias = 0;
for($w=0; $w<count($tmp); $w++){
$_SaveSQL = false;
$_SaveVersionSQL = false;
$_SQLUpdate = false;
$ExeSQL = trim(stripslashes($tmp[$w]));
if( $ExeSQL=="!" || $ExeSQL=="SYS"  ) $ExeSQL = 'SYS';
if( $ExeSQL=="." || $ExeSQL=="SETUP") $ExeSQL = 'SETUP';
if( $ExeSQL==":" || $ExeSQL=="SESS" ) $ExeSQL = 'SESS';
if( mb_substr($ExeSQL,-1)==';' ) $ExeSQL = trim(mb_substr($ExeSQL,0,-1));
$SinLimit = ($ExeSQL[0]=='=' && $ExeSQL<>"=");
if( $SinLimit ){
$ExeSQL = trim(mb_substr($ExeSQL,1));
$GLOBALS['_DBLimit'] = 999999999;
}
if( $ExeSQL=='' ) continue;
if( $w>0 && !preg_match("/^(TH:|:|-|=|#|NOTRACE|NOSHOW)/iu", $ExeSQL) && (!$_Trace && $ExeSQL[0]!='$') ) echo '<br>';
if( count($tmp)>1 ){
foreach($_Variable as $key=>$value){
if( is_array($value) ){
foreach($value as $k2=>$v2){
if( eSubstrCount($ExeSQL,'{'.$key.'['.$k2.']}'  )>0 ) $ExeSQL = str_replace('{'.$key.'['.$k2.']}'  , $v2, $ExeSQL);
if( eSubstrCount($ExeSQL,'{'.$key.'["'.$k2.'"]}')>0 ) $ExeSQL = str_replace('{'.$key.'["'.$k2.'"]}', $v2, $ExeSQL);
if( eSubstrCount($ExeSQL,'{'.$key."['".$k2."']}")>0 ) $ExeSQL = str_replace('{'.$key."['".$k2."']}", $v2, $ExeSQL);
}
}else{
if( $value[0]=='$' ){
$value = eval("return {$value};");
}
if( eSubstrCount($ExeSQL,'{'.$key.'}')>0 ) $ExeSQL = str_replace('{'.$key.'}', $value, $ExeSQL);
}
}
}
if( $ExeSQL=='-' ){
echo '<HR style="height:1px; border:0px; background:#000099">';
continue;
}else if( $ExeSQL=='=' ){
echo '<HR style="height:2px; border:0px; background:#000099">';
continue;
}else if( $ExeSQL=='#' ){
echo '<HR style="height:3px; border:0px; background:#000099">';
continue;
}else if( preg_match("/^(NOTRACE|NOSHOW)$/iu", $ExeSQL) ){
$_Trace = false;
continue;
}else if( $ExeSQL[0]==":" ){
echo "<b>".mb_substr($ExeSQL, 1)."</b>";
continue;
}else if( $ExeSQL[0]=='*' ){
$ExeSQL = "*".trim(mb_substr($ExeSQL,1));
}else if( preg_match("/^print:/iu", $ExeSQL) ){
echo "<b><u>".mb_substr($ExeSQL,6)."</u></b><br>";
continue;
}
$ExeSQL = trim($ExeSQL);
if( preg_match("/^PHISHING /iu", $ExeSQL) ){
list($ExeSQL, $NewEMail) = explode(" ", trim(mb_substr($ExeSQL,8)));
$ExeSQL = str_replace([".", ","], "", $ExeSQL);
SS::query("select login,pass,user_name,user_surname from {$_ENV['SYSDB']}gs_user where cd_gs_user={$ExeSQL}");
list($xLogin, $xPass, $xName, $xSubName) = SS::get("num");
$xLogin = trim($xLogin);
$xPass = trim($xPass);
if( $xLogin!='' && $xPass!='' ){
SS::query("select login from {$_ENV['SYSDB']}gs_user where cd_gs_user={$_User}");
list($xNomFile) = SS::get("num");
$xNomFile = trim($xNomFile);
if( $NewEMail!='' ) $xNomFile = $NewEMail;
$xNomFile = '../_datos/usr/'.$xNomFile;
if( file_exists($xNomFile) ) unlink($xNomFile);
error_log('<'.'?PHP'."\n", 3, $xNomFile);
error_log('$xUsuario = "'.$xLogin.'";'						 ."\n", 3, $xNomFile);
error_log('$xClave = "'.$xPass.'";'							 ."\n", 3, $xNomFile);
error_log('$Key = "'.mb_strtoupper(md5(date('d-m-Y H'))).'";'."\n", 3, $xNomFile);
error_log('?'.'>'."\n", 3, $xNomFile);
echo '<span style="color:red">OK: PHISHING '.$ExeSQL.": ".trim($xName).' '.trim($xSubName)."</span><br>\n";
}
continue;
}
if(		  preg_match("/^EXCEL:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,6));
$_ToExcel = true;
}else if( preg_match("/^XLS:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToExcel = true;
}else if( preg_match("/^XML:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToXML = true;
}else if( preg_match("/^CSV:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToCSV = true;
}else if( preg_match("/^TXT:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToTXT = true;
}else if( preg_match("/^PDF:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToPDF = true;
}
if( eSubstrCount($ExeSQL, " ")==1 ){
list($t,$n) = explode(" ",$ExeSQL);
if( ((int)$n)>0 ) $ExeSQL = "select * from {$t} limit {$n}";
}
$GLOBALS['_DEBUG'] = -1;
if( preg_match("/^TH:/iu", $ExeSQL) ){
$_TH = explode(',',mb_substr($ExeSQL,3));
continue;
}
$CalculaTiempo = false;
$NumeroDeTest = 1;
if( preg_match("/^TIME:/iu", $ExeSQL) || preg_match("/^TIME,/iu", $ExeSQL) ){
$CalculaTiempo = true;
if( preg_match("/^TIME,/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,5));
$n = mb_strpos( $ExeSQL, ':' );
$NumeroDeTest = mb_substr($ExeSQL,0,$n)*1;
$ExeSQL = trim(mb_substr($ExeSQL,$n+1));
}else{
$ExeSQL = trim(mb_substr($ExeSQL,5));
$NumeroDeTest = 1;
}
}
if( $totalSentencias > 0 ){
echo "<br><br>";
}
if( $_Trace && (count($tmp)>1 || $CalculaTiempo) ){
echo '<b>'.$ExeSQL.'</b><br>';
}
$totalSentencias++;
$_VerBlob = false;
$_WidthBlob = 400;
if( preg_match("/^BLOB:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,5));
$_VerBlob = true;
}else if( preg_match("/^BLOB,/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,5));
$_WidthBlob = (int)mb_substr( $ExeSQL,0,mb_strpos($ExeSQL,':') );
$ExeSQL = trim(mb_substr( $ExeSQL,mb_strpos($ExeSQL,':')+1 ));
$_VerBlob = true;
}
if( $ExeSQL==((int)$ExeSQL.'') ){
$ExeSQL = "select * from {$_ENV['SYSDB']}gs_user where cd_gs_user={$ExeSQL}";
}
if( preg_match("/^VERSION:/iu", $ExeSQL) && $_SESSION["_D_"]=="~" ){
$_SaveVersionSQL = true;
$ExeSQL = trim(mb_substr($ExeSQL,8));
}
if( preg_match("/^SAVESQL:/iu", $ExeSQL) ){
$_SaveSQL = true;
$ExeSQL = trim(mb_substr($ExeSQL,8));
}
if( preg_match("/^SHOW FIELDS FROM /iu", $ExeSQL) || preg_match("/^SELECT /iu", $ExeSQL) || ( preg_match("/SELECT /iu",$ExeSQL) && (preg_match("/^EXCEL:/iu",$ExeSQL) || preg_match("/^XLS:/iu",$ExeSQL) || preg_match("/^PDF:/iu",$ExeSQL) || preg_match("/^XML:/iu",$ExeSQL) || preg_match("/^CSV:/iu",$ExeSQL) || preg_match("/^TXT:/iu",$ExeSQL)) ) ){
if( preg_match("/^SHOW FIELDS FROM /iu", $ExeSQL) ){
if( SS::isDriver("mysql") || SS::isDriver("mysqli") ){
$SinLimit = true;
$_VerBlob = true;
}else{
die("ERROR comando no soportado");
}
}
if(		  preg_match("/^EXCEL:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,6));
$_ToExcel = true;
}else if( preg_match("/^XLS:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToExcel = true;
}else if( preg_match("/^XML:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToXML = true;
}else if( preg_match("/^CSV:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToCSV = true;
}else if( preg_match("/^TXT:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToTXT = true;
}else if( preg_match("/^PDF:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToPDF = true;
}
$NomTabla = "";
if( $_ToExcel || $_ToExcel || $_ToXML || $_ToCSV || $_ToTXT || $_ToPDF ){
$NomTabla = $ExeSQL;
}
if( !$SinLimit ){
if( SS::isDriver("informix") ){
if( preg_match("/ LIMIT /iu", $ExeSQL) ){
$n = mb_strpos(mb_strtoupper($ExeSQL), ' LIMIT ');
$Limit  = mb_substr( $ExeSQL, $n+7 );
$ExeSQL = mb_substr( $ExeSQL, 0, $n );
list($Desde, $RegAVer) = explode(',', $Limit);
if( $RegAVer!='' ){
$Hasta = $Desde+$RegAVer;
}else{
$Hasta = $Limit;
}
$ExeSQL = 'select first '.$Hasta.' '.mb_substr($ExeSQL,7);
}else{
if( !preg_match("/ FIRST /iu",$ExeSQL) && !preg_match("/SELECT COUNT\(\*\)/iu",$ExeSQL) ) $ExeSQL = 'select first '.$GLOBALS['_DBLimit'].' '.mb_substr($ExeSQL,7);
}
}else if( DB::isDriver("oci") ){
if( !preg_match("/ ROWNUM</iu", $ExeSQL) && !preg_match("/SELECT COUNT\(\*\)/iu", $ExeSQL) ){
$ExeSQL = str_replace( ' where ', ' WHERE ', $ExeSQL );
$ExeSQL = str_replace( ' group by ', ' GROUP BY ', $ExeSQL );
$ExeSQL = str_replace( ' having ', ' HAVING ', $ExeSQL );
$ExeSQL = str_replace( ' order by ', ' ORDER BY ', $ExeSQL );
if( preg_match("/ WHERE /iu", $ExeSQL) ){
$ExeSQL = str_replace( ' where ', ' where ROWNUM<'.$GLOBALS['_DBLimit'].' and ', $ExeSQL );
}else{
if( preg_match("/ GROUP BY /iu", $ExeSQL) ){
$ExeSQL = str_replace( ' GROUP BY ', ' WHERE ROWNUM<'.$GLOBALS['_DBLimit'].' GROUP BY ', $ExeSQL );
}else if( preg_match("/ HAVING /iu", $ExeSQL) ){
$ExeSQL = str_replace( ' HAVING ', ' WHERE ROWNUM<'.$GLOBALS['_DBLimit'].' HAVING ', $ExeSQL );
}else if( preg_match("/ ORDER BY /iu", $ExeSQL) ){
$ExeSQL = str_replace( ' ORDER BY ', ' WHERE ROWNUM<'.$GLOBALS['_DBLimit'].' ORDER BY ', $ExeSQL );
}else{
$ExeSQL .= ' where ROWNUM<'.$GLOBALS['_DBLimit'];
}
}
}
}else if( !preg_match("/ LIMIT /iu", $ExeSQL) && !preg_match("/SELECT COUNT\(\*\)/iu", $ExeSQL) ){
$ExeSQL .= ' limit '.$GLOBALS['_DBLimit'];
}
}
if( $_MemCursor ){
DB::query($ExeSQL);
$_Variable[$Nombre] = DB::get();
$_MemCursor = false;
}
if( !$CalculaTiempo ){
SS::query($ExeSQL);
}else{
$_TiempoInicial = getMicrotime();
for($n=0; $n<$NumeroDeTest; $n++) SS::query($ExeSQL);
echo getMicrotime() - $_TiempoInicial.' sg';
}
ListaFields($_Result, $Limit, $NomTabla);
$_TH = array();
echo "<br>";
}else if( preg_match("/^UPDATE /iu", $ExeSQL) || preg_match("/^DELETE /iu", $ExeSQL) || preg_match("/^INSERT /iu", $ExeSQL) ){
$_SQLUpdate = true;
SS::query( $ExeSQL );
echo 'Ok<br>';
}else if( preg_match("/^CREATE /iu", $ExeSQL) || preg_match("/^DROP /iu", $ExeSQL) || preg_match("/^ALTER /iu", $ExeSQL) || preg_match("/^RENAME /iu", $ExeSQL) ){
set_time_limit( 0 );
$_SQLUpdate = true;
if( DB::isDriver("oci") && preg_match("/^CREATE /u", $ExeSQL) && eSubstrCount( mb_strtoupper($ExeSQL), ' SERIAL' ) > 0 ){
CreaSerial( $ExeSQL );
}
if( SS::isDriver('mysql,mysqli') && preg_match("/^DROP /iu", $ExeSQL) && !preg_match("/ IF EXISTS /iu", $ExeSQL) ) $ExeSQL = 'DROP TABLE IF EXISTS '.mb_substr($ExeSQL,mb_strrpos($ExeSQL,' ')+1);
SS::query( $ExeSQL );
echo 'Ok<br>';
}else if( preg_match("/^TABLES/iu", $ExeSQL) || (preg_match("/TABLES /iu",$ExeSQL) && (preg_match("/^EXCEL:/iu",$ExeSQL) || preg_match("/^XLS:/iu",$ExeSQL) || preg_match("/^PDF:/iu",$ExeSQL) || preg_match("/^XML:/iu",$ExeSQL) || preg_match("/^CSV:/iu",$ExeSQL) || preg_match("/^TXT:/iu",$ExeSQL))) ){
if(		  preg_match("/^EXCEL:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,6));
$_ToExcel = true;
}else if( preg_match("/^XLS:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToExcel = true;
}else if( preg_match("/^XML:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToXML = true;
}else if( preg_match("/^CSV:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToCSV = true;
}else if( preg_match("/^TXT:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToTXT = true;
}else if( preg_match("/^PDF:/iu", $ExeSQL) ){
$ExeSQL = trim(mb_substr($ExeSQL,4));
$_ToPDF = true;
}
ListaTablas($_Result, $ExeSQL, count($tmp));
}else if( preg_match("/^INFO/iu", $ExeSQL) ){
$sExeSQL = str_replace('  ',' ',$ExeSQL);
$sExeSQL = mb_substr($sExeSQL, 4);
$sExeSQL = str_replace(' ','',$sExeSQL);
$TmpSql = explode(',',$sExeSQL);
for($i=0; $i<count($TmpSql); $i++){
if( mb_substr($TmpSql[$i],-1)==';' ) $TmpSql[$i] = trim(mb_substr($TmpSql[$i],0,-1));
if( $i>0 ) echo '<br><br>';
$dim = DBTable::info($TmpSql[$i]);
echo "<table>";
foreach($dim as $key=>$value){
echo "<tr><th class='alignLeft'>{$key}</th><td>{$value}</td></tr>";
}
echo "</table>";
}
}else if( preg_match("/^STRUCTURE/iu", $ExeSQL) || preg_match("/^EDIT STRUCTURE/iu", $ExeSQL) ){
$sExeSQL = str_replace('  ', ' ', $ExeSQL);
if( preg_match("/^EDIT STRUCTURE/iu", $ExeSQL) ){
$sExeSQL = mb_substr($sExeSQL, 5);
}
$sExeSQL = mb_substr($sExeSQL, 9);
DBTable::viewStructureTables($sExeSQL);
}else if( "STATUS"==mb_strtoupper($ExeSQL) ){
if( SS::isDriver('mysql,mysqli') ) echo StatusMySql();
}else if( preg_match("/^EXPLAIN /iu", $ExeSQL) ){
if( SS::isDriver('mysql,mysqli') ) echo ExplainMySql($ExeSQL);
}else if( preg_match("/^IMPORT:/iu", $ExeSQL) ){
$ok = false;
$totalRec = 0;
for($i=0; $i<count($Dim); $i++){
$txt = trim($Dim[$i]);
if( empty($txt) ) continue;
if( !$ok && preg_match("/^IMPORT:/iu", $txt) ){
$ok = true;
$table = trim(mb_substr($txt,7));
if( empty($table) ){
die("ERROR: need to define table name");
}
continue;
}
$txt = htmlentities($txt);
$sql = "insert into {$table} values (null, '{$txt}')";
SS::query($sql);
$totalRec++;
}
echo "<b>{$totalRec}</b> records have been inserted in <b>{$table}</b>";
eEnd();
}else if( preg_match("/^IMPORT /iu", $ExeSQL) ){
eImport($ExeSQL);
}else if( preg_match("/^PHPINFO/iu", $ExeSQL) || preg_match("/^PHPINFO\(\)/iu", $ExeSQL) ){
eInit();
phpInfo();
eEnd();
}else if( $ExeSQL[0]=='$' && count($tmp)==1 && !preg_match("/SELECT /iu", $ExeSQL)){
$tmp2 = explode(",",str_replace("  ","",$ExeSQL));
for($n=0; $n<count($tmp2); $n++){
$tmp2[$n] = eval("return({$tmp2[$n]});");
}
switch( count($tmp2) ){
case 1: ePrintR($tmp2[0]); break;
case 2: ePrintR($tmp2[0], $tmp2[1]); break;
case 3: ePrintR($tmp2[0], $tmp2[1], $tmp2[2]); break;
case 4: ePrintR($tmp2[0], $tmp2[1], $tmp2[2], $tmp2[3]); break;
case 5: ePrintR($tmp2[0], $tmp2[1], $tmp2[2], $tmp2[3], $tmp2[4]); break;
default: ePrintR("MAXIMO 5 VARIABLES", $tmp2[0], $tmp2[1], $tmp2[2], $tmp2[3], $tmp2[4]);
}
}else if( "HELP"==mb_strtoupper($ExeSQL) || "?"==mb_strtoupper($ExeSQL) ){
Help( $_Sql );
}else if( "HELP SQL"==mb_strtoupper($ExeSQL) ){
$sSql = $_Sql;
if( $sSql=="mysqli" ) $sSql = "mysql";
include($Dir_.'h/x_'.$sSql.'.htm');
}else if( preg_match("/^HELP /iu", $ExeSQL) ){
$sExeSQL = str_replace('  ',' ',$ExeSQL);
$TmpSql = explode(' ',$sExeSQL);
if( $TmpSql[1]=="mysqli" ) $TmpSql[1] = "mysql";
include($Dir_.'h/x_'.$TmpSql[1].'.htm');
}else if( preg_match("/^FIELDS/iu", $ExeSQL) ){
$ListaFields = str_replace(' ','',mb_substr($ExeSQL,6));
if( $ListaFields!='' ){
$ListaFields = '"'.str_replace(',','","',$ListaFields).'"';
}
if( SS::isDriver("informix") ) CamposInformix( $ListaFields );
if( DB::isDriver("oci") ) CamposOracle( $ListaFields );
if( SS::isDriver('mysql,mysqli') ) CamposMySql( $ListaFields );
}else if( preg_match("/^eUpper\(/iu",$ExeSQL) || preg_match("/eLower\(/iu",$ExeSQL) || preg_match("/eUpperLower\(/iu",$ExeSQL) || preg_match("/eChange\(/iu",$ExeSQL) ){
$_SQLUpdate = true;
ExeFuncion( $ExeSQL );
}else if( preg_match("/eMaxLeng\(/iu",$ExeSQL) || preg_match("/eChr\(/iu",$ExeSQL) ){
ExeFuncion( $ExeSQL );
}else if( preg_match("/^copy /iu",$ExeSQL) ){
CopyEstructura( $ExeSQL );
}else if( preg_match("/^LOAD /iu", $ExeSQL) || preg_match("/^UNLOAD /iu", $ExeSQL) ){
}else if( $ExeSQL[0]=='$' ){
$i = mb_strpos($ExeSQL, '=');
$Nombre = trim(mb_substr($ExeSQL,0,$i));
$Valor = trim(mb_substr($ExeSQL,$i+1));
if( mb_substr($Valor,-1)==';' ) $Valor = trim(mb_substr($Valor,0,-1));
if( $Valor[0]=='"' || $Valor[0]=="'" ) $Valor = mb_substr($Valor,1,-1);
$_Variable[$Nombre] = $Valor;
if( preg_match("/^SELECT /i", $Valor) ){
$tmp[$w] = $Valor;
$w--;
$_MemCursor = true;
}
if( !$_Trace) continue;
}else if( "FILL GS_CAMPO"==mb_substr(str_replace('  ',' ',mb_strtoupper($ExeSQL)),0,13) ){
RellenaGsCampo( trim(mb_substr(str_replace('  ',' ',$ExeSQL),13)) );
}else if( preg_match("/^DB:/iu",$ExeSQL) ){
$file = '../_datos/config/'.trim(mb_substr($ExeSQL,3));
if( eSubstrCount(mb_substr($file,2),".")==0 ) $file .= ".ini";
include($file);
list($_SqlPDOType) = explode(':', $_SqlPDOConnect);
}else if( preg_match("/^COLOR /iu",$ExeSQL) ){
for($n=0; $n<5; $n++) $ExeSQL = str_replace(array("==","=","  ",), array(" "," "," "), $ExeSQL);
$tmp2 = explode(" ", $ExeSQL);
$_Condition[] = array($tmp2[2], $tmp2[3], $tmp2[1]);
}else if( preg_match("/MARGIN /iu",$ExeSQL) ){
for($n=0; $n<5; $n++) $ExeSQL = str_replace(array("==","=","  ",), array(" "," "," "), $ExeSQL);
$tmp2 = explode(" ", $ExeSQL);
$_Margin[] = array($tmp2[2], $tmp2[3], $tmp2[1]);
}else if( preg_match("/^CHECK SYSTEM$/iu", $ExeSQL) ){
include(DIREDES."t/test/test.php");
}else if( preg_match("/^PASSWORD\:/iu", $ExeSQL) ){
$pass = trim(mb_substr($ExeSQL, 9));
echo "<pre class='FONTMONOSPACE'>";
if( empty($pass) ){
echo "new password: ".eNewPassword(10);
}else{
$newPass = ePassword($pass);
if( $newPass==$pass ){
$newPass = eCreatePassword($pass);
echo " original key: <b>{$pass}</b><br>";
echo "encrypted key: <b>{$newPass}</b>";
}else{
echo "   original key: <b>{$pass}</b><br>";
echo "unencrypted key: <b>{$newPass}</b>";
}
}
echo "</pre>";
echo "<hr>";
}else if( preg_match("/^MD5\:/iu", $ExeSQL) ){
$pass = trim(mb_substr($ExeSQL, 4));
list($email, $pass) = preg_split("/[,|\s]+/", $pass, 2);
$email = trim($email);
$pass = trim($pass);
echo "<pre class='FONTMONOSPACE'>";
echo "email.......: {$email}<br>";
echo "password....: {$pass}<br>";
echo "password md5: <b>".mb_strtoupper(md5($email.mb_strtoupper(md5($pass))))."</b>";
echo "</pre>";
echo "<hr>";
}else{
if( preg_match('/^[0-9]{1,6}$/u', $ExeSQL) ){
SS::query("select cd_gs_user, user_name, user_surname from {$_ENV['SYSDB']}gs_user where cd_gs_user={$ExeSQL}");
ListaFields($_Result, $Limit);
}else if( preg_match('/^(SESS|SETUP|SYS)$/u', $ExeSQL) ){
echo "<pre>";
echo SYS::classToString($ExeSQL);
echo "</pre>";
}else if( preg_match('/^charset/iu', $ExeSQL) ){
$database = $_SESSION["sql"]['database'];
if( mb_substr_count($ExeSQL, " ")>0 ){
list(,$database) = explode(" ", $ExeSQL);
}
$ExeSQL = "SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='{$database}' order by 1";
SS::query($ExeSQL);
ListaFields($_Result, $Limit);
echo "<br>";
}else if( eSubstrCount($ExeSQL,' ')==0 && eSubstrCount($ExeSQL,',')==0 ){
$nameTable = $ExeSQL;
if( $ExeSQL[0]=="*" ){
$nameTable = mb_substr($ExeSQL,1);
$ExeSQL = "select count(*) from ".mb_substr($ExeSQL, 1);
}else{
$ExeSQL = "select * from {$ExeSQL}";
if( SS::isDriver("informix") ){
if( preg_match("/ LIMIT /iu", $ExeSQL) ){
$n = mb_strpos(mb_strtoupper($ExeSQL), ' LIMIT ');
$Limit  = mb_substr( $ExeSQL, $n+7 );
$ExeSQL = mb_substr( $ExeSQL, 0, $n );
list($Desde, $RegAVer) = explode(',', $Limit);
if( $RegAVer!='' ){
$Hasta = $Desde+$RegAVer;
}else{
$Hasta = $Limit;
}
$ExeSQL = 'select first '.$Hasta.' '.mb_substr($ExeSQL,7);
}else{
if( !preg_match("/ FIRST /iu",$ExeSQL) && !preg_match("/SELECT COUNT\(\*\)/iu",$ExeSQL) ) $ExeSQL = 'select first '.$GLOBALS['_DBLimit'].' '.mb_substr($ExeSQL,7);
}
}else if( DB::isDriver("oci") ){
if( !preg_match("/ ROWNUM</iu", $ExeSQL) && !preg_match("/SELECT COUNT\(\*\)/iu", $ExeSQL) ){
$ExeSQL = str_replace( ' where ', ' WHERE ', $ExeSQL );
$ExeSQL = str_replace( ' group by ', ' GROUP BY ', $ExeSQL );
$ExeSQL = str_replace( ' having ', ' HAVING ', $ExeSQL );
$ExeSQL = str_replace( ' order by ', ' ORDER BY ', $ExeSQL );
if( preg_match("/ WHERE /iu", $ExeSQL) ){
$ExeSQL = str_replace( ' where ', ' where ROWNUM<'.$GLOBALS['_DBLimit'].' and ', $ExeSQL );
}else{
if( preg_match("/ GROUP BY /iu", $ExeSQL) ){
$ExeSQL = str_replace( ' GROUP BY ', ' WHERE ROWNUM<'.$GLOBALS['_DBLimit'].' GROUP BY ', $ExeSQL );
}else if( preg_match("/ HAVING /iu", $ExeSQL) ){
$ExeSQL = str_replace( ' HAVING ', ' WHERE ROWNUM<'.$GLOBALS['_DBLimit'].' HAVING ', $ExeSQL );
}else if( preg_match("/ ORDER BY /iu", $ExeSQL) ){
$ExeSQL = str_replace( ' ORDER BY ', ' WHERE ROWNUM<'.$GLOBALS['_DBLimit'].' ORDER BY ', $ExeSQL );
}else{
$ExeSQL .= ' where ROWNUM<'.$GLOBALS['_DBLimit'];
}
}
}
}else if( !preg_match("/ LIMIT /iu", $ExeSQL) && !preg_match("/SELECT COUNT\(\*\)/iu", $ExeSQL) ){
$ExeSQL .= ' limit '.$GLOBALS['_DBLimit'];
}
}
SS::query($ExeSQL);
ListaFields($_Result, $Limit, $nameTable);
echo "<br>";
}else{
echo '<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR';
}
}
if( $_SaveSQL && $_SQLUpdate ){
error_log(date('Y-m-d H:i:s').': '.$ExeSQL."\n", 3, '../_datos/config/sql.log');
$Cdi = date('Y-m-d H:i:s');
SS::query("insert into {$_ENV['SYSDB']}gs_activity (cd_gs_user, cdi, script, edes, email) values ({$_User}, '{$Cdi}', '/_datos/config/sql.log', 'C', '".$_SESSION["_UserEMail"]."')");
echo '<br><b style="color:red">SAVESQL:</b> '.$ExeSQL.'<br>';
}
if( $_SaveVersionSQL && $_SESSION["_D_"]=="~" && preg_match('/gs_/u',$ExeSQL) ){
$ExeSQL = str_replace($_ENV['SYSDB'], "", $ExeSQL);
$version = trim(file_get_contents(DIREDES.'data/version'));
error_log("{$version}: {$ExeSQL}\n", 3, DIREDES.'data/version.sql');
}
if( $_ExeSQL ){
file_put_contents('../_datos/config/sql.cdi', $DimCDI[$w]);
clearstatcache();
}
}
}
echo '</BODY></HTML>';
eEnd();
function eImport($txt){
list(,$file,$in,$tabla) = explode(" ", trim(preg_replace('/ {2,}/', ' ', $txt)));
if( $in!="in" ){
echo "Comando mal construido.<br>";
return;
}
$file = "../_tmp/imp/{$file}.csv";
if( file_exists($file) ){
$linea = -1;
$campos = "";
$df = fopen($file, "r");
while(($datos=fgetcsv($df, ","))==true){
$num = count($datos);
$linea++;
$txt = "";
for($columna=0; $columna<$num; $columna++){
if( $txt!="" ) $txt .= ", ";
$datos[$columna] = mb_strtolower($datos[$columna]);
if( $datos[$columna]=="null" ){
$txt .= "null";
}else{
$txt .= "'".str_replace(array("'", '"', "\\"), array("&#39;", "&#34;", "&#92;"), $datos[$columna])."'";
}
}
if( $linea==0 ){
$campos = str_replace("'", "", $txt);
}else{
SS::query("insert into {$tabla} ({$campos}) values ({$txt})");
}
}
fclose($df);
echo "Se han importado {$linea} registros.<br>";
}else{
echo "El fichero '{$file}' no existe.<br>";
}
}
function split_sql( $sql ){
$sql = trim($sql);
$sql = preg_replace("/#[^\n]*\n/", "", $sql);
$buffer = array();
$ret = array();
$in_string = false;
for($i=0; $i<mb_strlen($sql)-1; $i++){
if( $sql[$i] == ';' && !$in_string ){
$ret[] = trim(mb_substr( $sql, 0, $i ));
$sql = mb_substr( $sql, $i + 1 );
$i = 0;
}
if($in_string && ($sql[$i] == $in_string) && $buffer[0]!="\\"){
$in_string = false;
}else if(!$in_string && ($sql[$i] == "\"" || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0]!="\\")){
$in_string = $sql[$i];
}
if( isset($buffer[1]) ) $buffer[0] = $buffer[1];
$buffer[1] = $sql[$i];
}
if( !empty($sql) ) $ret[] = trim($sql);
return( $ret );
}
function ListaFields($dt_result, $Limit, $NomTabla='', $RegEnTabla=0){
global $_Condition, $_Margin;
$_Condition = array();
$_Margin = array();
global $_Form, $_ALIGN, $_ToExcel, $_ToPDF, $_ToXML, $_ToCSV, $_ToTXT, $_VerBlob, $_WidthBlob, $_TH, $_gsID;
global $_TotalTables;
if( empty($_TotalTables) ){
$_TotalTables = 0;
}
$_TotalTables++;
$n = 0;
$xTR = '';
$xCOL = '';
$sTipo = array();
$alignChild = "";
$dimType = [];
$def = SS::structureSQL();
foreach($def as $k=>$v){
$field = $v;
$field["type"] = $v["native_type"];
$dimType[$n] = $field["type"];
$_Form[$n] = array( $field["name"], $field["name"], '', '', max($field["leng"], $field["leng"]), '', '-', '', '', '');
if( $_TH[$n]!='' ){
$xTR .= "<th nc='{$n}' style='text-align:center'>".trim($_TH[$n])."</th>";
$_Form[$n][0] = trim($_TH[$n]);
}else{
$xTR .= "<th nc='{$n}' style='text-align:center'>{$field['name']}</th>";
}
$n++;
if( eIs($field["type"], ["integer", "float"]) ){
$xCOL .= '<col style="text-align:right" class="alignRight">';
$_ALIGN[$n-1] = 'D';
$dimClass[] = "col_".($n-1)."r";
$alignChild .= ".table_{$_TotalTables} th:nth-child(".($n).") { text-align: right; }\n";
$alignChild .= ".table_{$_TotalTables} td:nth-child(".($n).") { text-align: right; }\n";
}else if( eIs($field["type"], ["date", "datetime", "time"]) ){
$_ALIGN[$n-1] = 'C';
$dimClass[] = "col_".($n-1)."c";
$alignChild .= ".table_{$_TotalTables} th:nth-child(".($n).") { text-align: center; }\n";
$alignChild .= ".table_{$_TotalTables} td:nth-child(".($n).") { text-align: center; }\n";
}else if( preg_match('/binary/iu', $field["type"]) ){
if( $_VerBlob ){
$xCOL .= '<col  style="text-align:left; width:'.$_WidthBlob.'">';
$_ALIGN[$n-1] = 'L';
}else{
$xCOL .= '<col  style="text-align:center">';
$_ALIGN[$n-1] = 'C';
}
$_Form[$n-1][6] = 'L';
}else if( $field["leng"] == 1 ){
$xCOL .= '<col  style="text-align:center">';
$_ALIGN[$n-1] = 'C';
$alignChild .= ".table_{$_TotalTables} th:nth-child(".($n).") { text-align: center; }\n";
$alignChild .= ".table_{$_TotalTables} td:nth-child(".($n).") { text-align: center; }\n";
}else{
$xCOL .= '<col>';
$_ALIGN[$n-1] = 'I';
}
}
global $_User;
if(		  $_ToExcel ){
eInit();
include_once(DIREDES.'xls_lista.gs');
eEnd();
}else if( $_ToXML	){
eInit();
include_once(DIREDES.'xml_lista.gs');
eEnd();
}else if( $_ToCSV	){
eInit();
include_once(DIREDES.'csv_lista.gs');
eEnd();
}else if( $_ToTXT	){
eInit();
include_once(DIREDES.'txt_lista.gs');
eEnd();
}else if( $_ToPDF	){
eInit();
$FicheroPDF = '../_datos/config/pdf.ini';
if( file_exists($FicheroPDF) && is_readable($FicheroPDF) ) include( $FicheroPDF );
if( !isset($PDF_Lib) ) $PDF_Lib = 'pdf_lista.gs';
if( SETUP::$List['TCPDF'] ) $PDF_Lib = 'pdf_lista_tc.gs';
include_once(DIREDES.$PDF_Lib);
eEnd();
}
echo "<style>{$alignChild}</style>";
echo "<table id='BROWSE' class='table_{$_TotalTables}' cellspacing=1px cellpadding=3px border=0px>";
echo $xCOL;
if( $NomTabla!='' ){
echo '<tr><th colspan='.$n.' ondblclick="MaxDef(this)" onmousedown="Mover(this)" title=" '.$RegEnTabla.' Rec." style="text-align:center">TABLA '.$NomTabla;
}
echo '<tr style="position:relative; top:0px" on_click="ListadoEnFicha()">'.$xTR.'</tr>';
$tReg = 1;
$totalColumns = null;
while( $row = SS::get("num") ){
if( $tReg==1 ){
$totalColumns = SS::columnCount();
}
echo '<tr>';
for($i=0; $i<$totalColumns; $i++){
echo '<td>';
if( $dimType[$i]=="string" ){
echo htmlspecialchars($row[$i], ENT_QUOTES, 'UTF-8');
}else if( preg_match('/binary/iu', $dimType[$i]) ){
if( $_VerBlob ){
$Celda = urldecode( $row[$i] );
$Celda = str_replace("\n",'{#}ENTER{#}',$Celda);
echo $Celda;
}else{
echo '[BLOB]';
}
}else{
echo $row[$i];
}
echo '</td>';
}
echo '</tr>';
if( ++$tReg > $GLOBALS['_DBLimit'] ){
echo '<tr>';
for($i=0; $i<$totalColumns; $i++){
echo '<td>...';
}
echo '</tr>';
break;
}
}
echo '</table>';
}
function ListaTablas($dt_result, $ExeSQL, $NumSQL){
global $_Form, $_TH, $_gsID, $_ToExcel, $_ToPDF, $_ToXML, $_ToCSV, $_ToTXT;
$ConCount = false;
$ExeSQL = trim($ExeSQL);
$ExeSQL = str_replace('  ', ' ', $ExeSQL);
if( eSubstrCount($ExeSQL.' ', ' -r ')==1 ){
$ExeSQL = str_replace(' -r', '', $ExeSQL);
$ConCount = true;
}
if( mb_strlen($ExeSQL)!=6 ){
$ExeSQL = mb_substr($ExeSQL,6);
$ExeSQL = str_replace(' ','',$ExeSQL);
$tmp = explode(',',$ExeSQL);
$OkTable = array();
for( $i=0; $i<count($tmp); $i++ ) $OkTabla[trim($tmp[$i])] = true;
$ExeSQL = '?';
}else{
$ExeSQL = '*';
}
if( SS::isDriver("informix") ){
SS::query("select tabname,tabtype from systables where tabid>100");
}else if( DB::isDriver("oci") ){
global $_SqlUsuario; $SqlUsuario = mb_strtoupper($_SqlUsuario);
$sql = "SELECT TABLE_NAME FROM all_tables where OWNER='{$SqlUsuario}'";
SS::query($sql);
}else if( SS::isDriver('mysql,mysqli') ){
SS::query('show tables');
}
if( $_ToExcel || $_ToPDF || $_ToXML || $_ToCSV || $_ToTXT ){
$_Form[0] = explode("|", "Tabla | tabla | # | T | 35 ||M|||");
$_TH[0] = "Tabla";
}
if( $_ToExcel ){
global $_User;
eInit();
include_once(DIREDES.'xls_lista.gs');
eEnd();
}else if( $_ToXML ){
global $_User;
eInit();
include_once(DIREDES.'xml_lista.gs');
eEnd();
}else if( $_ToCSV ){
global $_User;
eInit();
include_once(DIREDES.'csv_lista.gs');
eEnd();
}else if( $_ToTXT ){
global $_User;
eInit();
include_once(DIREDES.'txt_lista.gs');
eEnd();
}else if( $_ToPDF ){
global $_User;
eInit();
$FicheroPDF = '../_datos/config/pdf.ini';
if( file_exists($FicheroPDF) && is_readable($FicheroPDF) ) include( $FicheroPDF );
if( !isset($PDF_Lib) ) $PDF_Lib = 'pdf_lista.gs';
if( SETUP::$List['TCPDF'] ) $PDF_Lib = 'pdf_lista_tc.gs';
include_once(DIREDES.$PDF_Lib );
eEnd();
}
?>
<STYLE>
.TblFlotante {
POSITION: absolute;
background: #FFFFCC;
padding: 1px;
BORDER: 1px solid #000099;
}
.TblFlotante TH {
padding-left: 10ox;
padding-right: 10px;
cursor: pointer;
}
.TblFlotante TD {
cursor: default;
}
#_BROWSE TD {
cursor: pointer;
}
#TBROWSE TD {
cursor: pointer;
}
BODY::-webkit-scrollbar {
-webkit-appearance: none;
width: 12px;
height:12px;
}
BODY::-webkit-scrollbar-thumb {
border-radius: 4px;
background-color: #7f7f7f;
}
</STYLE>
<IFRAME name='TLF' src='' width=0 height=0 style='border:1px solid red'></iframe>
<SCRIPT type="text/javascript">
top.S.init(window, "all,list");
function AnulaTecla(){
return S.eventClear(window);
}
function NoSelect(){ AnulaTecla();return false; }
document.onselectstart = NoSelect;
function xy( el ){
var xy = new Array(0,0);
while( el!=null ){
xy[0] += el.offsetLeft;
xy[1] += el.offsetTop;
el = el.offsetParent;
}
return xy;
}
document.onmouseup = new Function("dragapproved=false;document.onmousemove=null;");
var dragapproved = false;
var tempx, tempy, offsetx, _zIndex = 0;
function drag_drop(e){
if( dragapproved && event.button==1 ){
document.getElementById(NomDiv).style.left = tempx+event.clientX;
document.getElementById(NomDiv).style.top = tempy+event.clientY;
}
}
function Mover( e ){
NomDiv = e.parentElement.parentElement.parentElement.parentElement.id;
document.getElementById(NomDiv).style.zIndex = ++_zIndex;
tempx = parseInt(document.getElementById(NomDiv).style.left)-event.clientX;
tempy = parseInt(document.getElementById(NomDiv).style.top)-event.clientY;
dragapproved = true;
document.onmousemove = drag_drop;
}
function MaxDef( Obj ){
var Put = ( Obj.Max==1 ) ? 'none':'', i;
Obj.Max = !Obj.Max;
Obj = Obj.parentElement.parentElement.parentElement.rows;
for(i=1; i<Obj.length; i++) Obj[i].style.display = Put;
}
var _NomTabla = null;
function VistaTabla( Obj, Tabla ){
if( document.getElementById('def'+Tabla)!=null ) S('#def'+Tabla).nodeRemove();
var tmp = xy( Obj ),
oNode = document.createElement('DIV');
oNode.id = 'def'+Tabla;
oNode.className = 'TblFlotante';
oNode.style.cssText = 'LEFT:'+(tmp[0]+Obj.parentElement.parentElement.parentElement.clientWidth-2)+'px;TOP:'+(tmp[1]*1-3)+'px';
oNode.innerText = Tabla;
S(oNode).nodeEnd( document.body );
if( event.button == 2 ){
TLF.document.location.replace(S.sess("edes.php?E:$t/35.gs&VerSEL="+Tabla));
}else{
TLF.document.location.replace(S.sess("edes.php?E:$t/31.gs&VerSQL="+Tabla+'&IT=1&Multi=1'));
}
return AnulaTecla();
}
function ViewStructure(){
var Obj = event.target || event.srcElement;
if( Obj.tagName == 'U' ) Obj = Obj.parentElement;
if( Obj.tagName!='TD' ) return;
if( Obj.cellIndex == 1 ) Obj = Obj.parentElement.cells[0];
_NomTabla = Obj;
if( Obj.innerText.indexOf('*')>-1 ) return;
VistaTabla( Obj, Obj.innerText.replace(/\s/g,'') );
}
function Ocultar( Obj ){
Obj.parentElement.parentElement.parentElement.parentElement.style.display = 'none';
return AnulaTecla();
}
<?PHP
if( $_SERVER['REQUEST_METHOD']=='GET' ) echo 'top.eLoading(false,window);';
?>
</SCRIPT>
<?PHP
if( $NumSQL==1 ){
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px onmousedown=ViewStructure()>';
}else{
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px onmousedown=ViewStructure()>';
}
if( $ConCount ){
echo '<tr _style="position:relative; top:0px"><th>TABLES</th><th>REGISTROS</th></tr>';
echo '<col><col style="text-align:right">';
}else{
echo '<tr _style="position:relative; top:0px"><th>TABLES</th></tr>';
}
$Dim = array();
while( $row = SS::get("num") ){
if( SS::isDriver('informix') ){
if( $row[1]=='S' ){
$Dim[] = trim($row[0]).'*';
}else{
$Dim[] = trim($row[0]);
}
}else if( DB::isDriver("oci") ){
$Dim[] = mb_strtolower(trim($row[0]));
}else if( SS::isDriver('mysql,mysqli') ){
$Dim[] = trim($row[0]);
}
}
sort($Dim);
for($i=0; $i<count($Dim); $i++){
if( mb_substr($Dim[$i],-1)=='*' ){
$Dim[$i] = mb_substr($Dim[$i],0,-1);
if( $ExeSQL == '*' || $OkTabla[$Dim[$i]] == true ){
echo '<tr><td title="Alias"><U>'.$Dim[$i].'</U>';
if( $ConCount ) echo '<td>'.eNumberFormat(SS::count($Dim[$i])).'</td>';
}
}else{
if( $ExeSQL == '*' || $OkTabla[$Dim[$i]] == true ){
echo '<tr><td>'.$Dim[$i];
if( $ConCount ) echo '<td>'.eNumberFormat(SS::count($Dim[$i])).'</td>';
}
}
}
foreach($OkTabla as $key=>$value){
if( eSubstrCount($key,'*')>0 ){
echo '<tr><td title="Expresión regular" align=right><b>'.$key.'</b>';
if( $ConCount ) echo '<td> </td>';
$NomPatron = '/^'.$key.'$/u';
$NomPatron = str_replace('.', '\.', $NomPatron);
$NomPatron = str_replace('*', '.*', $NomPatron);
for($i=0; $i<count($Dim); $i++){
if( preg_match($NomPatron, $Dim[$i]) ){
echo '<tr><td>'.$Dim[$i];
if( $ConCount ) echo '<td>'.eNumberFormat(SS::count($Dim[$i])).'</td>';
}
}
}
}
echo '</table>';
}
function Help( $sql ){
?>
- El interprete SQL acepta los comandos SQL mas las siguientes ordenes:<BR>
- Se pueden encadenar sentencias SQL separadas por "<B>;</B>".<BR>
- Admite los comentarios de línea si empieza por "<B>.</B>" o por "<B>//</B>".<BR>
- Admite comentarios de multilínea si empieza una línea por "<B></B>" en una sola línea.<BR>
- Un guión "<B>-</B>" dibujará una línea de separación, para dar mas grosor a la linea "<B>=</B>" o "<B>#</B>".<BR>
- Admite comentarios de fin de código "<B>[Note]</B>" no teniendo en cuenta nada a partir del comando.<BR>
- Para poner una linea de texto la linea tiene que empezar por ":".<br>
- Para silenciar el sql "notrace" / "noshow".<br>
- Si ponemos solo el nombre de una tabla ejecutará "select * from NomTabla".<BR>
- Si ponemos solo el nombre de una tabla precedido por un "*" ejecutará "select count(*) from NomTabla".<BR>
- "<B>Ctrl-Enter</B>" ejecutará el sql sobre el que esté el cursor.<BR>
- "<B>Ctrl-F1</B>" formateará el sql sobre el que esté el cursor.<BR>
- Simula la orden "<B>limit</B>" para el controlador Informix<BR>
- La orden "<B>tables</B>" te muestra un listado con todas las tablas, pudiendo ver su estructura si pulsamos en el nombre, también puede ser "tables NomTabla,NomTabla,," saldrá un listado con esas tablas únicamente y al pulsar saldrá la estructura, esta estructura se puede desplazar la ventana por la pantalla, minimizar y maximizar con un doble click y ocultar con el botón derecho, también si se pulsa con el botón derecho saldrá un listado con los 10 primeros registros. Si se pone el parámetro "-r" mostrará una columna con el número de registros de cada tabla. Admite el símbolo comodín "*" ( ej: tables gs_* todas las tablas que empiezan por "gs_" )<BR>
- La orden "<B>structure</B>" NomTabla [,NomTabla,NomTabla,,,] te muestra la estructura de esas tablas.<BR>
- La orden "<B>edit structure</B>" NomTabla te permite tener una copia de la estructura de la tabla en el editor SQL para crear otra tabla a partir de esta.<BR>
- La orden "<B>info</B>" NomTabla [,NomTabla,NomTabla,,,] te muestra la información extra de esas tablas.<BR>
- "<B>help</B>" / "<B>?</B>" Muestra la ayuda<br>
- "<B>help sql</B>" Muestra la ayuda del gestor de base de datos activo.<br>
- "<B>fields</B>" lista los campos de todas las tablas, también puede llevar como parámetro una lista de campos separados por coma.<br>
- "Se pueden definir <B>variables</B> dentro del código declarando previamente la variable como: '$NomVariable = "..Valor..";' y usándola posteriormente dentro de una sentencia sql de la siguiente forma "{$NomVariable}".<BR>
- Memorizar el <B>cursor</B> en una variable: "$row = select * from tabla". y luego se sustituirá donde encuentre "$row[NomCampo]" por su valor<br>
- Con "Control" mas el icono de leer PHP/HTM/SQL puedes definir un nuevo fichero de trabajo.<br>
- Comando "<B>copy TablaOrigen TablaDestino</B>" creará la "TablaDestino" vacía iguan que la "TablaOrigen", cambiando en toda la definición la cadena "TablaOrigen" por "TablaDestino".<BR>
- Funciones para la manipulación del contenido de campos de caracteres:<br>
<span style='margin-left:20px'></span><B>eUpper( tabla.campo )</B>: Convierte a mayúsculas.<br>
<span style='margin-left:20px'></span><B>eLower( tabla.campo )</B>: Convierte a minúsculas.<br>
<span style='margin-left:20px'></span><B>eUpperLower( tabla.campo )</B>: Capitaliza la primera letra.<br>
<span style='margin-left:20px'></span><B>eMaxLeng( tabla.campo )</B>: Devuelve la longitud máxima del contenido del campo.<br>
<span style='margin-left:20px'></span><B>eChange( tabla.campo, CadenaOld, CadenaNew )</B>: Sustituye una cadena/carácter en un campo.<BR>
- Funciones para la información del contenido de un campo de caracteres:<br>
<span style='margin-left:20px'></span><B>eChr( tabla.campo )</B>: Devuelve una cadena con todos los carácteres usados en el campo.<br>
- Con el prefijo "<B>=</B>" delante de una sentencia "select" no añadirá LIMIT/ROWNUM/FIRST para limitar el select.
- Con el prefijo "<B>XLS:</B>" o "<B>EXCEL:</B>" delante de una sentencia "select" la salida en lugar de ser en HTML será en un fichero Excel.<br>
- Con el prefijo "<B>XML:</B>" delante de una sentencia "select" la salida en lugar de ser en será en un fichero XML.<br>
- Con el prefijo "<B>CSV:</B>" delante de una sentencia "select" la salida en lugar de ser en será en un fichero CSV.<br>
- Con el prefijo "<B>TXT:</B>" delante de una sentencia "select" la salida en lugar de ser en será en un fichero TXT.<br>
- Con el prefijo "<B>PDF:</B>" delante de una sentencia "select" la salida en lugar de ser en será en un fichero PDF.<br>
- Con el prefijo "<B>BLOB:</B>" delante de una sentencia "select" activará la visualización de los campos de texto largo, por defecto estos tienen un ancho de 400px si se quiere de otro ancho pondremos "BLOB,[Ancho]: select ..." (sólo MySql).<br>
- Con el prefijo "<B>SAVESQL:</B>" se grabará la sentencia que altere el diccionario de datos o los datos para actualizarlo en el servidor de procesos, el fichero de log se denomina "/_datos/config/sql.log".<br>
- Con el prefijo "<B>print:</B>" pintará el texto indicado.<br>
- Con el prefijo "<B>TIME[,NºDeVeces]:</B>" se ejecutará el select una o mas veces dandote el tiempo que ha tardado en ejecutarlo.<br>
- Con el comnado "<B>EXESQL</B>" se ejecutará todas las sentencias pendientes grabadas con "SAVESQL:" desde el fichero "/_datos/config/sql.log".<br>
- Con el comando "<B>DB: [NmFile]</B>" Nombre del fichero de definición de base de datos ubicado en "/_datos/config". Este comando tienen que ser la primera linea y define a que base de datos se conecta.<br>
- Con el comando "<B>FILL gs_campo [NmTabla&#47;&#42;]</B>" Rellena la tabla "gs_campo" para la definición de las extracciones.<br>
- Con el comando "<B>phpinfo</B>" Muestra el resultado de la función phpinfo(), si está este comando solo mostrará esta información.<br>
- Con el comamdo "<B>charset[ database]</B>" Muestra el listado de las tablas con su charset<br>
- Lista de variables "<B>$variable [, $variable, ...]</B>" Si hay una sola linea de variables las mostrará, máximo 5 variables.<br>
- Lista de variables de session con "<B>SESS</B>"<br>
- Lista de variables de configuración con "<B>SETUP</B>"<br>
<?PHP
if( $sql=="" ) $sql =  $_Sql;
if( SS::isDriver($sql) ){
?>
- Con el comando "<B>STATUS</B>" Proporciona información del estado del servidor.<br>
- Con el comando "<B>EXPLAIN [NmTabla]</B>" Proporciona información sobre las columnas en una tabla.<br>
<?PHP
}
if( $_SESSION["_D_"]=="~" || $_SESSION["_D_"]=="A" ){
?>
- Con el comando "<B>PASSWORD: palabraAEncriptar/palabraEncriptada</B>" Encriptar/desencriptar el password/user de la base de datos.<br>
<?PHP
}
echo "<br><br><br>";
}
function CamposMySql( $ListaCampos ){
$DimCampo = array();
SS::query( 'show tables', [], 1);
while( $NomTabla = SS::get("num", 1) ){
$NomTabla = trim($NomTabla[0]);
$txt = '';
$Condicion = ( $ListaCampos!='' ) ? " where Field in ({$ListaCampos})" : '';
DB::query("SHOW FIELDS FROM {$NomTabla}{$Condicion}");
while( $row = DB::get() ){
$txt = "{$row[Field]} {$row[Type]}";
if( count($DimCampo) == 0 ){
$DimCampo[] = array( $txt, 1, $NomTabla );
}else{
$Ok = false;
for( $n=0; $n<count($DimCampo); $n++ ){
if( $DimCampo[$n][0] == $txt ){
$DimCampo[$n][1]++;
$DimCampo[$n][2] .= ', '.$NomTabla;
$Ok = true;
break;
}
}
if( !$Ok ) $DimCampo[] = array( $txt, 1, $NomTabla );
}
}
}
$tmp = array();
for( $n=0; $n<count($DimCampo); $n++ ){
$tmp[] = $DimCampo[$n][0].'|'.$DimCampo[$n][1].'|'.$DimCampo[$n][2];
}
sort($tmp);
for( $n=0; $n<count($tmp); $n++ ){
list( $DimCampo[$n][0], $DimCampo[$n][1], $DimCampo[$n][2] ) = explode('|',$tmp[$n]);
}
?>
<style type="text/css">
TD {
WHITE-SPACE: normal;
}
</style>
<?PHP
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0 width=100%>';
echo '<col><col style="text-align:right"><col style="width:100%;">';
echo '<tr><th>FIELDS<th>Nº<th>TABLAS';
for( $n=0; $n<count($DimCampo); $n++ ){
if( $n > 0 ){
$an = explode(' ',$DimCampo[$n-1][0]);
$ac = explode(' ',$DimCampo[$n][0]);
$DimCampo[$n][0] = str_replace(' ','&nbsp;',$DimCampo[$n][0]);
if( $an[0]==$ac[0] ){
echo '<tr><td>'.str_replace($an[0].' ','<b style="color:#CC0000">'.$an[0].'</b> ',$DimCampo[$n][0]).'<td>'.$DimCampo[$n][1];
}else{
echo '<tr><td>'.$DimCampo[$n][0].'<td>'.$DimCampo[$n][1];
}
}else{
echo '<tr><td>'.$DimCampo[$n][0].'<td>'.$DimCampo[$n][1];
}
echo '<td>'.$DimCampo[$n][2];
}
echo '</table>';
}
function CamposOracle( $ListaCampos ){
global $_Result, $_SqlUsuario;
$SqlUsuario = mb_strtoupper($_SqlUsuario);
$DimCampo = array();
$Condicion = ( $ListaCampos!='' ) ? " where COLUMN_NAME in ({$ListaCampos})" : '';
SS::query("select * from all_tab_cols WHERE OWNER='{$SqlUsuario}' and TABLE_NAME NOT LIKE 'BIN".'$'."%' {$ListaCampos} order by TABLE_NAME,COLUMN_ID");
while( OCIFetch($_Result) ){
$NomTabla = trim(mb_strtolower(OCIResult($_Result,'TABLE_NAME')));
$Campo = trim(mb_strtolower(OCIResult($_Result,'COLUMN_NAME')));
$Tipo = trim(mb_strtolower(OCIResult($_Result,'DATA_TYPE')));
if( OCIResult($_Result,'DATA_TYPE')!='DATE' && mb_substr(OCIResult($_Result,'DATA_TYPE'),0,9)!='TIMESTAMP' ){
$Tipo .= '(';
if( OCIResult($_Result,'DATA_PRECISION')== 0 ){
$Tipo .=  OCIResult($_Result,'DATA_LENGTH');
}else{
$Tipo .=  OCIResult($_Result,'DATA_PRECISION');
if( OCIResult($_Result,'DATA_SCALE')!=0 ) $Tipo .= ','.OCIResult($_Result,'DATA_SCALE');
}
$Tipo .= ') ';
}
if( OCIResult($_Result,'NULLABLE')=='N' ) $Tipo .= ' not null';
$Cadena = $Campo.' '.$Tipo;
if( $DimCampo[$Cadena][0] == '' ){
$DimCampo[$Cadena][0] = $Cadena;
$DimCampo[$Cadena][1] = 1;
$DimCampo[$Cadena][2] = $NomTabla;
}else{
$DimCampo[$Cadena][1]++;
$DimCampo[$Cadena][2] .= ', '.$NomTabla;
}
}
$tmp = array();
foreach( $DimCampo as $k=>$v ){
$tmp[] = $v[0].'|'.$v[1].'|'.$v[2];
}
sort($tmp);
$DimCampo = array();
for( $n=0; $n<count($tmp); $n++ ){
list( $DimCampo[$n][0], $DimCampo[$n][1], $DimCampo[$n][2] ) = explode('|',$tmp[$n]);
}
?>
<style type="text/css">
TD {
WHITE-SPACE: normal;
}
</style>
<?PHP
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0 width=100%>';
echo '<col><col style="text-align:right"><col style="width:100%;">';
echo '<tr><th>FIELDS<th>Nº<th>TABLAS';
for( $n=0; $n<count($DimCampo); $n++ ){
if( $n > 0 ){
$an = explode(' ',$DimCampo[$n-1][0]);
$ac = explode(' ',$DimCampo[$n][0]);
$DimCampo[$n][0] = str_replace('  ',' ',trim($DimCampo[$n][0]));
$DimCampo[$n][0] = str_replace(' ','&nbsp;',$DimCampo[$n][0]);
if( $an[0]==$ac[0] ){
echo '<tr><td>'.str_replace($an[0].' ','<b style="color:#CC0000">'.$an[0].'</b> ',$DimCampo[$n][0]).'<td>'.$DimCampo[$n][1];
}else{
echo '<tr><td>'.$DimCampo[$n][0].'<td>'.$DimCampo[$n][1];
}
}else{
echo '<tr><td>'.$DimCampo[$n][0].'<td>'.$DimCampo[$n][1];
}
echo '<td>'.$DimCampo[$n][2];
}
echo '</table>';
}
function CamposInformix( $ListaCampos ){
$DimCampo = array();
SS::query("select tabname,tabid from systables where tabid>100 and tabtype=='T'");
while( $Tabla = SS::get("num") ){
$pTabla = '';
$Condicion = ( $ListaCampos!='' ) ? " and colname in ({$ListaCampos})" : '';
SS::query( 'select colname,colno,coltype,collength from syscolumns where tabid = '.$Tabla[1] . $Condicion, [], 1);
while( ($Campo = SS::get("num", 1)) ){
$NomTabla = $Tabla[0];
$txt = $Campo[0].','.$Campo[2].','.$Campo[3];
if( count($DimCampo) == 0 ){
$DimCampo[] = array( $txt, 1, $NomTabla );
}else{
$Ok = false;
for( $n=0; $n<count($DimCampo); $n++ ){
if( $DimCampo[$n][0] == $txt ){
$DimCampo[$n][1]++;
$DimCampo[$n][2] .= ', '.$NomTabla;
$Ok = true;
break;
}
}
if( !$Ok ) $DimCampo[] = array( $txt, 1, $NomTabla );
}
}
}
$DimTipos = array('CHAR','SMALLINT','INTEGER','FLOAT','SMALLFLOAT','DECIMAL','SERIAL','DATE','MONEY','',
'DATETIME','BYTE','TEXT','VARCHAR','INTERVAL','NCHAR','NVARCHAR','INT8','SERIAL8','SET','MULTISET','LIST','');
$DimTipos[40] = '';
$DimPrecision = array(
'0000'=>'year',
'0010'=>'month',
'0100'=>'day',
'0110'=>'hour',
'1000'=>'minute',
'1010'=>'second' );
$tmp = array();
for( $n=0; $n<count($DimCampo); $n++ ){
$Tipo = explode(',',$DimCampo[$n][0]);
$Nulos = '';
if( $Tipo[1] > 255 ){
$Nulos = ' not null ';
$Restar = 32768;
for( $i=0; $i<8; $i++ ){
if( $Tipo[1] >= $Restar ) $Tipo[1] -= $Restar;
$Restar = $Restar/2;
}
}
$txt = $Tipo[0].' '.mb_strtolower($DimTipos[$Tipo[1]]);
switch( $DimTipos[$Tipo[1]] ){
case 'DATETIME':
case 'INTERVAL':
for( $i=11; $i<16; $i++ ){
if( $Tipo[2] >= (256*$i) ) $Tipo[2] -= (256*$i);
}
$bin = decbin( $Tipo[2] );
$bin = mb_substr($bin,-8);
$desde = mb_substr($bin,0,4);
$hasta = mb_substr($bin,-4);
$txt .= ' '.$DimPrecision[$desde].' to '.$DimPrecision[$hasta];
$Tipo[2] = '';
break;
case 'DATE':
case 'TEXT':
case 'SMALLINT';
case 'INTEGER':
case 'FLOAT':
case 'SMALLFLOAT':
$Tipo[2] = '';
break;
case 'DECIMAL':
$Tipo[2] =  '('.floor($Tipo[2] / 256).','.($Tipo[2] % 256).')';
break;
case 'VARCHAR':
$Tipo[2] =  '('.($Tipo[2] % 256).','.floor($Tipo[2] / 256).')';
break;
default:
$txt .= '(';
$Tipo[2] .= ')';
}
$txt .= $Tipo[2].' '.$Nulos;
$DimCampo[$n][0] = $txt;
$tmp[] = $DimCampo[$n][0].'|'.$DimCampo[$n][1].'|'.$DimCampo[$n][2];
}
sort($tmp);
for( $n=0; $n<count($tmp); $n++ ){
list( $DimCampo[$n][0], $DimCampo[$n][1], $DimCampo[$n][2] ) = explode('|',$tmp[$n]);
}
?>
<style type="text/css">
TD {
WHITE-SPACE: normal;
}
</style>
<?PHP
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0 width=100%>';
echo '<col><col style="text-align:right"><col style="width:100%;">';
echo '<tr><th>FIELDS<th>Nº<th>TABLAS';
for( $n=0; $n<count($DimCampo); $n++ ){
if( $n > 0 ){
$an = explode(' ',$DimCampo[$n-1][0]);
$ac = explode(' ',$DimCampo[$n][0]);
$DimCampo[$n][0] = str_replace(' ','&nbsp;',$DimCampo[$n][0]);
if( $an[0]==$ac[0] ){
echo '<tr><td>'.str_replace($an[0].' ','<b style="color:#CC0000">'.$an[0].'</b> ',$DimCampo[$n][0]).'<td>'.$DimCampo[$n][1];
}else{
echo '<tr><td>'.$DimCampo[$n][0].'<td>'.$DimCampo[$n][1];
}
}else{
echo '<tr><td>'.$DimCampo[$n][0].'<td>'.$DimCampo[$n][1];
}
echo '<td>'.$DimCampo[$n][2];
}
echo '</table>';
}
function ExplainMySql( $sql ){
$sql = str_replace("  "," ",trim($sql));
list(,$tabla, $mas) = explode(" ",$sql);
if( $mas=="" ){
echo "<br>EXPLAIN {$tabla}<br>";
SS::query( "EXPLAIN {$tabla}" );
echo "<br>{$sql}<br>";
SS::query( "{$sql}" );
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px>';
echo '<tr><th>field<th>type<th>null<th>key<th>default<th>extra';
}else{
echo "<br>{$sql}<br>";
SS::query( "{$sql}" );
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px>';
echo '<tr><th>id<th>select_type<th>table<th>type<th>possible_keys<th>key<th>key_len<th>ref<th>rows<th>extra';
}
while( $row = SS::get("num") ){
echo '<tr>'; for( $n=0; $n<count($row); $n++ ) echo '<td>'.$row[$n];
}
echo '</table><br>';
}
function StatusMySql(){
echo "<br>SHOW DATABASES<br>";
SS::query( 'SHOW DATABASES' );
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px>';
echo '<tr><th>DATABASES';
while( $row = SS::get("num") ){
echo '<tr>'; for( $n=0; $n<count($row); $n++ ) echo '<td>'.$row[$n];
}
echo '</table><br>';
echo "<br>SHOW GRANTS<br>";
SS::query( 'SHOW GRANTS FOR '.$GLOBALS['_SqlUsuario'] );
echo '<table cellspacing=1px cellpadding=3px border=0px>';
echo '<tr><th>PRIVILEGES';
while( $row = SS::get("num") ){
echo '<tr>'; for( $n=0; $n<count($row); $n++ ) echo '<td>'.$row[$n];
}
echo '</table><br>';
echo "<br>SHOW PROCESSLIST<br>";
SS::query( 'SHOW PROCESSLIST' );
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px>';
echo '<col><col style="align:right">';
ECHO '<TR><TH>ID<TH>USER<TH>HOST<TH>DB<TH>COMMAND<TH>TIME<TH>STATE<TH>INFO';
while( $row = SS::get("num") ){
echo '<tr>'; for( $n=0; $n<count($row); $n++ ) echo '<td>'.$row[$n];
}
echo '</table><br>';
echo "<br>SHOW STATUS<br>";
SS::query( 'SHOW STATUS' );
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px>';
echo '<col><col style="TEXT-ALIGN:right">';
echo '<tr><th>STATUS<th>VALUE';
while( $row = SS::get("num") ){
echo '<tr>'; for( $n=0; $n<count($row); $n++ ) echo '<td>'.$row[$n];
}
echo '</table><br>';
echo "<br>SHOW VARIABLES<br>";
SS::query( 'SHOW VARIABLES' );
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px>';
echo '<col><col style="align:right">';
echo '<tr><th>VARIABLE<th>VALUE';
while( $row = SS::get("num") ){
echo '<tr>'; for( $n=0; $n<count($row); $n++ ) echo '<td>'.$row[$n];
}
echo '</table><br>';
echo "<br>SHOW TABLE STATUS<br>";
SS::query( 'SHOW TABLE STATUS' );
echo '<table id=T_BROWSE cellspacing=1px cellpadding=3px border=0px>';
echo '<col><col style="align:center">';
echo '<tr>';
echo '<th>Name';
echo '<th>Engine';
echo '<th>Version';
echo '<th>Row_format';
echo '<th>Rows';
echo '<th>Avg_row_length';
echo '<th>Data_lengt';
echo '<th>Max_data_length';
echo '<th>Index_length';
echo '<th>Data_free';
echo '<th>Auto_increment';
echo '<th>Create_time';
echo '<th>Update_time';
echo '<th>Check_time';
echo '<th>Collation';
echo '<th>Checksum';
echo '<th>Create_options';
echo '<th>Comment';
while( $row = SS::get("num") ){
echo '<tr>'; for( $n=0; $n<count($row); $n++ ) echo '<td>'.$row[$n];
}
echo '</table>';
}
function CopyEstructura( $ExeSQL ){
global $_Sql;
$ExeSQL = str_replace('  ',' ',$ExeSQL);
list( ,$DBOri, $DBDes ) = explode(' ',$ExeSQL);
$Buffer = ob_get_contents();
$n = mb_strlen(ob_get_contents());
if( SS::isDriver("informix") ) echo GetTablaInformix( $DBOri, false );
if( DB::isDriver("oci") ) echo GetTablaOracle( $DBOri, false );
if( SS::isDriver('mysql,mysqli') ) echo GetTablaMySql( $DBOri, false );
$tmp = mb_substr( ob_get_contents(), $n );
eInit();
echo $Buffer;
$tmp = str_replace('<TR',',<TR', $tmp);
$tmp = str_replace('<TD',' <TD', $tmp);
$tmp = mb_substr($tmp, mb_strrpos($tmp,'</TH>')+6);
$tmp = str_replace('  ',' ',$tmp);
$tmp = str_replace($DBOri,$DBDes,$tmp);
$tmp = 'create table '.$DBDes.' ('.strip_tags($tmp).')';
SS::query($tmp);
echo "Ok<br>";
}
function VerSelect($NomTabla){
eHTML();
?>
<style type="text/css">
BODY {
color: #000099;
background: #F5F5F5;
margin: 0px;
}
TABLE {
font-size: 12px;
background-color: #000099;
}
TH {
color: #000099;
background-color: #D3DCE3;
}
TD {
background-color: #F6F8F9;
WHITE-SPACE: nowrap;
}
</style>
<SCRIPT type='text/javascript' charset='UTF-8'>
document.title = "LIST";
top.S.init(window, "all,list");
</script>
</HEAD><BODY>
<?PHP
global $_Sql, $_SqlDiccionario, $_Result;
$_TReg = eNumberFormat(SS::count($NomTabla));
if( SS::isDriver("informix") ){
SS::query("select first 10 * from {$NomTabla}");
}else if( DB::isDriver("oci") ){
SS::query("select * from {$NomTabla} where ROWNUM<10");
}else if( SS::isDriver('mysql,mysqli') ){
SS::query("select * from {$NomTabla} limit 10");
}
ListaFields($_Result, 10, $NomTabla, $_TReg);
?>
<SCRIPT type="text/javascript">
if( parent._NomTabla.title.indexOf('/')==-1 ){
if( parent._NomTabla.title=='' ){
parent._NomTabla.title = '<?= $_TReg; ?> Rec.';
}else if( parent._NomTabla.title!='<?= $_TReg; ?> Rec.' ){
parent._NomTabla.title += '/<?= $_TReg; ?> Rec.';
}
}
parent.document.getElementById("def<?= $NomTabla; ?>").innerHTML = document.getElementsByTagName("TABLE")[0].outerHTML;
</SCRIPT>
<?PHP
echo '</BODY></HTML>';
eEnd();
}
function CreaSerial( &$sql ){
global $_SqlUsuario;
$NomTabla = '';
$tabla = '';
list($tabla) = explode('(',$sql);
$tabla = trim($tabla);
$tabla = str_replace('  ',' ',$tabla);
$tmp = explode(' ',$tabla);
$NomTabla = $tmp[count($tmp)-1];
if( eSubstrCount($NomTabla,'.')==1 ) list(,$NomTabla) = explode('.',$NomTabla);
$f = mb_strpos( mb_strtoupper($sql), ' SERIAL' );
$xSerial = mb_substr($sql,$f,7);
$sql = str_replace($xSerial,'',$sql);
if( $NomTabla!='' ){
$uSqlUsuario = mb_strtoupper($_SqlUsuario);
$uNomTabla = mb_strtoupper($NomTabla);
if( SS::count('all_sequences', "SEQUENCE_NAME='SQ{$uNomTabla}' and SEQUENCE_OWNER='{$uSqlUsuario}'" ) == 1 ){
SS::query( "drop sequence {$_SqlUsuario}.SQ".$uNomTabla );
}
$Secuencia = "CREATE SEQUENCE {$_SqlUsuario}.sq{$NomTabla} START WITH 1 INCREMENT BY 1 MINVALUE 0 CACHE 4 NOCYCLE ORDER";
SS::query( $Secuencia );
echo "Ok sequence {sq{$NomTabla}}<br>";
}
}
function ExeFuncion( $ExeSQL ){
$ini = mb_strpos($ExeSQL,'(')+1;
$fin = mb_strpos($ExeSQL,')');
list( $Tabla, $Campo ) = explode('.',trim( mb_substr( $ExeSQL, $ini, $fin-$ini )));
if( preg_match("/eMaxLeng\(/iu",$ExeSQL) ){
SS::query( "select max( length({$Campo}) ) from {$Tabla} order by 1 desc" );
$row = SS::get("num");
echo 'Máxima longitud: '.$row[0].'<br>';
return;
}
$n = 0;
if( eSubstrCount( $Campo, ',' ) > 0 ){
$txt = mb_substr( $Campo, mb_strpos($Campo,',')+1 );
$Campo = mb_substr( $Campo, 0, mb_strpos($Campo,',') );
}
SS::query("select {$Campo} from {$Tabla}");
if( preg_match("/^eUpper\(/iu",$ExeSQL) ){
while( $row=SS::get("num") ){
SS::query( 'update '.$Tabla.' set '.$Campo.'="'.trim(mb_strtoupper($row[0])).'" where '.$Campo.'="'.trim($row[0]).'"', $tmp );
$n++;
}
echo $n.' Registros convertidos a mayúsculas';
}else if( preg_match("/eLower\(/iu",$ExeSQL) ){
while( $row=SS::get("num") ){
SS::query( 'update '.$Tabla.' set '.$Campo.'="'.trim(mb_strtolower($row[0])).'" where '.$Campo.'="'.trim($row[0]).'"', $tmp );
$n++;
}
echo $n.' Registros convertidos a minúsculas';
}else if( preg_match("/eUpperLower\(/iu",$ExeSQL) ){
while( $row=SS::get("num") ){
SS::query( 'update '.$Tabla.' set '.$Campo.'="'.ucfirst(trim(mb_strtolower($row[0]))).'" where '.$Campo.'="'.trim($row[0]).'"', $tmp );
$n++;
}
echo $n.' Registros convertidos a mayúsculas y minúsculas';
}else if( preg_match("/eChr\(/iu",$ExeSQL) ){
$abc = '';
while( $row=SS::get("num") ) $abc = count_chars( $row[0].$abc, 3 );
echo $abc;
}else if( preg_match("/eChange\(/iu",$ExeSQL) ){
$Dim = array('','');
$p = 0;
for( $n=0; $n<mb_strlen($txt); $n++ ){
$c = mb_substr($txt,$n,1);
if( $c == '"' ||  $c == "'" ){
for( $i=$n+1; $i<mb_strlen($txt); $i++ ){
$cc = mb_substr($txt,$i,1);
if( $c == $cc ) break;
$Dim[$p] .= $cc;
}
$n = $i;
$p++;
}
}
while( $row=SS::get("num") ){
SS::query( 'update '.$Tabla.' set '.$Campo.'="'.str_replace( $Dim[0], $Dim[1], $row[0] ).'" where '.$Campo.'="'.trim($row[0]).'"', $tmp );
$n++;
}
echo $n.' Registros eChange()';
}
}
function AMayusculas( $txt ){
$Letras = array( array('á','A'),array('é','E'),array('í','I'),array('ó','O'),array('ú','U'),array('ü','Ü'),array('ñ','Ñ'), array('&EURO;','EUR') );
$txt = mb_strtoupper( $txt );
for( $i=0; $i<count($Letras); $i++ ) $txt = str_replace( $Letras[$i][0], $Letras[$i][1], $txt );
return $txt;
}
function EnPlural( $Titulo, $Delante, $EnPlural ){
if( $Titulo[0]=='$' ){
$Titulo = $GLOBALS[mb_substr($Titulo,1)];
}
while( eSubstrCount($Titulo,'{$') > 0 ){
$p = mb_strpos( $Titulo, '{$' );
$tmp = mb_substr($Titulo,$p,mb_strpos($Titulo, '}')-$p+1);
$Titulo = str_replace($tmp,$GLOBALS[mb_substr($tmp,2,-1)],$Titulo);
}
$Titulo = str_replace('"',"'",$Titulo);
if( eSubstrCount( $Titulo, '/' ) > 0 ){
$sTitulo = '';
$sc = '';
if( $EnPlural ){
for( $i=0; $i<mb_strlen($Titulo); $i++ ){
$c = mb_substr( $Titulo,$i, 1 );
if( !($sc!='<' && $c=='/') ) $sTitulo .= $c;
$sc = $c;
}
}else{
$Mem = true;
for( $i=0; $i<mb_strlen($Titulo); $i++ ){
$c = mb_substr( $Titulo,$i, 1 );
if( $sc!='<' && $c=='/' ){
$Mem = false;
}else if( eSubstrCount(' .,:;()',$c) > 0 ){
$Mem = true;
}
if( $Mem ) $sTitulo .= $c;
$sc = $c;
}
}
$Titulo = $sTitulo;
}
$Pregunta = false;
$pos = mb_strpos($Titulo,'#');
if( $pos === false ){
}else{
if( mb_substr($Titulo,$pos-1,1)!='&' || $pos==0 ){
$Titulo = mb_substr($Titulo,0,$pos).$Delante.mb_substr($Titulo,$pos+1);
$Pregunta = true;
}
}
if( ($Delante=='QUE' || $Delante=='SELECCIONA') && $Pregunta ){
if( $Titulo[0]!='<' ){
$Titulo = '¿&nbsp;'.$Titulo;
}else{
for( $i=0; $i<mb_strlen($Titulo); $i++ ){
if( mb_substr( $Titulo,$i, 1 )=='>' ){
$Titulo = mb_substr( $Titulo,0,$i+1 ).'¿&nbsp;'.mb_substr( $Titulo,$i+1 );
break;
}
}
}
if( mb_substr($Titulo,-1)!='>' ){
$Titulo .= '&nbsp;?';
}else{
for( $i=mb_strlen($Titulo)-1; $i>0; $i-- ){
if( mb_substr( $Titulo,$i, 1 )=='<' ){
$Titulo = '&nbsp;?'.mb_substr( $Titulo, $i );
break;
}
}
}
}
$sTitulo = $Titulo;
$Titulo = '';
$ok = true;
for( $i=0; $i<mb_strlen($sTitulo); $i++ ){
$c = mb_substr( $sTitulo,$i, 1 );
if( $c=='<' && $ok ){
$ok = false;
}else if( $c=='>' && !$ok ){
$ok = true;
}else if( $ok ){
if( $c==' ' ) $c = '&nbsp;';
}
$Titulo .= $c;
}
return $Titulo;
}
function Mensaje( $texto, $Accion, $sgMensage, $exe='', $_MessageType='OK' ){
eMessage( $texto, $Accion, $sgMensage, $exe='', $_MessageType='OK' );
}
function eMessage( $texto, $Accion, $sgMensage, $exe='', $_MessageType='OK' ){
global $_User;
eInit();
?>
<script type="text/javascript">
location.replace("edes.php?D:/_tmp/pdf/lst_<?= $_User ?>.xls");
</script>
<?PHP
eEnd();
}
function getMicrotime() {
list($milisegundos,$segundos) = explode(" ",microtime());
return ( (float)$milisegundos + (float)$segundos );
}
function RellenaGsCampo( $QueTabla ){
$DimTablas = array();
$QueTabla = str_replace(' ','',$QueTabla);
if( $QueTabla=='' ) $QueTabla = '*';
if( $QueTabla == '*' ){
if( SS::count("{$_ENV['SYSDB']}gs_campo")>0 ){
echo 'ERROR: La base de datos tiene registros';
return;
}
}else{
$NomTablas = "'".str_replace(',',"','",$QueTabla)."'";
if( SS::count("{$_ENV['SYSDB']}gs_campo", "tabla in ({$NomTablas})")>0 ){
echo 'ERROR: La base de datos tiene registros';
return;
}
$DimTablas = explode(',',$QueTabla);
}
set_time_limit( 0 );
$DimAlineacion = array(
'decimal'=>'D',
'int'=>'D',
'smallint'=>'D',
'tinyint'=>'D',
'mediumint'=>'D',
'char'=>'I',
'varchar'=>'I',
'tinytext'=>'I',
'mediumtext'=>'I',
'text'=>'I',
'longtext'=>'I',
'blob'=>'I',
'date'=>'C',
'datetime'=>'C',
'timestamp'=>'C'
);
$DimUnscape = array(
'tinytext'=>'S',
'mediumtext'=>'S',
'text'=>'S',
'longtext'=>'S',
'blob'=>'S'
);
SS::query( 'show tables', [], 9);
while( $row = SS::get("num", 9) ){
if( SS::isDriver("informix") ){
if( $row[1]=='S' ){
$NomTabla = trim($row[0]).'*';
}else{
$NomTabla = trim($row[0]);
}
}else if( DB::isDriver("oci") ){
$NomTabla = mb_strtolower(trim($row[0]));
}else if( SS::isDriver('mysql,mysqli') ){
$NomTabla = trim($row[0]);
}
if( mb_substr($NomTabla,0,3) == 'gs_' ) continue;
if( eSubstrCount($NomTabla,'.') > 0 ) continue;
if( !($QueTabla=='*' || in_array( $NomTabla, $DimTablas )) ) continue;
$zOrden = 0;
$DimCampos = array();
GetTablaMySql( $NomTabla, false, $DimCampos );
echo $NomTabla.'<br>';
$DimTipos = array();
for( $n=0; $n<count($DimCampos); $n++ ){
$zTabla = $NomTabla;
$zCampo = $DimCampos[$n][0];
$zAncho = $DimCampos[$n][2];
$zDecimale = $DimCampos[$n][3];
$zAlineacion = $DimAlineacion[$DimCampos[$n][1]];
$zUnscape = $DimUnscape[$DimCampos[$n][1]];
$zOrden++;
SS::query( "insert into {$_ENV['SYSDB']}gs_campo set tabla='{$zTabla}', campo='{$zCampo}', ancho='{$zAncho}', decimales='{$zDecimale}', alineacion='{$zAlineacion}', orden='{$zOrden}', nivel=3, unescape='{$zUnscape}', etiqueta='{$zCampo}'" );
}
}
echo '- Ok -';
}
function PintaCondiciones(){
return array();
}
function InfoStructure($ExeSQL){
global $_Sql, $_DEBUG;
$_DEBUG = 30;
$sExeSQL = str_replace(' ','',$ExeSQL);
$TmpSql = explode(',',$sExeSQL);
for($i=0; $i<count($TmpSql); $i++){
if( mb_substr($TmpSql[$i],-1)==';' ) $TmpSql[$i] = trim(mb_substr($TmpSql[$i],0,-1));
if( $i>0 ) echo '<br>';
$txt = '';
if( SS::isDriver("informix") ) $txt = GetTablaInformix($TmpSql[$i], false);
if( DB::isDriver("oci") ) $txt = GetTablaOracle($TmpSql[$i], false);
if( SS::isDriver("mysql") ) $txt = GetTablaMySql($TmpSql[$i], false);
if( SS::isDriver("mysqli") ) $txt = GetTablaMySqli($TmpSql[$i], false);
if( $txt!='NoExist' ){
$txt = $xJS.'<table class=INFOTABLE cellspacing=1px cellpadding=3px border=0px>'.$txt.'</TABLE>';
}
echo $txt;
}
eEnd();
}
function _CalcTHColSpan(&$_THCOLSPAN, &$THColPuntero, &$THCol, $_Form, $NCampos){
for($n=0; $n<$NCampos; $n++) $THCol[$n] = array('',0,3);
for($n=0; $n<count($_THCOLSPAN); $n++){
$tmp = explode(',',$_THCOLSPAN[$n]);
for($i=3; $i<count($tmp); $i++){
$tmp[$i] = trim($tmp[$i]);
$tmp[2] .= ','.$tmp[$i];
}
for($i=0; $i<3; $i++) $tmp[$i] = trim($tmp[$i]);
for($i=0; $i<$NCampos; $i++){
$xTmp = explode(' ',$_Form[$i][1]);
$iz = trim($xTmp[0]);
$de = trim($xTmp[count($xTmp)-1]);
$ConLLaves = "";
if( eSubstrCount($_Form[$i][17],'{')==1 ){
$tmp2 = str_replace('{', ',', $_Form[$i][17]);
$tmp2 = str_replace('}', '', $tmp2);
$tmp2 = explode(',', $tmp2);
$ConLLaves = trim($tmp2[count($tmp2)-1]);
}
if( $ConLLaves==$tmp[0] || (is_numeric($tmp[0]) && $i==$tmp[0]) || $_Form[$i][1]==$tmp[0] || ( mb_strtoupper($_Form[$i][3][0])=='S' && ($_Form[$i][1]=='nm_'.mb_substr($tmp[0],4) || $_Form[$i][1] == 'nm_'.mb_substr($tmp[0],3)) ) || $de==$tmp[0] ){
$THCol[$i][0] = $tmp[2];
$THCol[$i][1] = 1;
$THCol[$i][2] = 2;
$THCol[$i][3] = 0;
if( $_Form[$i][1]<>$tmp[1] ){
for($p=$i+1; $p<$NCampos; $p++){
$ConLLaves2 = "";
if( eSubstrCount($_Form[$p][17],'{')==1 ){
$tmp2 = str_replace('{', ',', $_Form[$p][17]);
$tmp2 = str_replace('}', '', $tmp2);
$tmp2 = explode(',', $tmp2);
$ConLLaves2 = trim($tmp2[count($tmp2)-1]);
}
if( eSubstrCount($_Form[$p][6],"*")==0 ) $THCol[$i][1]++;
$THCol[$p][1] = 1;
$THCol[$p][2] = 0;
$THCol[$p][3] = 0;
$xTmp = explode(' ',$_Form[$p][1]);
$iz = trim($xTmp[0]);
$de = trim($xTmp[count($xTmp)-1]);
if( $ConLLaves2==$tmp[1] ){
break;
}
if( (is_numeric($tmp[1]) && $p==$tmp[1]) || $_Form[$p][1]==$tmp[1] || ( mb_strtoupper($_Form[$p][3][0])=='S' && ($_Form[$p][1]=='nm_'.mb_substr($tmp[1],4) || $_Form[$p][1]=='nm_'.mb_substr($tmp[1],3)) ) || $de==$tmp[1] ){
break;
}
}
}
break;
}
}
}
}
function eNewPassword($LonClave){
$str =  "ABCDEFGHJKLMNPQRSTUVWXYZ"."123456789".
"abcdefghjklmnpqrstuvwxyzi".
"ñÑ,.;-_*:!¡#$%&()[]{}";
$MinNum = 2;
$MinChr = 2;
$nMinNum = 0;
$nMinChr = 0;
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
return $Pass;
}
?>