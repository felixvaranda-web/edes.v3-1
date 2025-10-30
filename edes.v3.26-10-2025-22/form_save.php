<?PHP
error_log(date("Y-m-d H:i:s | ").str_pad($_POST["cd_gs_form"],6)." | ".str_pad(S::$_User,6)." | ".$_POST["DATOS"]."\n", 3, "../_datos/portfolio/form_".$_SESSION["_Node"].".data");
$data = str_replace(
array(  '"'  ,   "'"  ,   '<'  ,   '>'  ,  '\\'  , mb_chr(0)),
array('&#34;', '&#39;', '&#60;', '&#62;', '&#92;',   ''  ),
$_POST["DATOS"]
);
SS::query("update {$_ENV['SYSDB']}gs_form set data='{$data}', dt_update='{$hoy}' where cd_gs_form={$_POST['cd_gs_form']} and cd_gs_node=".$_SESSION["_Node"]);
eExeScript("top.S.info('Impreso grabado',3,'ok');");
?>