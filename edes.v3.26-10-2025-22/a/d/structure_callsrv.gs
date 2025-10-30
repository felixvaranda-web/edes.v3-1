<?PHP
[CallSrv] MoverDir
eLngLoad(DIREDES.'lng/structure_def.edf');
if( file_exists('../_datos/config/structure.idf.lng') ) eLngLoad('../_datos/config/structure.idf');
SS::query("update {$_ENV['SYSDB']}gs_structure set cd_gs_structure_parent='{$_GET['cPadre']}' where cd_gs_structure={$_GET['MoverDir']}");
echo '<script type="text/javascript">';
echo 'top.eInfo( window.frameElement.WOPENER, "'.eLng('Directorio Movido').'" );';
echo '</script>';
eEnd();
[CallSrv] BorrarDir
eLngLoad(DIREDES.'lng/structure_def.edf');
if( file_exists('../_datos/config/structure.idf.lng') ) eLngLoad('../_datos/config/structure.idf');
$_DEBUG = 1;
function BorrarHijos( $BorrarDir ){
SS::query("select * from {$_ENV['SYSDB']}gs_structure where cd_gs_structure_parent={$BorrarDir}", [], 1);
while( $r = SS::get(1) ){
if( $r['cd_gs_structure_parent'] > 0 ) BorrarHijos( $r['cd_gs_structure'] );
SS::query("delete from {$_ENV['SYSDB']}gs_structure where cd_gs_structure={$r['cd_gs_structure']}", [], 2);
}
}
BorrarHijos( $BorrarDir );
SS::query("delete from {$_ENV['SYSDB']}gs_structure where cd_gs_structure={$_GET['BorrarDir']}");
echo '<script type="text/javascript">';
echo 'top.eInfo( window.frameElement.WOPENER, "'.eLng('Directorio Borrado').'" );';
echo '</script>';
eEnd();
[CallSrv] LoadDirGrupo
eHTML('E:$a/d/structure.gs', 'LoadDir');
?>
<SCRIPT type="text/javascript">
function eClearEvent(){
try{
S.eventClear(window);
}catch(e){}
return false;
}
function EditTmp( tmp ){
if( top._Desktop!=undefined ){
top.gsEdit(window, tmp,10);
}
return eClearEvent();
}
function VerIco(){
if( (event.ctrlKey || event.altKey) && top._Desktop!=undefined ){
top.gsEdit(window, '$a/d/structure.gs',10);
return eClearEvent();
}
return true;	}
document.oncontextmenu = VerIco;
document.ondblclick = function anonymous(){ top._Reload(window); }
top.eLoading(false,window);
</SCRIPT>
</head><body><script type="text/javascript">
var _WOPENER = window.frameElement.WOPENER;
var DimFolder = new Array();
DimFolder[0] = "<img src='edes.php?R:/_datos/config/2_0.gif' class='n0' cElemento=15268 cPadre=15267 SC=0 CNT='S' Tipo='2' Hijos='0'>De.. ANDALUCIA";
DimFolder[1] = "<img src='edes.php?R:/_datos/config/2_0.gif' class='n0' cElemento=15335 cPadre=15267 SC=0 CNT='S' Tipo='2' Hijos='0'>De.. ARAGON";
DimFolder[2] = "<img src='edes.php?R:/_datos/config/2_0.gif' class='n0' cElemento=15348 cPadre=15267 SC=0 CNT='S' Tipo='2' Hijos='0'>De.. ASTURIAS"	;
DimFolder[3] = "<img src='edes.php?R:/_datos/config/2_0.gif' class='n0' cElemento=15360 cPadre=15267 SC=0 CNT='S' Tipo='2' Hijos='0'>De.. CANTABRIA";
if( DimFolder.length > 0 ){
var TABLA = _WOPENER.document.children[<?=$DesdeTABLE?>];
var TR = TABLA.insertRow(<?=$DesdeTR+1?>);
var TD = TR.insertCell(0);
TD.innerHTML = '<table id="Compartir2" class="n<?=$Indent?>" style="border:1px dashed red"><tr><th>Grupo</table>';
var pTD = TD.children[0];
for( var n=DimFolder.length-1; n>=0; n-- ){
var TR = pTD.insertRow(1);
var TD = TR.insertCell(0);
TD.innerHTML = DimFolder[n];
}
TABLA.rows[8].cells[0].children[0].src = TABLA.rows[8].cells[0].children[0].src.replace('_0.gif','_1.gif');
}
_WOPENER.eLoadingObj();
</script>
<?PHP
eEnd();
[CallSrv] LoadDir
if( $cd_gs_group=='' ) $cd_gs_group = 0;
echo '<script type="text/javascript">';
echo 'var _WOPENER = window.frameElement.WOPENER;';
echo 'var DimFolder = new Array();';
SS::query("select A.*, T.container from {$_ENV['SYSDB']}gs_structure A, {$_ENV['SYSDB']}gs_tstructure T where A.cd_gs_structure_parent={$_GET['LoadDir']} and A.cd_gs_tstructure=T.cd_gs_tstructure order by T.sort,A.nm_gs_structure");
$n=0;
while( $r = SS::get() ){
$TieneHijos = ( ( SS::count("{$_ENV['SYSDB']}gs_structure", "cd_gs_structure_parent={$r['cd_gs_structure']}", $p ) > 0 ) ? '_0' : '_1');
$Sufijo = $TieneHijos;
$r['mfilter'] = trim($r['mfilter']);
echo "DimFolder[{$n}] = ".'"';
$SeCargo = (($TieneHijos=='_0')?0:1);
echo "<img src='edes.php?R:/_datos/config/{$r['cd_gs_tstructure']}{$Sufijo}.gif' class='n{$Indent}' cElemento={$r['cd_gs_structure']} cPadre={$r['cd_gs_structure_parent']} SC={$SeCargo} CNT='{$r['container']}' Tipo='{$r['cd_gs_tstructure']}' User='{$r['cd_gs_user']}' Hijos='".mb_substr($TieneHijos,1)."' Filter='{$r['mfilter']}'>".trim($r['nm_gs_structure']);
if( $r['cd_gs_user'] > 0 ){
SS::query("select user_name,user_surname, email from {$_ENV['SYSDB']}gs_user where cd_gs_user='{$r['cd_gs_user']}'", [], 1);
$nm = SS::get("num", 1);
if( trim($nm[0])!='' ) echo  '|'.trim($nm[0]).' '.trim($nm[1]).' ('. trim($nm[2]).')';
}
echo '";';
$n++;
}
?>
if( DimFolder.length > 0 ){
var TABLA = _WOPENER.document.children[<?=$DesdeTABLE?>];
for( var n=DimFolder.length-1; n>=0; n-- ){
var TR = TABLA.insertRow(<?=$DesdeTR+1?>);
var TD = TR.insertCell(0);
var Datos = DimFolder[n].split('|');
TD.innerHTML = Datos[0];
if( Datos.length==2 ){
TD = TR.insertCell(1);
TD.innerHTML = Datos[1];
}else{
TD.colSpan = 2;
if( TD.children[0].CNT=='S' ) TR.className = 'oC';
}
}
<?PHP  if( $DesdeTR > 0 ){ ?>
TABLA.rows[<?=$DesdeTR?>].cells[0].children[0].src = TABLA.rows[<?=$DesdeTR?>].cells[0].children[0].src.replace('_0.gif','_1.gif');
<?PHP  } ?>
}
_WOPENER.eLoadingObj();
</script>
<?PHP
eEnd();
[CallSrv] ModificarDir
eLngLoad(DIREDES.'lng/structure_def.edf');
if( file_exists('../_datos/config/structure.idf.lng') ) eLngLoad('../_datos/config/structure.idf');
if( DB::isDriver("oci") ) $ModificarDir = str_replace("'","''",$ModificarDir);
$_DEBUG = 1;
SS::query("update {$_ENV['SYSDB']}gs_structure set nm_gs_structure='{$_GET['ModificarDir']}', cd_gs_tstructure={$_GET['Tipo']}, cd_gs_user='{$_GET['User']}', mfilter='{$_GET['Filter']}' where cd_gs_structure={$_GET['cElemento']}");
?>
<script type="text/javascript">
var _WOPENER = window.frameElement.WOPENER;
top.eInfo(_WOPENER,'<?=eLng('Directorio Modificado')?>');
</script>
<?PHP
eEnd();
[CallSrv] CrearDir
eLngLoad(DIREDES.'lng/structure_def.edf');
if( file_exists('../_datos/config/structure.idf.lng') ) eLngLoad('../_datos/config/structure.idf');
if( DB::isDriver("oci") ) $CrearDir = str_replace("'","''",$CrearDir);
if( $cd_gs_group=='' ) $cd_gs_group = 0;
$Ahora = date('Y-m-d H:i:s');
SS::query("insert into {$_ENV['SYSDB']}gs_structure (    nm_gs_structure   , cd_gs_tstructure, cd_gs_structure_parent, cdi_insert, cdi_update, mfilter )
values ( '{$_GET['CrearDir']}',  {$_GET['Tipo']},     {$_GET['cPadre']} , '{$Ahora}', '{$Ahora}', '{$_GET['Filter']}' )");
$Id = SS::id();
?>
<script type="text/javascript">
var _WOPENER = window.frameElement.WOPENER;
_WOPENER.document.children[<?=$SourceIndex?>].cElemento = <?=$Id?>;
top.eInfo(_WOPENER,'<?=eLng('Directorio Creado')?>');
</script>
<?PHP
eEnd();
?>