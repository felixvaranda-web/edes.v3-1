<?PHP
if( S::$_User==-1 || $_gsID!=getmypid() ) exit;
if( isset($_POST["_index_"]) && isset($_POST["_script_"]) ){
$_POST["_index_"] = str_replace("&#39;", "'", $_POST["_index_"]);
if( $_POST["_script_"]!="" ) include(eScript($_POST["_script_"]));
list($pkField, $pkValue) = explode("=", $_POST["_index_"]);
if( $pkValue=="''" || $pkValue=="'0'" ){
$campos = "";
$valores = "";
foreach($_POST as $k=>$v){
if( $k[0]!="_" ){
if( $campos!="" ){
$campos .= ", ";
$valores .= ", ";
}
$campos .= $k;
if( $v=="" ){
$valores .= "NULL";
}else{
$valores .= "'{$v}'";
}
}
}
$sql = "insert into {$_POST['_dbtable_']} ($campos) values ($valores)";
DB::query($sql);
$pk = DB::id();
die($pk."");
}else{
$set = "";
foreach($_POST as $k=>$v){
if( $k[0]!="_" ){
if( $set!="" ) $set .= ", ";
$set .= $k."=";
if( $v=="" ){
$set .= "NULL";
}else{
$set .= "'{$v}'";
}
}
}
$sql = "update {$_POST['_dbtable_']} set {$set} where {$_POST['_index_']}";
DB::query($sql);
die("ok");
}
}
list($Tabla, $Modificar, $Buscar, $MasModificar, $GrabarLog, $NomInclude, $NewDDBB) = explode('|', urldecode($_SERVER['QUERY_STRING']));
list($GrabarLog ) = explode('&_CALL', $GrabarLog);
list($NomInclude) = explode('&_CALL', $NomInclude);
list($NewDDBB	) = explode('&', $NewDDBB);
$NewDDBB = trim($NewDDBB);
if( isset($_GET['_DB']) && $_GET['_DB']!='' ) $NewDDBB = $_GET['_DB'];
$_DEBUG = true;
if( $_DEBUG ){
eTrace( urldecode($_SERVER['QUERY_STRING']) );
eTrace( 'Tabla.......: '.$Tabla );
eTrace( 'Modificar...: '.$Modificar );
eTrace( 'Buscar......: '.$Buscar );
eTrace( 'MasModificar: '.$MasModificar );
eTrace( 'GrabarLog...: '.$GrabarLog );
eTrace( 'NomInclude..: '.$NomInclude );
eTrace( 'DDBB........: '.$NewDDBB );
}
if( $NewDDBB!='' ){
if( eSubstrCount(str_replace('\\','/',$NewDDBB),'/')==0 ) $NewDDBB = '/_datos/config/'.$NewDDBB;
if( eSubstrCount($NewDDBB,'.')==0 ) $NewDDBB .= '.ini';
if( $NewDDBB[0]=='~' ){
$sqlIni = str_replace('~', '../..', $NewDDBB);
}else{
$sqlIni = eScript($NewDDBB);
}
DB::open( $sqlIni );
}else{
DB::sameDataBase("SS");
}
list(,$Valor) = explode("'", $Modificar);
$_DBTABLE = $Tabla;
$_DBSET = $Modificar;
if( trim($MasModificar)!='' ) $_DBSET .= ', '.$MasModificar;
$_DBWHERE = $Buscar;
if( $NomInclude!='' ) include(eScript($NomInclude));
if( DB::count($_DBTABLE, $_DBWHERE)==1 ){
if( $MasModificar!='' ) $MasModificar = ','.$MasModificar;
if( $GrabarLog=='L' ){
$Dim = explode( ' and ', $_DBWHERE );
$nValor = '';
for( $n=0; $n<count($Dim); $n++ ){
list( $Log, $Valor ) = explode('=',$Dim[$n]);
$Valor = trim($Valor);
if( $Valor[0]=='"' || $Valor[0]=="'" ) $Valor = mb_substr( $Valor, 1, -1 );
$nValor .= $Valor;
}
${$Log} = $nValor;
}
if( DB::isDriver("oci") ){
$_DBSET = str_replace( CHR92."'", "''", $_DBSET );
}
if( DB::query("update {$_DBTABLE} set {$_DBSET}{$MasModificar} where {$_DBWHERE}") ){
?>
<SCRIPT type="text/javascript">
top.eAlert( top.S.lng(212), top.eLng(29), 'A', 'E', window.frameElement.WOPENER.eEditListResetCell );
</SCRIPT>
<?PHP
eEnd();
}
}else{
?>
<SCRIPT type="text/javascript">
top.eAlert( top.S.lng(212), top.eLng(31), 'A', 'E', window.frameElement.WOPENER.eEditListResetCell );
</SCRIPT>
<?PHP
eEnd();
}
eHTML('$mod_td.gs');
$Color = '#001296';
?>
<SCRIPT type="text/javascript">
top.S("body").tip(top.eLng(27), 1);
</SCRIPT>
</HEAD><BODY>Ok</BODY></HTML>
<?PHP
eEnd();
?>