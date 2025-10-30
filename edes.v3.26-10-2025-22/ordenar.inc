<?PHP
$NomTabla = $_DBTABLE;
global $indice, $vector, $vectorniveles;
function desplazamiento( &$vector, $inicio, $fin, $init ){
$i = $fin-$inicio;
for($j=$fin; $j>=$inicio; $j--){
$vector[$init+$i] = $vector[$j];
$i--;
}
}
function ordenar(){
global $vector, $vectorniveles, $indice, $NomTabla;
$n = count($vector);
if( $n==0 ){
$idpadre=0;
$nivelpadre=-1;
}else{
$idpadre=$vector[$indice];
$nivelpadre=$vectorniveles[$indice];
}
DB::query("select count(*) from {$NomTabla} where padre={$idpadre} order by nm_{$NomTabla}");
$desplz = DB::get("num")[0];
if( $desplz > 0 ){
DB::query("select cd_{$NomTabla} from {$NomTabla} where padre={$idpadre} order by nm_{$NomTabla}");
if( !isset($indice) ){
$i=0;
}else{
desplazamiento($vector, $indice+1, $n-1, $desplz+$indice+1);
desplazamiento($vectorniveles, $indice+1, $n-1, $desplz+$indice+1);
$i = $indice+1;
}
while( $row=DB::get("num") ){
$vector[$i] = $row[0];
$vectorniveles[$i] = $nivelpadre+1;
$i++;
}
}
if( !isset($indice) ){
$indice=0;
ordenar();
}else{
$indice++;
$n = count($vector);
if( $indice < $n ){
ordenar();
}else{
for($i=0; $i<$n; $i++){
DB::query("update {$NomTabla} set orden={$i}, nivel={$vectorniveles[$i]} where cd_{$NomTabla}={$vector[$i]}");
}
}
}
}
ordenar();
?>