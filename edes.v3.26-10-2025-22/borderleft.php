<?PHP
$file = str_replace("/", "_", $_POST["source"]).".bleft.".S::$_User;
$_BorderLeft = SETUP::$List['ShowInColumns'] ?? false;
if( $_BorderLeft ){
if( $_POST["px"] == 0 ){
file_put_contents("../_datos/usr/{$file}", $_POST["px"]);
}else if( file_exists("../_datos/usr/{$file}") ){
@unlink("../_datos/usr/{$file}");
}
}else{
if( $_POST["px"] > 0 ){
file_put_contents("../_datos/usr/{$file}", $_POST["px"]);
}else if( file_exists("../_datos/usr/{$file}") ){
@unlink("../_datos/usr/{$file}");
}
}
die("Configuración guardada");
?>