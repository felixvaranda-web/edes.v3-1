<?PHP
$_TITULO = str_replace( ' onclick="_SetCaption(\'TD\')"', '', $_POST['_TITULO'] );
$_TITULO = eEntityDecode($_TITULO, false);
$_IMPRIMIR = eEntityDecode($_POST['_IMPRIMIR'], false);
$_TREG = $_POST['_TREG'];
$ePagina = str_replace("\n", ' ', $_POST['_TITULOTXT']);
$ePagina = str_replace("\r", ' ', $ePagina);
$Quitar = array(
array('onresize='		,'on_resize='),
array('onmouseleave='	,'on_mouseleave='),
array('onmousemove='	,'on_mousemove'),
array('onmousedown='	,'on_mousedown'),
array('oncontextmenu='	,'on_contextmenu'),
array('onclick='		,'on_click')
);
for($n=0; $n<count($Quitar); $n++) $_IMPRIMIR = str_replace( $Quitar[$n][0], $Quitar[$n][1], $_IMPRIMIR );
$txt = eHTML('$logprint.php', "", "", true);
$txt .= <<<EOT
<LINK REL='stylesheet' HREF='".$_SESSION["_PathCSS"]."/list.css' TYPE='text/css'>
<LINK REL='stylesheet' HREF='".$_SESSION["_PathCSS"]."/list_print.css' TYPE='text/css' MEDIA='print'>
</HEAD>
<BODY>
{$_TITULO}
{$_IMPRIMIR}
</BODY>
</HTML>
EOT;
$sFile = '../_tmp/log/lst_'.S::$_User.'.htm';
file_put_contents($sFile, $txt);
if( $_SESSION["sql"]['statistics'] ){
if( SETUP::$LogTrace["D*"] || SETUP::$LogTrace["DPRN"] ){
SS::insert("{$_ENV['SYSDB']}gs_acceso", [
"cd_gs_toperacion"	=> 'DOC',
"conexion"			=> $_SESSION["_Connection_"],
"objeto"			=> 'D',
"modo"				=> 'l',
"edf"				=> '',
"tabla"				=> 'PRN',
"parametros"		=> '',
"pagina"			=> $ePagina,
"parametro"			=> '',
"registros"			=> $_TREG,
"cd_gs_user"		=> $_User,
"cd_gs_node"		=> $_SESSION["_Node"],
"cdi"				=> date('Y-m-d H:i:s')
]);
$SerialDOC = SS::id();
$Dir = eGetCWD();
if( LINUX_OS ){
$ExeZip = "zip -9 -j -b {$Dir} ".SETUP::$LogDownload['LogFileDownload'].$SerialDOC." ".eScript($sFile);
}else{
$ExeZip = eScript('$win/zip.exe')." -9 -j -b {$Dir} ".SETUP::$LogDownload['LogFileDownload'].$SerialDOC." ".eScript($sFile);
}
$Dim = array();
exec($ExeZip, $Dim);
}
if( SETUP::$LogHistory['LogGsAccessFile']!='' ){
error_log(
date('Y-m-d H:i:s')."|{$_User}|".$_SESSION["_Node"]."|".$_SESSION["_Connection_"]."|Imprimir listado\n"
,3
,SETUP::$LogHistory['LogGsAccessFile']
);
}
}
echo "ok";
eEnd();
?>