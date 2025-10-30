<?PHP
die("ok");
list($location, $file, $type) = explode("|", $_POST["definition"]);
if( $location=="edfi" ){
replaceLenguaje($file, $_POST["text"]);
}else{
file_put_contents($file.".ok", $_POST["text"]);
}
?>
<script>
alert("Grabado");
</script>
<?PHP
eEnd();
function replaceLenguaje($file, $text){
$lineas = @file($file);
if( $lineas === false ){
file_put_contents($file.".ok", $text);
return null;
}
$dentroComentarioBloque = false;
$memLng = false;
$lineIni = -1;
$lineEnd = -1;
$nLine = -1;
for($i=0; $i<count($lineas); $i++){
$linea = trim($lineas[$i]);
if ($dentroComentarioBloque) {
$posFin = strpos($linea, '*/');
if ($posFin !== false) {
$linea = substr($linea, $posFin + 2);
$dentroComentarioBloque = false;
} else {
continue; // La línea está completamente dentro del comentario de bloque
}
}
$posInicio = strpos($linea, '/*');
isma línea (comentario en línea), solo tomamos lo de antes
$posFin = strpos($linea, '*/', $posInicio + 2);
if ($posFin !== false) {
$linea = substr($linea, 0, $posInicio) . substr($linea, $posFin + 2);
} else {
$linea = substr($linea, 0, $posInicio);
$dentroComentarioBloque = true;
}
}
if( $linea[0] == '.' ){
continue;
}
$posDobleBarra = strpos($linea, '/'.'/');
if ($posDobleBarra !== false) {
$linea = substr($linea, 0, $posDobleBarra);
}
if( substr(trim($linea),0,2) == '/'.'/' ){
continue;
}
if( strtolower(substr($linea,0,6)) == '[note]' ){
break;
}
if( strtolower(substr($linea,0,10))=='[language]' ){
$lineIni = $i;
$memLng = true;
continue;
}
if( $linea[0]=='[' || $linea[0]=='¿' || substr($linea,0,2)=='#(' || substr($linea,0,3)=='#!(' ){
$lineEnd = $i-1;
break;
}
}
array_splice($lineas, $lineIni, $lineEnd - $lineIni + 1);
$newText = $text ."\n\n\n" . implode("", $lineas);
file_put_contents($file.".okok", $newText);
}