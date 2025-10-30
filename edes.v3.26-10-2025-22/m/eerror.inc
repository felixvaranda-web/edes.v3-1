<?PHP
$buffer = ob_get_contents();
ob_end_clean();
ob_start();
debug_print_backtrace();
$trace = "\t".str_replace(CHR10, CHR10."\t", trim(ob_get_contents()));
ob_end_clean();
ob_start();
if( empty($Error) ){
$Error = "Indeterminate error";
}
$request = "";
if( !empty($_SERVER["REQUEST_URI"]) ){
$request = $_SERVER["REQUEST_URI"];
}
$tab = "\t";
$codErr = mb_chr(rand(65,90)).mb_chr(rand(65,90)).mb_chr(rand(65,90)).mb_chr(rand(65,90));
$infoDevelopmet = "";
if( !empty($_SESSION["_D_"]) && !empty($masInfo) ){
$infoDevelopmet = $tab."Add Info..: ".$masInfo."\n";
}
$LogTxt = date('Y-m-d H:i:s')."\n".
$tab.'User......: '.(S::$_User ?? "")."\n".
$tab."Error.....: [{$codErr}] {$Error}.\n".
$infoDevelopmet.
$tab.'RequestURI: '.explode("edes.php?", $request."edes.php?")[1]."\n".
$trace."\n";
eErrorToFile($LogTxt);
$dim = explode("\n", var_export($_GET, true));
array_shift($dim);
array_pop($dim);
$LogTxt .=  "{$tab}GET: ".implode("\n".$tab.$tab, $dim);
$dim = explode("\n", var_export($_POST, true));
array_shift($dim);
array_pop($dim);
$LogTxt .=  "\n{$tab}POST: ".implode("\n".$tab.$tab, $dim);
if( !empty($nameArray) ){
$dim = explode("\n", var_export($addArray, true));
array_shift($dim);
array_pop($dim);
$LogTxt .=  "\n{$tab}{$nameArray}: ".implode("\n".$tab.$tab, $dim);
}
DB::rollBackAll();
DB::closeAll();
eInit();
?>
<script>
var oWin = top.S(window.frameElement.parentNode).toTag(".WINDOW");
if( oWin != null ){
top.S.windowView(window);
}
<?PHP
if( !empty($_SESSION["_D_"]) ){
global $OriFichero;
echo "setTimeout(function(){";
echo "document.body.oncontextmenu = function(){top.gsEdit(window, '{$OriFichero}');}";
echo "}, 100);";
}else{
$LogTxt = "Error[{$codErr}]";
}
echo "</script>";
$LogTxt = "<pre>{$LogTxt}</pre>";
die(str_replace("\n", "<br>", $LogTxt));
?>