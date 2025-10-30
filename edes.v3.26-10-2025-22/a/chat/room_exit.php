<?PHP
$room = $_GET["room"]*1;
$user = S::$_User;
$cdi = date('Y-m-d H:i:s');
SS::query("select * from {$_ENV['SYSDB']}gs_chat_room where cd_gs_chat_room={$room} and cdi_close is null");
$r = SS::get();
if( $r["cd_gs_chat_room"]!=$room || $r["type_room"]=="S" ) exit;
if( $r["type_room"]=="P" ){
$ok = false;
$dim = array();
SS::query("select * from {$_ENV['SYSDB']}gs_chat_user where cd_gs_room={$room} and cdi_end is null");
while( $r = SS::get()){
if( $r["cd_gs_user"]==$user ) $ok = true;
$dim[] = $r["cd_gs_user"];
}
if( $ok ){
SS::query("update {$_ENV['SYSDB']}gs_chat_user set cdi_end='{$cdi}'   where cd_gs_room={$room} and cdi_end   is null");
SS::query("update {$_ENV['SYSDB']}gs_chat_room set cdi_close='{$cdi}' where cd_gs_room={$room} and cdi_close is null");
for($n=0; $n<count($dim); $n++){
eNodeSend(array("room"=>$room, "type"=>"user_delete", "room_pk":$room, "user_pk":$dim[$n]));
}
}
}else if( $r["type_room"]=="G" ){
SS::query("update {$_ENV['SYSDB']}gs_chat_user set cdi_end='{$cdi}' where cd_gs_room={$room} and cd_gs_user={$user} and cdi_end is null");
eNodeSend(array("room"=>$room, "type"=>"user_delete", "room_pk":$room, "user_pk":$user));
if( SS::count("{$_ENV['SYSDB']}gs_chat_user", "cd_gs_room={$room} and cd_gs_user={$user} and cdi_end is null")==0 ){
SS::query("update {$_ENV['SYSDB']}gs_chat_room set cdi_close='{$cdi}' where cd_gs_room={$room} and cdi_close is null");
}
}else{
exit;
}
?>