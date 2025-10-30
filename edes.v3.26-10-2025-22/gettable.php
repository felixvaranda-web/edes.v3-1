<?PHP
if( !function_exists("getTable") ){
function getTable($table, $Multi, &$DimCampos=NULL){
$txt = "";
if( SS::isDriver("informix") ) $txt = GetTablaInformix($table, $Multi, $DimCampos);
if( DB::isDriver("oci") ) $txt = GetTablaOracle($table, $Multi, $DimCampos);
if( SS::isDriver('mysql,mysqli') ) $txt = GetTablaMySql($table, $Multi, $DimCampos);
if( $DimCampos==NULL ) echo $txt;
}
}
if( !function_exists("GetTablaMySql") ){
function GetTablaMySql($table, $Multi, &$DimCampos=NULL){
if( !qExists($table) ) return "NoExist";
if( $Multi ){
$xLinea = "<TR><TH colspan=2 Max=1 ondblclick='MaxDef(this)' onmousedown='Mover(this)'>TABLE <B>{$table}</B></TH>";
}else{
$xLinea = "<TR><TH colspan=2>TABLE <B>{$table}</B></TH>";
}
$txt = '';
if( mb_substr_count($table, '.')>0 ){
list($database, $oTable) = explode('.', $table);
SS::query("select database()", [], 1);
$aDatabase = SS::get("num", 1)[0];
if( $database==$aDatabase ){
$table = $oTable;
}
}
SS::query("SHOW FIELDS FROM {$table}");
while( $row = SS::get() ){
list($zTipo, $zLong) = explode('(', $row['Type']);
list($zLong) = explode(')', $zLong);
if( $zLong=='' ){
if( $zTipo=='date' ) $zLong = 10;
if( $zTipo=='datetime' ) $zLong = 19;
if( $zTipo=='timestamp' ) $zLong = 19;
if( $zTipo=='text' ) $zLong = 2000;
if( $zTipo=='tinytext' ) $zLong = 2000;
if( $zTipo=='mediumtext' ) $zLong = 2000;
if( $zTipo=='longtext' ) $zLong = 2000;
}
list($zLong, $zDecimal) = explode(',', $zLong);
$DimCampos[] = array($row['Field'], $zTipo, $zLong, $zDecimal);
$txt .= "<TR><TD><B>{$row['Field']}</B><TD nowrap> {$row['Type']}";
if(isset($row["Default"]) && (!empty($row["Default"]) || $row["Default"]=="0")) $txt .= " DEFAULT '{$row['Default']}'";
if($row["Null"]!="YES") $txt .= " NOT NULL";
if($row["Extra"]!=""){
if( trim($row["Extra"])=="'CURRENT_TIMESTAMP'" ) $row["Extra"] = 'CURRENT_TIMESTAMP';
$txt .= " {$row['Extra']}";
}
}
if( $txt=="" ) die('La tabla <b>"'.$table.'"</b> no existe.');
$txt = $xLinea.$txt;
$txt = preg_replace('/,$/', "", $txt);
SS::query("SHOW KEYS FROM {$table}");
while( $row = SS::get() ){
$kname = $row['Key_name'];
if(($kname!="PRIMARY") && ($row['Non_unique'] == 0))
$kname="UNIQUE|{$kname}";
if(!isset($index[$kname]))
$index[$kname] = array();
$index[$kname][] = $row['Column_name'];
}
foreach($index as $x=>$columns){
$txt .= "<TR><TD colspan=2 nowrap>";
if($x == "PRIMARY")
$txt .= "   PRIMARY KEY (" . implode(", ", $columns) . ")";
elseif (mb_substr($x,0,6) == "UNIQUE")
$txt .= "   UNIQUE ".mb_substr($x,7)." (" . implode(", ", $columns) . ")";
else
$txt .= "   KEY $x (" . implode(", ", $columns) . ")";
}
return(stripslashes($txt));
}
}
if( !function_exists("GetTablaMySqli") ){
function GetTablaMySqli($table, $Multi, &$DimCampos=NULL){
if( !qExists($table) ) return "NoExist";
if( $Multi ){
$xLinea = "<TR><TH colspan=2 Max=1 ondblclick='MaxDef(this)' onmousedown='Mover(this)'>TABLE <B>{$table}</B></TH>";
}else{
$xLinea = "<TR><TH colspan=2>TABLE <B>{$table}</B></TH>";
}
$txt = "";
SS::query("SHOW FIELDS FROM {$table}");
while( $row = SS::get() ){
list($zTipo, $zLong) = explode('(', $row['Type']);
list($zLong) = explode(')',$zLong);
if( $zLong=='' ){
if( $zTipo=='date' ) $zLong = 10;
if( $zTipo=='datetime' ) $zLong = 19;
if( $zTipo=='timestamp' ) $zLong = 19;
if( $zTipo=='text' ) $zLong = 2000;
if( $zTipo=='tinytext' ) $zLong = 2000;
if( $zTipo=='mediumtext' ) $zLong = 2000;
if( $zTipo=='longtext' ) $zLong = 2000;
}
list($zLong, $zDecimal) = explode(',', $zLong);
$DimCampos[] = array($row['Field'], $zTipo, $zLong, $zDecimal);
$txt .= "<TR><TD><B>{$row['Field']}</B><TD nowrap> {$row['Type']}";
if(isset($row["Default"]) && (!empty($row["Default"]) || $row["Default"] == "0")) $txt .= " DEFAULT '{$row['Default']}'";
if($row["Null"]!='YES') $txt .= " NOT NULL";
if($row["Extra"]!=''){
if( trim($row['Extra']) == "'CURRENT_TIMESTAMP'" ) $row["Extra"] = 'CURRENT_TIMESTAMP';
$txt .= " {$row['Extra']}";
}
}
if( $txt=="" ) die('La tabla <b>"'.$table.'"</b> no existe.');
$txt = $xLinea.$txt;
$txt = preg_replace('/,$/', "", $txt);
SS::query("SHOW KEYS FROM {$table}");
while( $row = SS::get() ){
$kname = $row['Key_name'];
if(($kname!="PRIMARY") && ($row['Non_unique'] == 0))
$kname="UNIQUE|{$kname}";
if(!isset($index[$kname]))
$index[$kname] = array();
$index[$kname][] = $row['Column_name'];
}
foreach($index as $x=>$columns){
$txt .= "<TR><TD colspan=2 nowrap>";
if($x == "PRIMARY")
$txt .= "   PRIMARY KEY (".implode(", ", $columns).")";
elseif (mb_substr($x,0,6) == "UNIQUE")
$txt .= "   UNIQUE ".mb_substr($x,7)." (".implode(", ", $columns).")";
else
$txt .= "   KEY $x (".implode(", ", $columns).")";
}
return(stripslashes($txt));
}
}
if( !function_exists("GetTablaOracle") ){
function GetTablaOracle($table, $Multi, &$DimCampos=NULL){
if( !qExists($table) ) return "NoExist";
global $_T, $_SqlUsuario;
$SqlUsuario = mb_strtoupper($_SqlUsuario);
if( $Multi ){
$xLinea = "<TR><TH colspan=2 Max=1 ondblclick='MaxDef(this)' onmousedown='Mover(this)'>TABLE <B>{$table}</B></TH>";
}else{
$xLinea = "<TR><TH colspan=2>TABLE <B>{$table}</B></TH>";
}
global $_Result;
$Tabla = mb_strtoupper($table);
$sql = "select * from all_tab_cols where table_name='{$Tabla}' order by COLUMN_ID";
SS::query($sql);
while( OCIFetch($_Result) ){
$txt .= '<TR><TD>'.mb_strtolower(OCIResult($_Result,'COLUMN_NAME')).'<TD nowrap> '.mb_strtolower(OCIResult($_Result,'DATA_TYPE'));
if( OCIResult($_Result,'DATA_TYPE')!='DATE' && mb_substr(OCIResult($_Result,'DATA_TYPE'),0,9)!='TIMESTAMP' ){
$txt .= '(';
if( OCIResult($_Result,'DATA_PRECISION')== 0 ){
$txt .=  OCIResult($_Result,'DATA_LENGTH');
}else{
$txt .=  OCIResult($_Result,'DATA_PRECISION');
if( OCIResult($_Result,'DATA_SCALE')!=0 ) $txt .= ','.OCIResult($_Result,'DATA_SCALE');
}
$txt .= ') ';
}
if( OCIResult($_Result,'NULLABLE')=='N' ) $txt .= ' not null';
}
$sql = "SELECT INDEX_NAME,UNIQUENESS FROM all_indexes where TABLE_OWNER='{$SqlUsuario}' and TABLE_NAME='{$Tabla}'";
SS::query($sql);
while( OCIFetch($_Result) ){
$n=0;
$sql = "SELECT COLUMN_NAME,DESCEND FROM all_ind_columns where INDEX_OWNER='{$SqlUsuario}' and TABLE_NAME='{$Tabla}' and INDEX_NAME='".OCIResult($_Result,'INDEX_NAME')."' order by COLUMN_POSITION";
$txt .= '<TR><TD colspan=2 nowrap>';
SS::query($sql, $pt);
while( OCIFetch($pt) ){
if( $n>0 ) $txt .= ',';
$txt .= mb_strtolower(OCIResult($pt,'COLUMN_NAME'));
if( OCIResult($pt,'DESCEND') == 'DESC' ) $txt .= ' DESC';
$n++;
}
if( OCIResult($_Result,'UNIQUENESS')=='UNIQUE' ) $txt .= ' <B>(unique)</B>';
}
if( $txt=="" ) die('La tabla <b>"'.$table.'"</b> no existe.');
$txt = $xLinea.$txt;
return(stripslashes($txt));
}
}
if( !function_exists("GetTablaInformix") ){
function GetTablaInformix($table, $Multi, &$DimCampos=NULL){
if( !qExists($table) ) return "NoExist";
$OriTabla = $NomTabla = $table;
$NomAlias = '';
if( SS::count('systables', "tabname='{$NomTabla}'") == 0 ){
die('ERROR: Tabla no encontrada');
}
SS::query("select * from systables where tabname='{$NomTabla}'");
$row = SS::get();
$TablaSinonimo = $row['tabtype'];
if( $row['tabtype']=='S' ){
SS::query("select * from syssyntable where tabid='{$row['tabid']}'");
$row = SS::get();
SS::query("select * from systables where tabid='{$row['btabid']}'");
$row = SS::get();
}else if( $row['tabtype']=='T' ){
SS::query("select * from syssyntable where btabid='{$row['tabid']}'", [], 1);
$row2 = SS::get(1);
SS::query("select * from systables where tabid='{$row2['tabid']}'", [], 1);
$row2 = SS::get(1);
$NomAlias = trim($row2['tabname']);
}
$bd_tabid = $row['tabid'];
if( $TablaSinonimo == 'T' ){
if( $NomAlias!='' ){
$OriTabla = '<B title="Table">'.$OriTabla.'</B> (<U title="Alias">'.$NomAlias.'</U>)';
}else{
}
}else{
$OriTabla = '<U title="Alias">'.$OriTabla.'</U> <B title="Table">('.trim($row['tabname']).'</B>)';
}
if( $Multi ){
$xLinea = "<TR><TH colspan=2 Max=1 ondblclick='MaxDef(this)' onmousedown='Mover(this)'>TABLE {$OriTabla}</TH>";
}else{
$xLinea = "<TR><TH colspan=2>TABLE {$OriTabla}</TH>";
}
$DimTipos = array('CHAR','SMALLINT','INTEGER','FLOAT','SMALLFLOAT','DECIMAL','SERIAL','DATE','MONEY','',
'DATETIME','BYTE','TEXT','VARCHAR','INTERVAL','NCHAR','NVARCHAR','INT8','SERIAL8','SET','MULTISET','LIST','');
$DimTipos[40] = '';
$DimPrecision = array(
'0000'=>'year',
'0010'=>'month',
'0100'=>'day',
'0110'=>'hour',
'1000'=>'minute',
'1010'=>'second' );
$cad_sql = 'select A.tabname,A.ncols,A.nindexes,B.colname,B.colno,B.coltype,B.collength from systables as A, outer syscolumns as B where A.tabtype = "T" and A.tabid = B.tabid and A.tabid = '.$bd_tabid;
SS::query($cad_sql, [], 3);
$DimCampo = array();
while( $Dim = SS::get(3) ){
$Nulos = '';
if( $Dim['coltype'] > 255 ){
$Nulos = ' not null ';
$Restar = 32768;
for( $n=0; $n<8; $n++ ){
if( $Dim['coltype'] >= $Restar ) $Dim['coltype'] -= $Restar;
$Restar = $Restar/2;
}
}
$txt .= '<TR><TD>'.$Dim['colname'];
$txt .= '<TD nowrap>'.mb_strtolower($DimTipos[$Dim['coltype']]);
switch( $DimTipos[$Dim['coltype']] ){
case 'DATETIME':
case 'INTERVAL':
for( $n=11; $n<16; $n++ ){
if( $Dim['collength'] >= (256*$n) ) $Dim['collength'] -= (256*$n);
}
$bin = decbin( $Dim['collength'] );
$bin = mb_substr($bin,-8);
$desde = mb_substr($bin,0,4);
$hasta = mb_substr($bin,-4);
$txt .= ' '.$DimPrecision[$desde].' to '.$DimPrecision[$hasta];
$Dim['collength'] = '';
break;
case 'DATE':
case 'TEXT':
case 'SMALLINT';
case 'INTEGER':
case 'FLOAT':
case 'SMALLFLOAT':
$Dim['collength'] = '';
break;
case 'DECIMAL':
$Dim['collength'] =  '('.floor($Dim['collength'] / 256).','.($Dim['collength'] % 256).')';
break;
case 'VARCHAR':
$Dim['collength'] =  '('.($Dim['collength'] % 256).','.floor($Dim['collength'] / 256).')';
break;
default:
$txt .= '(';
$Dim['collength'] .= ')';
}
$txt .= $Dim['collength'].' '.$Nulos;
$DimCampo[$Dim['colno']] = trim($Dim['colname']);
}
$npart = 17;
$cad_sql = 'select part1,part2,part3,part4,part5,part6,part7,part8,part9,part10,part11,part12,part13,part14,part15,part16,idxtype from sysindexes where tabid = '.$bd_tabid;
SS::query( $cad_sql );
$i = 0;
while( $Dim = SS::get() ){
$txt .= '<TR><TD colspan=2 nowrap>';
$num = 0;
for( $l=1;$l<$npart;$l++ ){
if( trim($Dim['part'.$l]) ){
$ind[$i][$num] = trim($Dim['part'.$l]);
if( $num > 0 && trim($DimCampo[$ind[$i][$num]])!='' ) $txt .= ', ';
$txt .= $DimCampo[$ind[$i][$num]];
$num++;
}
}
if( trim($Dim['idxtype']) == 'U' ){
$ind[$i][16] = 'S';
$txt .= ' <B>(unique)</B>';
}else{
$ind[$i][16] = 'N';
}
$i++;
}
if( $txt=="" ) die('La tabla <b>"'.$table.'"</b> no existe.');
$txt = $xLinea.$txt;
return(stripslashes($txt));
}
}
?>