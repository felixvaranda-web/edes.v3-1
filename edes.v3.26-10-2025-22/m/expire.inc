<?PHP
eLogError("SESS", "MaxLife expire ".$_SESSION["SessionMaxLife"].' < '.$_SESSION["_LoginTime"]);
if(S::$__tronEntrar) eTron(date('His: ')."eEnd:19");
eSessionClose(11);
if(S::$__tronEntrar) eTron(date('His: ')."eEnd:20");
eJS("top.document.write('El tiempo ha expirado');");
exit;
?>