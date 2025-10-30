<?PHP
if( $_GET["_LANG"]!="" ){
$_SESSION["_LANGUAGE_"] = $_GET["_LANG"];
$_SESSION["_LANGUAGE_SUFFIX"] = "_{$_GET['_LANG']}";
}
$file = eScript($_JSINCLUDEFILE);
if( file_exists($file) ) @unlink($file);
if( $_GET["_LANG"]!="" ){
echo "top._FIELDS=[]; if(top.S('#eIDIOMA').length)top._setLanguage('".$_SESSION["_LANGUAGE_"]."');";
SS::query("update {$_ENV['SYSDB']}gs_user set cd_gs_language='{$_GET['_LANG']}' where cd_gs_user='{$_ENV['user']}'");
}else{
error_log("top._FIELDS=[]; if(top.S('#eIDIOMA').length)top._setLanguage('".$_SESSION["_LANGUAGE_"]."');", 3, $file);
}
setCookie("e-language", $_SESSION["_LANGUAGE_"], time()+(86400*365));
echo "if( top.S('HTML').length ){";
echo "top.S('HTML').attr('lang', '".$_SESSION["_LANGUAGE_"]."');";
echo "}";
$campo = "text_".$_SESSION["_LANGUAGE_"];
SS::query("select * from {$_ENV['SYSDB']}gs_storage where type_storage='x' order by cdi");
while( $r = SS::get() ){
$text = addslashes($r[$campo]);
if( $text=="" ) $text = addslashes($r["text_es"]);
$text = str_replace(array(CHR10, CHR13), array("&#0A;", "&#0D;"), $text);
if( $_GET["_LANG"]!="" ){
echo "localStorage.setItem('e-{$r['type_storage']}{$r['key_storage']}', '{$text}');";
}else{
error_log("localStorage.setItem('e-{$r['type_storage']}{$r['key_storage']}', '{$text}');", 3, $file);
}
}
if( $_GET["_LANG"]!="" ){
echo "localStorage.setItem('e-language', '".$_SESSION["_LANGUAGE_"]."');";
}else{
error_log("localStorage.setItem('e-language', '".$_SESSION["_LANGUAGE_"]."');", 3, $file);
}
if( $_GET["_LANG"]!="" ){
$idioma = SS::selectOne("select * from {$_ENV['SYSDB']}gs_language where tf_translation='{$_ENV['ON']}' and cd_gs_language='".$_SESSION["_LANGUAGE_"]."'")['nm_gs_language'];
echo "top.S.setup.language = '_".$_SESSION["_LANGUAGE_"]."';";
echo "top.S.info(top.S.lng(302, '<b>{$idioma}</b>'));";
eEnd();
}
?>