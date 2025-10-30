<?PHP
$txt = '';
foreach($_POST as $k=>$v) if( $k[0]!='_' ) $txt .= $k.'='.$v."\n";
$Md5 = md5($txt);
if( $txt=='' ) $Md5='';
SS::query( "select seconds from {$_ENV['SYSDB']}gs_progress where script='{$_POST['_SOURCE_']}' and md5='{$Md5}'" );
list( $ns ) = SS::get("num");
$ns = $ns*1;
if( $ns==0 ){
SS::query( "select sum(seconds),count(*) from {$_ENV['SYSDB']}gs_progress where script='{$_POST['_SOURCE_']}'" );
list( $ns, $c ) = SS::get("num");
$ns = $ns*1;
if( $c > 0 ) $ns = round($ns/$c);
}
$ns += 1;
echo '<script type="text/javascript">';
echo 'var Obj = window.frameElement.WOPENER;';
if( $ns>0 ) echo "Obj._Progress = {$ns};";
if( $_POST['_FunctionSubmit']<>'' ){
echo 'Obj.'.$_POST['_FunctionSubmit'].'();';
}else{
echo 'Obj._Submit();';
}
echo '</script>';
eEnd();
?>