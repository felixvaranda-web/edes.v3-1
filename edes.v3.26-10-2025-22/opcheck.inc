<?PHP
function _eOpCkeck( $OpAChequear ){
include("../_datos/config/desktop.ini");
global $_Sql;
$_UserTree = S::$_User;
if( SETUP::$Desktop['DesktopTreeType']=='O' ){
$OpNo = "'U'";
if( $_SESSION["_Development"] ) $OpNo .= "";
else if( $_Test ) $OpNo .= ",'D'";
else $OpNo .= ",'D','T'";
$TypeUR = (($_TypeTree=='P') ? 'user' : 'rol');
$OpcionesAEliminar = '';
if( SS::isDriver('mysql,mysqli') ){
SS::query( "select distinct seq, indent, type, caption, script_url, o.cd_gs_op, tip, icon, show_type, alias, icons, mode
from {$_ENV['SYSDB']}gs_op o left join {$_ENV['SYSDB']}gs_tree_op t on o.cd_gs_op = t.cd_gs_op
where
o.cd_gs_op={$OpAChequear}
and
(
( type<>'O' or type is null )
or
(
show_type not in ({$OpNo})
and
(
(
instr(         (select mode     from {$_ENV['SYSDB']}gs_{$TypeUR}_tree where cd_gs_{$TypeUR}={$_UserTree} and cd_gs_tree=t.cd_gs_tree), o.mode ) > 0
or
o.cd_gs_op  in (select cd_gs_op from {$_ENV['SYSDB']}gs_{$TypeUR}_op   where cd_gs_{$TypeUR}={$_UserTree} and cd_gs_tree=t.cd_gs_tree and action='I')
)
and
o.cd_gs_op not in (select cd_gs_op from {$_ENV['SYSDB']}gs_{$TypeUR}_op   where cd_gs_{$TypeUR}={$_UserTree} and cd_gs_op=o.cd_gs_op and action='D')
{$OpcionesAEliminar}
and
t.cd_gs_tree in (select cd_gs_tree from {$_ENV['SYSDB']}gs_tree where cd_gs_tree=t.cd_gs_tree and permission='{$_ENV['ON']}')
)
)
)
order by seq" );
}else{
SS::query( "select distinct seq, indent, type, caption, script_url, o.cd_gs_op, tip, icon, show_type, alias, icons, mode
from {$_ENV['SYSDB']}gs_op o left join {$_ENV['SYSDB']}gs_tree_op t on o.cd_gs_op = t.cd_gs_op
where
o.cd_gs_op={$OpAChequear}
and
(
( type<>'O' or type is null )
or
(
show_type not in ({$OpNo})
and
(
(
instr(         (select mode     from {$_ENV['SYSDB']}gs_{$TypeUR}_tree where cd_gs_{$TypeUR}={$_UserTree} and cd_gs_tree=t.cd_gs_tree), o.mode ) > 0
or
o.cd_gs_op  in (select cd_gs_op from {$_ENV['SYSDB']}gs_{$TypeUR}_op   where cd_gs_{$TypeUR}={$_UserTree} and cd_gs_tree=t.cd_gs_tree and action='I')
)
and
o.cd_gs_op not in (select cd_gs_op from {$_ENV['SYSDB']}gs_{$TypeUR}_op   where cd_gs_{$TypeUR}={$_UserTree} and cd_gs_op=o.cd_gs_op and action='D')
{$OpcionesAEliminar}
and
t.cd_gs_tree in (select cd_gs_tree from {$_ENV['SYSDB']}gs_tree where cd_gs_tree=t.cd_gs_tree and permission='{$_ENV['ON']}')
)
)
)
order by seq" );
}
}
$r = SS::get();
return ( $r["script_url"] != "" );
}
?>