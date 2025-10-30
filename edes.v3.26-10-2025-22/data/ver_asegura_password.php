<?php
function ver_asegura_passwmb_ord(){
SS::query("select cd_gs_user,login,pass from {$_ENV['SYSDB']}gs_user", [], 1);
while( $r = SS::get(1) ){
$user = $r["cd_gs_user"];
$pass = mb_strtoupper(md5(trim($r["login"]).$r["pass"]));
$sql = "update {$_ENV['SYSDB']}gs_user set pass='{$pass}' where cd_gs_user='{$user}'";
SS::query($sql);
}
}
ver_asegura_passwmb_ord();
?>