<?PHP
eCheckUser();
eInclude( $_Sql );
if( $_GET['O']==$_ENV['ON'] ){
SS::query("insert into {$_ENV['SYSDB']}gs_permission (cd_gs_user,cd_gs_tpermission) values (".$_GET['U'].','.$_GET['P'].')' );
}else{
SS::query("delete from {$_ENV['SYSDB']}gs_permission where cd_gs_user=".$_GET['U'].' and cd_gs_tpermission='.$_GET['P'] );
}
?>