<?PHP
eTrace("Unload: gs_icon.unl : "._ExportGsStorage("gs_icon"));
function _ExportGsStorage($tabla){
SS::query("select * from {$tabla}", [], 1);
$fd = fopen(DIREDES."web/edesweb/{$tabla}.unl", 'w');
$TReg = 0;
$Pipa = false;
while( $linea=SS::get("num", 1) ){
$txt = '';
if( $Pipa ) $txt .= "\n";
$Pipa = false;
foreach($linea as $valor){
if( $Pipa ){
$txt .= '|';
}else{
$Pipa = true;
}
$valor = str_replace(CHR10, '{&#10;}', $valor);
$valor = str_replace(CHR13, '{&#13;}', $valor);
$valor = str_replace('"', '&quot;', $valor);
$valor = str_replace('|', '{&#124;}', $valor);
$txt .= trim((string)$valor);
}
fputs($fd, $txt);
$TReg++;
}
fclose($fd);
return $TReg;
}
?>