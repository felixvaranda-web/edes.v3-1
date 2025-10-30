<?PHP
class Device {
public static function sendLogin( $RegisteredDevice ){
if( !$RegisteredDevice ){
return;
}
if( SS::count("{$_ENV['SYSDB']}gs_device") == 0 ){
return;
}
if( empty($_SESSION["device"]) || !self::check($_SESSION["device"]) ){
self::sendError(500, "Internal Server Error");
}
}
public static function isRegistered( $RegisteredDevice, $email ){
$tableDevice = "{$_ENV['SYSDB']}gs_device";
if( !$RegisteredDevice || SS::count($tableDevice) == 0 ){
return true;
}
$data = SS::selectOne("select device, status, email, dt_life from {$tableDevice}", [
"device" => $_SESSION["device"],
"email"  => $email
]);
if( empty($data["device"]) ){
return false;
}
if( $data["status"]=="S" && $data["dt_life"] < $_ENV["cdi"] ){
return false;
}
if( $data["status"]=="D" ){
return false;
}
return true;
}
public static function errorStatistics( $RegisteredDevice, $field ){
$tableDevice = "{$_ENV['SYSDB']}gs_device";
if( !$RegisteredDevice || SS::count($tableDevice) == 0 ){
return;
}
$data = SS::selectOne("select * from {$tableDevice}", [
"device" => $_SESSION["device"]
]);
$data[$field] = $data[$field]+1;
SS::update($tableDevice, [ $field => $data[$field] ], [ "device" => $_SESSION["device"] ] );
}
public static function loggedIn( $RegisteredDevice, $email, $RenewDevice ){
if( !$RegisteredDevice ){
return;
}
$now = $_ENV["cdi"];
$tableDevice = "{$_ENV['SYSDB']}gs_device";
if( SS::count($tableDevice) == 0 ){
$device = self::new();
$js = "localStorage.setItem('s-device', '{$device}');";
SS::insert($tableDevice, [
"device"		=> $device,
"email"			=> $email,
"dt_insert"		=> $now,
"dt_activate"	=> $now,
"dt_life"		=> $now,
"dt_lastentry"	=> $now,
"renew_device"	=> date("Y-m-d H:i:s", strtotime("+{$RenewDevice} days", strtotime($now))),
"status"		=> "A",
"accesses"		=> 1
]);
return $js;
}
$data = SS::selectOne("select * from {$_ENV['SYSDB']}gs_device", [
"device" => $_SESSION["device"]
]);
$updates = [ "dt_lastentry" => $now ];
$updates["accesses"] = $data["accesses"] + 1;
$js = "";
if( empty($data["renew_device"]) || $data["renew_device"] < $now ){
$device = self::new();
$js = "localStorage.setItem('s-device', '{$device}');";
$updates["device"] = $device;
$updates["renew_device"] = date("Y-m-d H:i:s", strtotime("+{$RenewDevice} days", strtotime($now)));
}
if( $data["status"]=="S" ){
$updates["dt_activate"] = $now;
$updates["status"] = "A";
}
SS::update($tableDevice, $updates, [ "device" => $_SESSION["device"] ] );
return $js;
}
private static function new(){
$prefix = dechex( (int)date("ymd") );
$device = S::randon(51, '/^[a-zA-Z0-9]$/');
$crc    = substr( dechex( crc32($prefix . $device) ), -4 );
return $prefix . $device . $crc;
}
private static function check( $device ){
$crc = substr( dechex( crc32( substr($device, 0, -4) ) ), -4);
return $crc == substr($device, -4);
}
private static function sendError($code, $text){
eInit();
header("HTTP/1.0 {$code} {$text}");
http_response_code( $code*1 );
$sc = explode(",", $_ENV["login"]["delay-in-error"]);
sleep(rand($sc[0], $sc[1]));
eEnd();
}
}