<?PHP
$type	= $_POST['type'];
$field	= $_POST['field'];
$source	= $_POST['source'];
$value	= $_POST['value'];
$serial	= $_POST['serial'];
$pk		= $_POST['pk'];
$exe	= $_POST['exe'];
$dim = SYS::getLabel($source, "DB, DBTable, DBSerial, UploadFile, AddCode", true);
$DB		  = $dim["DB"][0];
$DBTable  = $dim["DBTable"][0];
$DBSerial = $dim["DBSerial"][0];
if( !empty($DB) ){
DB::open($DB);
DB::update($DBTable, [$field => $value], [$DBSerial => $serial]);
}else{
SS::update($DBTable, [$field => $value], [$DBSerial => $serial]);
}
for($i=0; $i<count($dim["UploadFile"]); $i++){
if( $dim["UploadFile"][$i][0]!=$field ){
continue;
}
$nameFile = eScript($dim["UploadFile"][$i][1])."/";
if( !empty($dim["UploadFile"][$i][6]) ){
$nameFile .= $dim["UploadFile"][$i][6];
}
$nameFile .= "{$serial}.".mb_strtolower(eFileType($value));
rename($oFile, $nameFile);
}
if( mb_strlen($exe)>5 ){
$exe = eScript($exe);
exec("php {$exe} {$DBTable} {$field} {$DBSerial} {$serial} {$nameFile}> /dev/null &", $lines);
}
?>