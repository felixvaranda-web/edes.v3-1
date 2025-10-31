# Devoluciones
```php
[Title]  DEVOLUCIONES
[DBTable]  orden
[DBIndex]  id_orden
[DBSerial] id_orden

[NoTools] pxm 

[MsgSubmit] a | <center>Este proceso no se puede deshacer<br><b>CONFIRME</b> que desea realizar la devolución</center>
[Title] = Modificar cobro
[Button] a | <IMG src='g/cuotas.gif'>Devolver importe | Devolver importe
[Radio] tipo_regularizacion| 2L |T,Total;P,Parcial
[AddCode] a | importe_devol | S | <span style="color:red; font-weight:bold">Importe a devolver</span>
[AddOptionValue]  cd_tipo_regu | valor
[AddOption] a | cd_tipo_regu | tipoRegu()
[OnChange] a | cd_tipo_regu | if( eGA('cd_tipo_regu','valor')>0 ) ePF('importe_devol',(eGF('importe') - eGA('cd_tipo_regu','valor')) );
[OnChange] a | tipo_regularizacion | gestionRegularizacion()

[PHPIni] *
if (!ePermission("devolucion")) eMessage('OPCIÓN NO PERMITIDA','HS');


[Fields] 2
                     | id_orden			 | +   | T  | 8   |                   | * |  |   | 
    DNI              | dni              | DNI | T  | 10  |                   | - |  | - | 
    Apellidos        | apel             | N   | T  | 30  | numero_pedido     | - |  |   | 
 +2 Nombre           | nombre           | N   | T  | 20  | numero_pedido     | - |  |   | 

-|Datos de cobro
    Periodo desde    | p_ini_pago       | P4  | T  | 7   | 75                | - |  |   | 
   ,hasta            | p_fin_pago       | P4  | T  | 7   | 75/<numero_pedido | - |  |   | 
 +2 Fecha            | fecha            | F4  | T  | 10  |                   | - |  |   | 
   ,Importe          | importe          | +,  | T  | 4,2 | +estado_pago      | - |  |   | 
    Numero pedido    | numero_pedido    | X   | T  | 20  | 250               | - |  |   | 
 +2 Modo             | modo             | X   | T  | 20  | numero_pedido     | - |  |   | 
    Estado           | estado_procesado | X   | T  | 20  | numero_pedido     | - |  |   | 
 +2 Pago             | estado_pago      | X   | T  | 20  | numero_pedido     | - |  |   | 
    Cuota            | cd_tipo          | X   | T  | 20  | numero_pedido     | - |  |   | 
 +2 Suplemento       | cd_sup           | X   | T  | 20  | numero_pedido     | - |  |   | 
-|Datos de regularización
    Tipo devolución  | tipo_regularizacion  | X   | r  | 1   |                   | M |  |   | 
 +2                  | importe_devol    	  | +,  | T  | 4,2 | <cd_sup           | - |  |   | 
    Tipo cuota final | cd_tipo_regu     	  | X   | SV | 20  | 250               | M |  |   | 
    


[JSCheck] a
	if(eGF('tipo_regularizacion')=='') ePE('tipo_regularizacion', 'Falta introducir el dato "TIPO DEVOLUCIÓN"');
	if(eGF('tipo_regularizacion')=='P'){//Parcial obligado a meter cuota que se queda
		if(eGF('cd_tipo_regu')=='') ePE('cd_tipo_regu', 'Falta introducir el dato "TIPO CUOTA FINAL"');
	}
	
[JSIni] a
function gestionRegularizacion(){
	if( eGF('tipo_regulariza') == 'T' ) {
		ePF('importe_devol',eGF('importe')); 
		ePF('cd_tipo_regu','');
		eEF('cd_tipo_regu',0);
	}else if( eGF('tipo_regulariza') == 'P' ) {
		ePF('importe_devol','');
		eEF('cd_tipo_regu',1);
	}else{
		ePF('importe_devol','');
		eEF('cd_tipo_regu',0);
	
	}
}


[DBIni] A 
	//Antes de hacer nada en la BBDD con dbini podemos coger el control para hacer lo que queramos. 
	//Podemos llamar por ejemplo a un procedimiento almacenado o ejecutar otra cosa con los datosa que nos llegan del formulario.
	//para parar la ejecucion para que no haga nada en BBDD usamos eMessage("PROCESO TERMINADO", 'HS'); eMessage hace exit.

[Note]	
Aqui ponemos las notas que se necesiten
```