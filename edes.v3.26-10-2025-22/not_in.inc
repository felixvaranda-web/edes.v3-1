<?PHP
SS::query( "select count(*) from _seek where valor not in (select {$_NotInTemporary} from {$_DBTABLE})" );
$r=SS::get("num");
eTrace( $r[0] );
SS::query( "select valor from _seek where valor not in (select {$_NotInTemporary} from {$_DBTABLE})" );
while( $r=SS::get("num") ){
eTrace( $r[0] );
}
?>