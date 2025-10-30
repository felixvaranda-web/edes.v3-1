<?PHP
if( $GLOBALS['_gsID']!=getmypid() ) exit;
global $Dir_, $_Sql, $_User, $_Node, $_Connection_, $_Tree;
if( $_Sql=='' ){
$tmpFile = '../_datos/config/sql.ini';
include($tmpFile);
_ShowError($php_errormsg, $tmpFile);
}
SS::query("update {$_ENV['SYSDB']}gs_conexion set cdi_fin='".date('Y-m-d H:i:s')."' where conexion='".$_SESSION["_Connection_"]."'");
eInit();
echo '<script type="text/javascript">top.Terminar();</script>';
eEnd();
?>